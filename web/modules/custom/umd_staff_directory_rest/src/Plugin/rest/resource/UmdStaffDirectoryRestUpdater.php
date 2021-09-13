<?php

namespace Drupal\umd_staff_directory_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\Entity\Node;

/**
 * Provides a UMD Staff Directory Updater REST Resource.
 *
 * @RestResource(
 *   id = "umd_staff_directory_rest_updater",
 *   label = @Translation("UMD Staff Directory Rest Updater"),
 *   uri_paths = {
 *     "create" = "/directory/updater"
 *   }
 * )
 */
class UmdStaffDirectoryRestUpdater extends ResourceBase {
  /**
   * Mapping of fields in UmdTerpPerson to the corresponding fields in
   * the Staff Directory JSON file.
   */
  const UMD_TERP_PERSON_TO_STAFF_DIRECTORY = [
    'field_directory_id' => 'directory_id',
    'field_library_division' => 'division',
    'field_library_department' => 'department',
    'field_library_unit' => 'unit',
    'field_umdt_ct_person_first_name' => 'first_name',
    'field_umdt_ct_person_last_name' => 'last_name',
    'field_umdt_ct_person_phone' => 'phone',
    'field_umdt_ct_person_email' => 'email',
    'field_umdt_ct_person_title' => 'title',
    'field_umdt_ct_person_location' => 'location',
    'title' => 'display_name',
  ];

  const STATUS_PUBLISHED = 1;
  const STATUS_UNPUBLISHED = 0;

  /**
   * Responds to POST requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post(array $incoming_json) {
    \Drupal::service('page_cache_kill_switch')->trigger();
    \Drupal::logger('umd_staff_directory_rest_updater')->info(
      "Number of incoming entries: " . count($incoming_json)
    );

    $division_ids_by_name = self::get_division_ids_by_name();

    $terp_persons = self::getUmdTerpPersons();
    $current_json = self::umdTerpPersonsToJsonArray($terp_persons);
    $directory_id_to_node_ids = self::getDirectoryIdsToNodeIds($terp_persons);

    $current_directory_ids = array_keys($current_json);
    $incoming_directory_ids = array_keys($incoming_json);

    $directory_ids_to_add = array_diff($incoming_directory_ids, $current_directory_ids);
    $directory_ids_to_remove = array_diff($current_directory_ids, $incoming_directory_ids);

    $directory_ids_to_update = self::entriesToUpdate($current_directory_ids, $incoming_directory_ids, $current_json, $incoming_json);

    \Drupal::logger('umd_staff_directory_rest_updater')->info(
      "Entries to add (count=" . count($directory_ids_to_add) . "): " . implode(",", $directory_ids_to_add) . "\n" .
      "Entries to update: (count=" . count($directory_ids_to_update) . "): " . implode(",", $directory_ids_to_update) . "\n");
      "Entries to remove: (count=" . count($directory_ids_to_remove) . "): " . implode(",", $directory_ids_to_remove) . "\n" .

    self::addEntries($directory_ids_to_add, $incoming_json, $division_ids_by_name);
    self::updateEntries($directory_ids_to_update, $directory_id_to_node_ids, $current_json, $incoming_json, $division_ids_by_name);
    self::removeEntries($directory_ids_to_remove, $directory_id_to_node_ids);

    $now = (new \DateTime())->format('Y-m-d H:i:s');
    $response = ['message' => "UmdStaffDirectoryRestUpdater::$now::" .
                              "Added: " . count($directory_ids_to_add) .
                              ", Updated: " . count($directory_ids_to_update) .
                              ", Removed: " . count($directory_ids_to_remove)];

    return new ResourceResponse($response);
  }

  private static function addEntries(array $directory_ids_to_add, array $incoming_json, array $division_ids_by_name) {
    foreach($directory_ids_to_add as $directory_id) {
      $staff_dir_values = $incoming_json[$directory_id];
      self::addEntry($staff_dir_values, $division_ids_by_name);
    }
  }

  private static function addEntry(array $staff_dir_values, array $division_ids_by_name) {
    $values = [
      'type' => 'umd_terp_person',
      'title' => $staff_dir_values['display_name']
    ];
    $node = \Drupal::entityTypeManager()->getStorage('node')->create($values);
    self::populateNode($node, $staff_dir_values, $division_ids_by_name);
    $node->save();
  }

  private static function updateEntries(array $directory_ids_to_update, array $directory_id_to_node_ids, array $current_json, array $incoming_json, array $division_ids_by_name) {
    foreach($directory_ids_to_update as $directory_id) {
      $staff_dir_values = $incoming_json[$directory_id];
      $node_id = $directory_id_to_node_ids[$directory_id];
      self::updateEntry($node_id, $staff_dir_values, $division_ids_by_name);
    }
  }

  private static function updateEntry(string $node_id, array $staff_dir_values, array $division_ids_by_name) {
    $node = Node::load($node_id);

    self::populateNode($node, $staff_dir_values, $division_ids_by_name);
    $node->save();
  }

  private static function populateNode(Node $node, array $staff_dir_values, array $division_ids_by_name) {
    $field_mappings = array_flip(UmdStaffDirectoryRestUpdater::UMD_TERP_PERSON_TO_STAFF_DIRECTORY);

    foreach($field_mappings as $staff_dir_field => $umd_field) {
      if ($staff_dir_field == "division") {
        self::setDivision($node, $staff_dir_values, $staff_dir_field, $field_mappings[$staff_dir_field], $division_ids_by_name);
        continue;
      }

      $node->set($umd_field, $staff_dir_values[$staff_dir_field]);
    }

    // Ensure node is published
    $node->set('status', self::STATUS_PUBLISHED);
  }

  /**
   * Removes staff directory entries by setting them to unpublished.
   *
   * @param array $directory_ids_to_remove the directory ids to remove
   * @param array $directory_id_to_node_ids associative array mapping directory ids to node ids
   */
  private static function removeEntries(array $directory_ids_to_remove, array $directory_id_to_node_ids) {
    foreach($directory_ids_to_remove as $directory_id) {
      $node_id = $directory_id_to_node_ids[$directory_id];
      self::removeEntry($node_id);
    }
  }

  /**
   * Removes (by unpublishing) the node at the given node id.
   *
   * @param int the node id of the node to remove
   */
  private static function removeEntry(int $node_id) {
    $node = Node::load($node_id);

    $node->set('status', self::STATUS_UNPUBLISHED);
    $node->save();
  }

  private static function setDivision(Node $node, array $staff_dir_values, string $staff_dir_field, string $umd_terp_field, array $division_ids_by_name) {
    $division_name = $staff_dir_values[$staff_dir_field];
    if (!empty($division_name)) {
      if (array_key_exists($division_name, $division_ids_by_name)) {
        $division_id = $division_ids_by_name[$division_name];
        $node->set($umd_terp_field, ['target_id' => $division_id ]);
      }
      else {
        \Drupal::logger('umd_staff_directory_rest_updater')->error(
          "Node with id " . $node_id . " has an unknown division of '" . $division_name . "'. Skipping division update.");
      }
    }
  }

  private static function entriesToUpdate(array $current_directory_ids, array $incoming_directory_ids, array $current_json, array $incoming_json) {
    $directory_ids_to_check = array_intersect($incoming_directory_ids, $current_directory_ids);

    $directory_ids_to_update = array();
    foreach ($directory_ids_to_check as $directory_id) {
      $current = $current_json[$directory_id];
      $incoming = $incoming_json[$directory_id];

      $diff = array_diff($current, $incoming);

      if (!empty($diff)) {
        array_push($directory_ids_to_update, $directory_id);
      }
    }

    return $directory_ids_to_update;
  }


  /**
   * Returns an array of a;; UMD Terp Person nodes
   *
   * @return array an array of all UMD Terp Person nodes
   */
  private static function getUmdTerpPersons() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'umd_terp_person');
    $ids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    return $nodes;
  }

  /**
   * Returns an associative array of Drupal node ids, keyed by directory id
   *
   * @param array an array of UMD Terp Person nodes from the database.
   * @return array an associative array of Drupal node ids, keyed by
   * directory id
   */
  private static function getDirectoryIdsToNodeIds($terp_persons) {
    $directory_ids_to_node_ids = array();

    foreach ($terp_persons as $node) {
      $directory_id = self::getNodeValue($node, 'field_directory_id');
      $node_id = self::getNodeValue($node, 'nid');
      $directory_ids_to_node_ids[$directory_id] = $node_id;
    }

    return $directory_ids_to_node_ids;
  }

  private static function umdTerpPersonsToJsonArray($nodes) {
    $json_array = array();
    foreach ($nodes as $node) {
      $node_json = self::umdTerpPersonToJsonArray($node);
      $directory_id = $node_json['directory_id'];
      if (empty($directory_id)) {
        $node_id = $node->get('nid')->first()->value;
        \Drupal::logger('umd_staff_directory_rest_updater')->error(
          "Node with id " . $node_id . " does not have a directory id. Skipping.");
        continue;
      }
      $json_array[$directory_id] = $node_json;
    }
    return $json_array;
  }

  /**
   * Converts a UMD Terp Person node to an associative array for comparison
   * to the incoming JSON data.
   *
   * @param \Drupal\node\Entity\Node the UMD Terp Person node to convert
   * @return array an associative array using the JSON keys to map the values
   * in the node.
   */
  private static function umdTerpPersonToJsonArray(Node $node) {
    $json_array = array();
    foreach (UmdStaffDirectoryRestUpdater::UMD_TERP_PERSON_TO_STAFF_DIRECTORY as $umd_field => $staff_dir_field) {
      $node_value = self::getNodeValue($node, $umd_field);
      $json_array[$staff_dir_field] = $node_value;
    }
    unset($umd_field);
    unset($staff_dir_field);

    return $json_array;
  }

  private static function getNodeValue(Node $node, string $field) {
    $node_field = $node->get($field);

    if (is_null($node_field)) {
      return "";
    }
    $node_value = $node_field->first();

    if (is_null($node_value)) {
      return "";
    }

    if (($node_value instanceof \Drupal\Core\Field\Plugin\Field\FieldType\StringItem) ||
        ($node_value instanceof \Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem))
    {
      $value = $node_value->value;
      return $value;
    } else if ($node_value instanceof \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem) {
      $value = $node_value->get('entity')->getTarget()->getValue()->get('name')->value;
      return $value;
    } else {
      \Drupal::logger('umd_staff_directory_rest_updater')->error(
        "Field " . $umd_field . " has an unknown node value type " . get_class($node_value));
    }
    return "";
  }

  /**
   * Returns an associative array, indexed by division name, containing the
   * division's taxonomy id.
   */
  private static function get_division_ids_by_name() {
    $vid = "divisions";
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
    $divisions = array();
    foreach ($terms as $term) {
      $divisions[$term->name] = $term->tid;
    }
    return $divisions;
  }
}

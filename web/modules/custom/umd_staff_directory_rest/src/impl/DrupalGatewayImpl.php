<?php

namespace Drupal\umd_staff_directory_rest\impl;

use Drupal\node\Entity\Node;
use Drupal\umd_staff_directory_rest\DrupalGateway;

/**
 * Drupal gateway for the UMD Staff Directory REST endpoing
 */
class DrupalGatewayImpl implements DrupalGateway {
  protected $umd_terp_persons_nodes;
  protected $directory_id_to_node_ids;

  // An associative array of Division taxonomy ids, indexed by division name
  protected $division_ids_by_name;


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

  public function __construct() {
    $this->umd_terp_persons_nodes = self::getUmdTerpPersonsNodes();
    $this->directory_id_to_node_ids = self::getDirectoryIdsToNodeIds($this->umd_terp_persons_nodes);
    $this->division_ids_by_name = self::get_division_ids_by_name();
  }

  public function addEntry(array $staff_dir_values) {
    $values = [
      'type' => 'umd_terp_person',
      'title' => $staff_dir_values['display_name']
    ];
    $node = \Drupal::entityTypeManager()->getStorage('node')->create($values);
    $this->populateNode($node, $staff_dir_values);
    $node->save();
  }

  public function updateEntry(string $directory_id, array $staff_dir_values) {
    $node_id = $this->directory_id_to_node_ids[$directory_id];
    $node = Node::load($node_id);

    $this->populateNode($node, $staff_dir_values);
    $node->save();
  }

    /**
   * Removes (by unpublishing) the node for the given directory id.
   *
   * @param string $directory_id the directory id of the node to remove.
   */
  public function removeEntry(string $directory_id) {
    $node_id = $this->directory_id_to_node_ids[$directory_id];
    $node = Node::load($node_id);

    $node->set('status', self::STATUS_UNPUBLISHED);
    $node->save();
  }

  /**
   * Restores the node (by publishing) for the given diretory id.
   *
   * @param string $directory_id the directory id of the node to republish.
   */
  public function republishEntry(string $directory_id) {
    $node_id = $this->directory_id_to_node_ids[$directory_id];
    $node = Node::load($node_id);

    $node->set('status', self::STATUS_PUBLISHED);
    $node->save();
  }

  private function populateNode(Node $node, array $staff_dir_values) {
    $field_mappings = array_flip(self::UMD_TERP_PERSON_TO_STAFF_DIRECTORY);

    foreach($field_mappings as $staff_dir_field => $umd_field) {
      if ($staff_dir_field == "division") {
        self::setDivision($node, $staff_dir_values, $staff_dir_field, $field_mappings[$staff_dir_field]);
        continue;
      }

      $node->set($umd_field, $staff_dir_values[$staff_dir_field]);
    }

    // Ensure node is published
    $node->set('status', self::STATUS_PUBLISHED);
  }

  private function setDivision(Node $node, array $staff_dir_values, string $staff_dir_field, string $umd_terp_field) {
    $division_name = $staff_dir_values[$staff_dir_field];
    if (!empty($division_name)) {
      if (array_key_exists($division_name, $this->division_ids_by_name)) {
        $division_id = $this->division_ids_by_name[$division_name];
        $node->set($umd_terp_field, ['target_id' => $division_id ]);
      }
      else {
        \Drupal::logger('umd_staff_directory_rest_updater')->error(
          "Node with id " . $node_id . " has an unknown division of '" . $division_name . "'. Skipping division update.");
      }
    }
  }

  /**
   * Returns an array of all UMD Terp Person nodes
   *
   * @return array an array of all UMD Terp Person nodes
   */
  private static function getUmdTerpPersonsNodes() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'umd_terp_person');
    $ids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);
    return $nodes;
  }

  /**
   * Returns an array of directory ids for all unpublished UMD Terp Persons
   *
   * @return array an array of directory ids for all unpublished UMD Terp Persons
   */
  public static function getUnpublishedUmdTerpPersonDirectoryIds() {
    $query = \Drupal::entityTypeManager()->getStorage('node')->getQuery();
    $query->condition('type', 'umd_terp_person');
    $query->condition('status', FALSE);
    $ids = $query->execute();
    $nodes = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($ids);

    $directory_ids = array();

    foreach($nodes as $node) {
      $directory_id = self::getNodeValue($node, 'field_directory_id');
      if (!empty($directory_id)) {
        $directory_ids[] = $directory_id;
      }
    }
    return $directory_ids;
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

  public function umdTerpPersonsToJsonArray() {
    $nodes = $this->umd_terp_persons_nodes;

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
    foreach (self::UMD_TERP_PERSON_TO_STAFF_DIRECTORY as $umd_field => $staff_dir_field) {
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
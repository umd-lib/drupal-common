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

    $division_ids_by_name = DrupalGateway::get_division_ids_by_name();

    $terp_persons = DrupalGateway::getUmdTerpPersons();
    $current_json = DrupalGateway::umdTerpPersonsToJsonArray($terp_persons);
    $directory_id_to_node_ids = DrupalGateway::getDirectoryIdsToNodeIds($terp_persons);

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
      DrupalGateway::addEntry($staff_dir_values, $division_ids_by_name);
    }
  }

  private static function updateEntries(array $directory_ids_to_update, array $directory_id_to_node_ids, array $current_json, array $incoming_json, array $division_ids_by_name) {
    foreach($directory_ids_to_update as $directory_id) {
      $staff_dir_values = $incoming_json[$directory_id];
      $node_id = $directory_id_to_node_ids[$directory_id];
      DrupalGateway::updateEntry($node_id, $staff_dir_values, $division_ids_by_name);
    }
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
      DrupalGateway::removeEntry($node_id);
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
}

<?php

namespace Drupal\umd_staff_directory_rest\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\umd_staff_directory_rest\DrupalGateway;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Psr\Log\LoggerInterface;

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
   * @var \Drupal\umd_staff_directory_rest\DrupalGateway
   */
  protected $gateway;

  /**
   * UmdStaffDirectoryRestUpdater constructor.
   *
   * @param \Drupal\umd_staff_directory_rest\DrupalGateway $gateway
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, DrupalGateway $gateway) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->gateway = $gateway;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('rest'),
      $container->get('umd_staff_directory_rest.gateway')
    );
  }


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

    // $terp_persons = $this->gateway->getUmdTerpPersons();
    $current_json = $this->gateway->umdTerpPersonsToJsonArray();
    $unpublished_directory_ids = $this->gateway->getUnpublishedUmdTerpPersonDirectoryIds();

    $current_directory_ids = array_keys($current_json);
    $incoming_directory_ids = array_keys($incoming_json);

    $directory_ids_to_add = array_diff($incoming_directory_ids, $current_directory_ids);
    $directory_ids_to_remove = self::entriesToRemove($current_directory_ids, $incoming_directory_ids, $unpublished_directory_ids);
    $directory_ids_to_update = self::entriesToUpdate($current_directory_ids, $incoming_directory_ids, $current_json, $incoming_json);

    $directory_ids_to_republish = self::entriesToRepublish($incoming_directory_ids, $directory_ids_to_update, $unpublished_directory_ids);

    \Drupal::logger('umd_staff_directory_rest_updater')->info(
      "Entries to add (count=" . count($directory_ids_to_add) . "): " . implode(",", $directory_ids_to_add) . "\n" .
      "Entries to update: (count=" . count($directory_ids_to_update) . "): " . implode(",", $directory_ids_to_update) . "\n" .
      "Entries to remove: (count=" . count($directory_ids_to_remove) . "): " . implode(",", $directory_ids_to_remove) . "\n" .
      "Entries to republish: (count=" . count($directory_ids_to_republish) . "): " . implode(",", $directory_ids_to_republish) . "\n" );

    $this->addEntries($directory_ids_to_add, $incoming_json);
    $this->updateEntries($directory_ids_to_update, $current_json, $incoming_json);
    $this->removeEntries($directory_ids_to_remove);
    $this->republishEntries($directory_ids_to_republish);

    $now = (new \DateTime())->format('Y-m-d H:i:s');
    $response = ['message' => "UmdStaffDirectoryRestUpdater::$now::" .
                              "Added: " . count($directory_ids_to_add) .
                              ", Updated: " . count($directory_ids_to_update) .
                              ", Removed: " . count($directory_ids_to_remove) .
                              ", Republished: " . count($directory_ids_to_republish)];

    return new ResourceResponse($response);
  }

  /**
   * Returns an array of directory ids that should be removed.
   *
   * Entries that should be removed are those that are:
   *
   * 1) Not in the "incoming" directory ids array
   * 2) Not already unpublished (i.e., are not in the "unpublished" directory
   *    ids array.
   *
   * @param array current_directory_ids the directory ids of all UMD Terp Persons
   * @param array incoming_directory_ids the "incoming" directory ids from the POST request
   * @param array unpublished_directory_ids the directory ids of "unpublished" UMD Terp Persons
   */
  private static function entriesToRemove($current_directory_ids, $incoming_directory_ids, $unpublished_directory_ids) {
    $directory_ids_not_in_incoming = array_diff($current_directory_ids, $incoming_directory_ids);
    // Remove any ids not in the incoming list that are already unpublished.
    // This prevents entries from being unpublished every time the staff
    // directory is updated
    $directory_ids_to_remove = array_diff($directory_ids_not_in_incoming, $unpublished_directory_ids);
    return $directory_ids_to_remove;
  }

    /**
   * Returns an array of directory ids that should be republished.
   *
   * Entries that should be republished are those that are:
   *
   * 1) Are in the "unpublished_directory_ids" directory ids array
   * 2) Are in the "incoming" directory ids array
   * 3) Are NOT in the "update" directory ids array (as they will be republished automatically).
   *
   * @param array incoming_directory_ids the "incoming" directory ids from the POST request
   * @param array directory_ids_to_update the directory ids of entries that will be updated"updated" UMD Terp Persons
   * @param array unpublished_directory_ids the directory ids of "unpublished" UMD Terp Persons
   */
  private static function entriesToRepublish($incoming_directory_ids, $directory_ids_to_update, $unpublished_directory_ids) {
    $unpublished_directory_ids_in_incoming = array_intersect($incoming_directory_ids, $unpublished_directory_ids);
    $directory_ids_to_republish = array_diff($unpublished_directory_ids_in_incoming, $directory_ids_to_update);

    return $directory_ids_to_republish;
  }

  private function addEntries(array $directory_ids_to_add, array $incoming_json) {
    foreach($directory_ids_to_add as $directory_id) {
      $staff_dir_values = $incoming_json[$directory_id];
      $this->gateway->addEntry($staff_dir_values);
    }
  }

  private function updateEntries(array $directory_ids_to_update, array $current_json, array $incoming_json) {
    foreach($directory_ids_to_update as $directory_id) {
      $staff_dir_values = $incoming_json[$directory_id];
      $this->gateway->updateEntry($directory_id, $staff_dir_values);
    }
  }

  /**
   * Removes staff directory entries by setting them to unpublished.
   *
   * @param array $directory_ids_to_remove the directory ids to remove
   */
  private function removeEntries(array $directory_ids_to_remove) {
    foreach($directory_ids_to_remove as $directory_id) {
      $this->gateway->removeEntry($directory_id);
    }
  }

  /**
   * Restores previously removed staff directory entries by setting them to published.
   *
   * @param array $directory_ids_to_republish the directory ids to republish
   */
  private function republishEntries(array $directory_ids_to_republish) {
    foreach($directory_ids_to_republish as $directory_id) {
      $this->gateway->republishEntry($directory_id);
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

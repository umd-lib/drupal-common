<?php

/**
 * @file
 * Definition of Drupal\aleph_connector\Controller\AlephController
 */

namespace Drupal\aleph_connector\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Controller class for Aleph services.
 */
class AlephController extends ControllerBase implements TrustedCallbackInterface {

  protected $config;

  public function __construct() {
    $this->config = \Drupal::config('aleph_connector.settings');
  }

  /**
   * Generate sample page content for umd-examples page.
   */
  public function availabilityField($bibnum, $availability_data) {
    $processed_date = null;
    if ($raw_date = $availability_data['mindue']) {
      // $tz = new DateTimeZone('EST');
      try {
        // 2021-07-30-16:30
        $datetime_p = DateTimePlus::createFromFormat('YmdHi', $raw_date);
        $processed_date = $datetime_p->format('g:i A \o\n F j, Y');
      }
      catch (\InvalidArgumentException $e) {
        \Drupal::messenger()->addError('fail1: ' . $e->getMessage());
      }
      catch (\UnexpectedValueException $e) {
        \Drupal::messenger()->addError('fail2: ' . $e->getMessage());
      }
    }
    return [
      '#theme' => 'aleph_equipment_available',
      '#equipment_count' => $availability_data['available'],
      '#equipment_mindue' => $processed_date,
      '#equipment_sysnum' => $bibnum,
    ];
  }

  public function getEquipmentData() {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->getAlephBase() . $this->getEquipmentEndpoint());
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);

    if (!curl_errno($curl)) {
      if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        \Drupal::messenger()->addError(t('Connection error.'));
        return FALSE; 
      }
    } else {
      \Drupal::messenger()->addError(t('Connection error.'));
      return FALSE;
    }

    $xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
    $rows = $xml->xpath('//tr');
    $aleph_data = [];
    foreach ($rows as $row) {
      $vals = $row->xpath('//td[@class]');
      $row_data = [];
      $tmp_sysnum = null;
      foreach ($row->td as $td) {
        if ((string)$td->attributes()['class'] == 'sysnum') {
          $tmp_sysnum = (string)$td;
        }
        if ((string)$td->attributes()['class'] == 'available') {
          $row_data['available'] = (int)$td;
        }
        if ((string)$td->attributes()['class'] == 'mindue') {
          $row_data['mindue'] = (string)$td;
        }
      }
      if ($tmp_sysnum != null) {
        $aleph_data[$tmp_sysnum] = $row_data;
      }
    }
    curl_close($curl);
    if (count($aleph_data) > 0) {
      return $aleph_data;
    }
    return FALSE;
  }

  public function getBibnumData($bibnum) {
    if (!$equipment_data = $this->getEquipmentData()) {
      // Log this
      return FALSE;
    }
    return $equipment_data;
    if (!empty($equipment_data[$bibnum])) {
      return $this->availabilityField($bibnum, $equipment_data[$bibnum]); 
    }
  }

  /**
   * Getters
   *
   * Consider spawning this off into a util class
   */
  public function getQueryField() {
    return $this->config->get('equipment_query_field');
  }

  public function getAlephBase() {
    return $this->config->get('aleph_base');
  }

  public function getEquipmentEndpoint() {
    return $this->config->get('equipment_endpoint');
  }

  /**
   * Return count of entities of a given type.
   *
   * @param
   *   type - Entity Type (e.g., node, user, term)
   * @return
   *   count - results count (int)
   * @see
   *   https://api.drupal.org/api/drupal/core!lib!Drupal.php/function/Drupal%3A%3AentityQuery/8.2.x
   */
  private function entityQuery($type) {
    $query = \Drupal::entityQuery($type)
      ->condition('status', 1);

    return $query->count()->execute();
  }

  /**
   * Return count of solr documents of a given type.
   *
   * @param
   *   type - Entity Type
   * @return
   *   count - results count (int)
   * @see
   *   https://www.drupal.org/docs/8/modules/search-api/developer-documentation/executing-a-search-in-code
   */
  private function solrQuery($type) {
    if ($index = \Drupal\search_api\Entity\Index::load('drupal')) {
      $query = $index->query();
      $query->addCondition('search_api_datasource', 'entity:' . $type);
      return $query->execute()->getResultCount();
    }
    return false;

    /**
     * Alternately process results though this should generally be handled
     * using Drupal Views.
     *
     * $results =  $query->execute();
     * $items = $results->getResultItems();
     * $item = reset($items);
     * if (!empty($item)) {
     *  if ($object_title = $item->getField('display_title')->getValues()[0]) {
     *    return $object_title;
     *  }
     * }
     */
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['availabilityField'];
  }
}

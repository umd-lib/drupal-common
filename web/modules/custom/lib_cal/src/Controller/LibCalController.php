<?php
/**
 * @file
 * Definition of Drupal\lib_cal\Controller\LibCalController
 */

namespace Drupal\lib_cal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\lib_cal\Helper\LibCalSettingsHelper;
use Drupal\lib_cal\Helper\LibCalApiHelper;
use Drupal\Core\Security\TrustedCallbackInterface;
 /**
  * Implementation of LibCalController
  */
  class LibCalController {

    private $cid;
    private $configHelper;

    public function __construct() {
      $this->configHelper = LibCalSettingsHelper::getInstance();
      $this->cid = 'lib_cal:' . \Drupal::languageManager()
        ->getCurrentLanguage()
        ->getId();
    }

    public function getEvents($limit=3) {
      $events = NULL;
      $cached_limit = $this->getCachedEventsCount();
      if ($cached_limit >= $limit && $cache = \Drupal::cache()->get($this->cid)) {
        $events = $cache->data;
      }
      else {
        $req_limit = $cached_limit > $limit ? $cached_limit : $limit;
        $events = $this->getEventsFromApi($req_limit);
        if ($events) {
          \Drupal::cache()->set($this->cid . '_count', $req_limit);
          \Drupal::cache()->set($this->cid, $events, time() + 360);
        } else {
          return FALSE;
        }
      }
      return array_slice($events, 0, $limit);
    }

    private function getCachedEventsCount() {
      if ($cache = \Drupal::cache()->get($this->cid . '_count')) {
        return $cache->data;
      }
      return 0;
    }
  
    public function updateEquipmentDataCache($limit=3) {
      $cached_limit = $this->getCachedEventsCount();
      if ($cached_limit > $limit) {
        $limit = $cached_limit;
      }
      $events = $this->getEventsFromApi($limit);
      if ($events) {
        \Drupal::cache()->set($this->cid . '_count', $limit);
        \Drupal::cache()->set($this->cid, $events, time() + 360);
      }
    }
  
    
    public function getEventsFromApi($limit=3) {
      $endpoint = $this->configHelper->getEndpoint();
      $client_id = $this->configHelper->getClientID();
      $client_secret = $this->configHelper->getClientSecret();
      $calendar_id = $this->configHelper->getCalendarID();
      
      // Verify configuration
      if ($endpoint == null) {
        \Drupal::logger('lib_cal')->notice('LibCal API Configuration missing!');
        return FALSE;
      }

      $apiHelper = LibCalApiHelper::getInstance($endpoint, $endpoint, $client_id, $client_secret);
      $events = $apiHelper->getEvents($calendar_id, $limit);
      return $events;
    }

    /**
     * {@inheritDoc}
     */
    public static function trustedCallbacks() {
      return ['getEvents'];
    }
  }

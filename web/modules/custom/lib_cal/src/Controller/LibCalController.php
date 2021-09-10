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

    private $configHelper;

    public function __construct() {
      $this->configHelper = LibCalSettingsHelper::getInstance();
    }

    public function getEvents($limit=3) {
      $endpoint = $this->configHelper->getEndpoint();
      $client_id = $this->configHelper->getClientID();
      $client_secret = $this->configHelper->getClientSecret();
      $calendar_id = $this->configHelper->getCalendarID();
      
      // Verify configuration
      if ($endpoint == null) {
        // LOG ERROR
        return FALSE;
      }

      $apiHelper = LibCalApiHelper::getInstance($endpoint, $client_id, $client_secret);
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
<?php

/**
 * @file
 * Definition of Drupal\lib_cal\Helper\LibCalSettingsHelper
 */

namespace Drupal\lib_cal\Helper;

/**
 * Helper class for retrieving search target settings
 */
class LibCalSettingsHelper {

  // Constants
  const SETTINGS = 'lib_cal.settings';
  const ENDPOINT = 'lib_cal_endpoint';
  const CLIENT_ID = 'lib_cal_client_id';
  const CLIENT_SECRET = 'lib_cal_client_secret';
  const CALENDAR_ID = 'lib_cal_calendar_id';

  protected $config;

  private static $instance;

  private function __construct() {
    $this->config = \Drupal::config(static::SETTINGS);
  }

  public static function getInstance()
  {
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getEndpoint() {
    return $this->config->get(static::ENDPOINT);
  }

  public function getClientID() {
    return $this->config->get(static::CLIENT_ID);
  }

  public function getClientSecret() {
    return $this->config->get(static::CLIENT_SECRET);
  }

  public function getCalendarID() {
    return $this->config->get(static::CALENDAR_ID);
  }
} 

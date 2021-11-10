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
  const HOURS_ENDPOINT = 'lib_hours_endpoint';
  const CLIENT_ID = 'lib_cal_client_id';
  const CLIENT_SECRET = 'lib_cal_client_secret';
  const CALENDAR_ID = 'lib_cal_calendar_id';
  const LIBRARIES = 'lib_hours_libraries';
  const SHADY_GROVE = 'lib_hours_shady_grove';
  const ALL_LIBRARIES = 'lib_hours_all_libraries';

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

  public function getOther(String $key) {
    return $this->config->get($key);
  }

  public function getEndpoint() {
    return $this->config->get(static::ENDPOINT);
  }

  public function getHoursEndpoint() {
    return $this->config->get(static::HOURS_ENDPOINT);
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

  public function getAllLibraries() {
    return $this->config->get(static::ALL_LIBRARIES);
  }

  public function getShadyGrove() {
    return $this->config->get(static::SHADY_GROVE);
  }

  public function getLibraries() {
    return $this->config->get(static::LIBRARIES);
  }

  public function getLibrariesOptions() {
    $libs = $this->config->get(static::LIBRARIES);

    $values = [];

    $list = explode("\n", $libs);
    $list = array_map('trim', $list);

    foreach ($list as $position => $text) {
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = strtolower(trim($matches[1]));
        $value = trim($matches[2]);
      }
      $values[$key] = $value;
    }
    return $values;
  }
} 

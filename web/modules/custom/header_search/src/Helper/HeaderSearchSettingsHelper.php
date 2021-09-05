<?php

/**
 * @file
 * Definition of Drupal\header_search\Controller\HeaderSearchSettingsHelper
 */

namespace Drupal\header_search\Helper;

/**
 * Helper class for retrieving search target settings
 */
class HeaderSearchSettingsHelper {

  protected $config;

  private static $instance;

  private function __construct() {
    $this->config = \Drupal::config('header_search.settings');
  }

  public static function getInstance()
  {
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  // 
  public function parseSearchTargets($multiline_str) {
    $values = [];

    $list = explode("\n", $multiline_str);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = trim($matches[1]);
        $value = trim($matches[2]);
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    return $values;
  }

  public function convertSearchTargetsToString($targets) {
    $target_str = '';
    foreach($targets as $name => $url) {
      $target_str = $target_str . "$name|$url\n";
    }
    return $target_str;
  }

  public function getSearchTargetOptions() {
    $target_names = array_keys($this->config->get('search_targets'));
    return array_combine($target_names, $target_names);
  }

  public function getSearchTargetUrl($target) {
    $targets = $this->config->get('search_targets');
    return $targets[$target];
  }
} 

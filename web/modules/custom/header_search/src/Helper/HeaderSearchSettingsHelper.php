<?php

namespace Drupal\header_search\Helper;

/**
 * Helper class for retrieving search target settings.
 */
class HeaderSearchSettingsHelper {

  /**
   * The module configuration.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The singleton instance.
   *
   * @var Drupal\header_search\Helper\HeaderSearchSettingsHelper
   */
  private static $instance;

  /**
   * Constructor.
   */
  private function __construct() {
    $this->config = \Drupal::config('header_search.settings');
  }

  /**
   * Returns the single instance of this class.
   *
   * @return HeaderSearchSettingsHelper
   *   Tthe singleton instance of this class.
   */
  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function parseSearchTargets($multiline_str) {
    $values = [];

    $list = explode("\n", $multiline_str);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    foreach ($list as $position => $text) { // phpcs:ignore
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
    foreach ($targets as $name => $url) {
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

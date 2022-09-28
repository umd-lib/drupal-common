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

  /**
   * An associative array of search target names.
   *
   * @return array
   *   An associative array of search target names, suitable for use in the
   *   "options" property of a "radios" form element.
   */
  public function getSearchTargetOptions() {
    $target_names = array_keys($this->config->get('search_targets'));
    return array_combine($target_names, $target_names);
  }

  /**
   * Returns the URL for the given search target.
   *
   * @param string $target
   *   The search target to return the URL of.
   *
   * @return string
   *   The URL for the given search target.
   */
  public function getSearchTargetUrl($target) {
    $targets = $this->config->get('search_targets');
    return $targets[$target]['url'];
  }

  /**
   * Returns the default placeholder text to display in the search textfield.
   *
   * @return string
   *   The default placeholder text to display in the search textfield.
   */
  public function getDefaultSearchPlaceholder() {
    return $this->config->get('default_placeholder') ?? "";
  }

  public function getHelpUrl() {
    return $this->config->get('help_url') ?? "";
  }

}

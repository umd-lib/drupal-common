<?php

namespace Drupal\hero_search\Helper;

/**
 * Helper class for retrieving search target settings.
 */
class HeroSearchSettingsHelper {

  const LINK_FIELDS = [
    'button1' => 'Button 1',
    'button2' => 'Button 2',
    'button3' => 'Button 3',
    'button4' => 'Button 4',
    'top_right_link' => 'Top Right Link',
    'bottom_left_link' => 'Bottom Left Link',
    'bottom_right_link' => 'Bottom Right Link',
  ];

  /**
   * The module configuration.
   *
   * @var Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The singleton instance.
   *
   * @var Drupal\hero_search\Helper\HeroSearchSettingsHelper
   */
  private static $instance;

  /**
   * Constructor.
   */
  private function __construct() {
    $this->config = \Drupal::config('hero_search.settings');
  }

  /**
   * Returns the single instance of this class.
   *
   * @return HeroSearchSettingsHelper
   *   Tthe singleton instance of this class.
   */
  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Generic array gathering class.
   *
   * @return array
   *   An associative array of configuration information.
   */
  public function getConfigArray($field_name, $value_name) {
    $field_values = $this->config->get($field_name);
    foreach ($field_values as $field_value => &$val) {
      // Append the search_target to the associative array, so that the
      // configuration can be associated with one of the search options.
      $val[$value_name] = $field_value;
    }
    return $field_values;
  }

  /**
   * An associative array of configuration information for search targets.
   *
   * @return array
   *   An associative array of configuration information for search targets.
   */
  public function getSearchTargets() {
    return $this->getConfigArray('search_targets', 'search_target');
  }

  /**
   * An associative array of configuration information for Discover links.
   *
   * @return array
   *   An associative array of configuration information for Discover links.
   */
  public function getDiscoverLinks() {
    return $this->getConfigArray('discover_links', 'discover_link');
  }

  /**
   * An associative array of configuration information for Discover links.
   *
   * @return array
   *   An associative array of configuration information for Discover links.
   */
  public function getSearchMoreLinks() {
    return $this->getConfigArray('search_more_links', 'search_more_link');
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
   * Returns the title to display in the search block.
   *
   * @return string
   *   The title to display in the search block.
   */
  public function getSearchTitle() {
    return $this->config->get('title');
  }

  /**
   * Returns the alert to display in the search block.
   *
   * @return string
   *   The alert to display in the search block.
   */
  public function getAlert() {
    return $this->config->get('hero_search_alert');
  }

  /**
   * Returns the top content to display in the search block.
   *
   * @return string
   *   Content to display in the search block.
   */
  public function getTopContent() {
    return $this->config->get('top_content');

  }

  /**
   * Returns the bottom content to display in the search block.
   *
   * @return string
   *   Content to display in the search block.
   */
  public function getBottomContent() {
    return $this->config->get('bottom_content');
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

  /**
   * Returns an assoc. array of configured link info for the given name or NULL.
   *
   * @param string $name
   *   The name to look up the configured link information.
   *
   * @return array
   *   An associative array of link information for the given name, or NULL.
   */
  public function getLinkField($name) {
    $url = $this->config->get($name . '_url');
    return $url == NULL ? NULL : [
      'url' => $url,
      'text' => $this->config->get($name . '_text'),
      'title' => $this->config->get($name . '_title'),
    ];
  }

  /**
   * Returns a (possibly empty) array of alternate search information.
   *
   * @return array
   *   A (possibly empty) array of associative arrays containing information
   *   about all alternate search links from the configuration.
   */
  public function getAlternateSearches() {
    $search_targets = $this->config->get('search_targets');
    $alternate_searches = [];
    foreach ($search_targets as $search_target => $val) {
      $has_alternate = isset($val['alternate']);
      if ($has_alternate) {
        // Append the search_target to the associative array, so the alternate
        // can be associated with one of the search options.
        $val['alternate']['search_target'] = $search_target;
        $alternate_searches[] = $val['alternate'];
      }
    }
    return $alternate_searches;
  }

}

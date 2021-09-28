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

  protected $config;

  private static $instance;

  private function __construct() {
    $this->config = \Drupal::config('hero_search.settings');
  }

  public static function getInstance() {
    if (is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getSearchTargetOptions() {
    $target_names = array_keys($this->config->get('search_targets'));
    return array_combine($target_names, $target_names);
  }

  public function getSearchTargetUrl($target) {
    $targets = $this->config->get('search_targets');
    return $targets[$target]['url'];
  }

  public function getSearchTitle() {
    return $this->config->get('title');
  }

  public function getSearchPlaceholder() {
    return $this->config->get('placeholder');
  }

  public function getLinkField($name) {
    $url = $this->config->get($name . '_url');
    return $url == NULL ? NULL : [
      'url' => $url,
      'text' => $this->config->get($name . '_text'),
      'title' => $this->config->get($name . '_title'),
    ];
  }

  public function getAlternateSearches() {
    $search_targets = $this->config->get('search_targets');
    $alternate_searches = [];
    foreach ($search_targets as $search_target => $val) {
      $has_alternate = isset($val['alternate']);
      if ($has_alternate) {
        $val['alternate']['search_target'] = $search_target;
        $alternate_searches[] = $val['alternate'];
      }
    }
    return $alternate_searches;
  }

}

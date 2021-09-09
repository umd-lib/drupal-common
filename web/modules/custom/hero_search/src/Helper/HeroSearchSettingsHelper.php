<?php

/**
 * @file
 * Definition of Drupal\hero_search\Controller\HeroSearchSettingsHelper
 */

namespace Drupal\hero_search\Helper;

/**
 * Helper class for retrieving search target settings
 */
class HeroSearchSettingsHelper {

  const LINK_FIELDS = [
    'button1' => 'Button 1',
    'button2' => 'Button 2',
    'button3' => 'Button 3',
    'button4' => 'Button 4',
    'advanced_search' => 'Advanced Search',
    'top_right_link' => 'Top Right Link',
    'bottom_left_link' => 'Bottom Left Link',
    'bottom_right_link' => 'Bottom Right Link',
  ];

  protected $config;

  private static $instance;

  private function __construct() {
    $this->config = \Drupal::config('hero_search.settings');
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

  public function getSearchTitle() {
    return $this->config->get('title');
  }

  public function getSearchPlaceholder() {
    return $this->config->get('placeholder');
  }

  public function getLinkField($name) {
    $url = $this->config->get($name . '_url');
    return $url == null ? null : [
      'url' => $url,
      'text' => $this->config->get($name . '_text'),
      'title' => $this->config->get($name . '_title'),
    ];
  }
}

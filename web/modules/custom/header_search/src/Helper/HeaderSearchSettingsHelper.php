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

  const SEARCH_TARGETS = [
    "all" =>  'All',
    "umd_catalog" =>  'UMD CATALOG',
    "worldcat" =>  'WORLDCAT',
    "databases" =>  'DATABASES',
    "research_guides" =>  'RESEARCH GUIDES',
    "faqs" =>  'FAQS',
    "website" =>  'WEBSITE',
  ];

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

  public function getSearchTargets() {
    return static::SEARCH_TARGETS;
  }

  public function getSearchTargetUrl($target) {
    return $this->config->get($target);
  }
} 

<?php

/**
 * @file
 * Definition of Drupal\aleph_connector\Controller\AlephxConfigHelper
 */

namespace Drupal\aleph_connector\Helper;

class AlephxConfigHelper {

  protected $config;

  private static $instance;

  private function __construct() {
    $this->config = \Drupal::config('aleph_connector.xsettings');
  }

  public static function getInstance()
  {
    if ( is_null( self::$instance ) )
    {
      self::$instance = new self();
    }
    return self::$instance;
  }

  public function getBase() {
    return $this->config->get('base');
  }

  public function getAdmLibrary() {
    return $this->config->get('adm_library');
  }

  public function getBibLibrary() {
    return $this->config->get('bib_library');
  }

  public function getSubLibrary() {
    return $this->config->get('sub_library');
  }

  public function getCollection() {
    return $this->config->get('collection');
  }

}
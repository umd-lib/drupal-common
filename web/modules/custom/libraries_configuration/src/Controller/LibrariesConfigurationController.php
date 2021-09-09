<?php

namespace Drupal\libraries_configuration\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines LibrariesConfigurationController class.
 */
class LibrariesConfigurationController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $heading = '<h3>' . t("Libraries' Configuration") . '</h3>';
    $content = 'The libraries custom module configuration forms are available in individual tabs';
    $para = '<p>' . t($content) . '</p>';
    return [
      '#type' => 'markup',
      '#markup' => $heading . $para,
    ];
  }

}
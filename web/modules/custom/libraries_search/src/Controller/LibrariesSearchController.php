<?php

namespace Drupal\libraries_search\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Defines LibrariesSearchController class.
 */
class LibrariesSearchController extends ControllerBase {

  /**
   * Display the markup.
   *
   * @return array
   *   Return markup array.
   */
  public function content() {
    $heading = '<h3>' . t("Libraries' Search") . '</h3>';
    $content = 'The libraries search modules configuration forms are available in individual tabs';
    $para = '<p>' . t($content) . '</p>';
    return [
      '#type' => 'markup',
      '#markup' => $heading . $para,
    ];
  }

}
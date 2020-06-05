<?php

namespace Drupal\mirador_viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;

class DisplayMiradorController extends ControllerBase {

  public function ViewObject($object_id) {
    $config = \Drupal::config('mirador_viewer.adminsettings');

    return [
      '#theme' => 'mirador_viewer',
      '#title' => $this->t('test object'),
      '#iiif_server' => $config->get('iiif_server'),
      '#error_message' => $config->get('error_message'),
      '#object_id' => $object_id,
    ];
  }

}

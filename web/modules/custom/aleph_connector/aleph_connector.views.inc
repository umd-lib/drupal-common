<?php

use Drupal\search_api\Entity\Index;
use Drupal\search_api\IndexInterface;

/**
 * Implements hook_views_data_alter().
 */
function aleph_connector_views_data_alter(array &$data) {
  /** @var \Drupal\search_api\IndexInterface $index */
  foreach (Index::loadMultiple() as $index) {
    $index_key = 'search_api_index_' . $index->id();
    $data[$index_key]['aleph_equipment_available'] = array(
      'title' => t('Equipment Availability - count'),
      'help' => t('Given a bibnum, how many items available in Aleph'),
      'field' => array(
        'title' => t('Equipment Availability - count'),
        'id' => 'aleph_equipment_available',
       ),
    );
    $data[$index_key]['aleph_equipment_mindue'] = array(
      'title' => t('Equipment Availability - count'),
      'help' => t('Given a bibnum, when is next available'),
      'field' => array(
        'title' => t('Equipment Availability - due'),
        'id' => 'aleph_equipment_mindue',
       ),
    );
  }
}

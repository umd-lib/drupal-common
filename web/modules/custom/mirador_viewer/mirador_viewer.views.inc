<?php

use Drupal\search_api\Entity\Index;
use Drupal\search_api\IndexInterface;

/**
 * Implements hook_views_data_alter().
 */
function mirador_viewer_views_data_alter(array &$data) {
  /** @var \Drupal\search_api\IndexInterface $index */
  foreach (Index::loadMultiple() as $index) {
    $index_key = 'search_api_index_' . $index->id();
    $data[$index_key]['fcrepo_id_hash'] = array(
      'title' => t('FCRepoID'),
      'help' => t('Use to generate hash from Fedora ID'),
      'field' => array(
        'title' => t('FCRepoID'),
        'id' => 'fcrepo_id_hash',
       ),
    );
    $data[$index_key]['mirador_viewer'] = array(
      'title' => t('Mirador Viewer'),
      'field' => array(
        'title' => t('Mirador Viewer'),
        'id' => 'mirador_viewer',
      ),
    );
    $data[$index_key]['fcrepo_hash_reverse'] = array(
      'real field' => 'id',
      'argument' => array(
        'title' => t('FCRepo ID Reverse Override'),
        'help' => t('Used to generate Fedora ID from truncated hash value'),
        'id' => 'fcrepo_hash_reverse_id',
      ),
    );
  }
}


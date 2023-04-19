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
    $data[$index_key]['fcrepo_thumbnails'] = array(
      'title' => t('FCThumbnails'),
      'help' => t('Use to generate thumbnails from PCDM manifest'),
      'field' => array(
        'title' => t('FCThumbnails'),
        'id' => 'fcrepo_thumbnails',
       ),
    );
    $data[$index_key]['fc_annotation'] = array(
      'title' => t('FCAnnotation'),
      'help' => t('Fedora annotation output using the extracted_text_source field'),
      'field' => array(
        'title' => t('FCAnnotation'),
        'id' => 'fc_annotation',
       ),
    );
    $data[$index_key]['mirador_viewer'] = array(
      'title' => t('Mirador Viewer'),
      'field' => array(
        'title' => t('Mirador Viewer'),
        'id' => 'mirador_viewer',
      ),
    );
    $data[$index_key]['static_viewer'] = array(
      'title' => t('Static Viewer'),
      'field' => array(
        'title' => t('Static Viewer'),
        'id' => 'static_viewer',
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


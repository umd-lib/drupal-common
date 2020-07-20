<?php

/**
 * Implements hook_views_data_alter().
 */
function mirador_viewer_views_data_alter(array &$data) {
// dsm($data);
  $data['search_api_index_posters']['fcrepo_id_hash'] = array(
    'title' => t('FCRepo ID'),
    'field' => array(
      'title' => t('FCRepo ID'),
      'id' => 'fcrepo_id_hash',
     ),
  );
  $data['search_api_index_posters']['mirador_viewer'] = array(
    'title' => t('Mirador Viewer'),
    'field' => array(
      'title' => t('Mirador Viewer'),
      'id' => 'mirador_viewer',
    ),
  );
  $data['search_api_index_posters']['fcrepo_hash_reverse'] = array(
    'real field' => 'id',
    'argument' => array(
      'title' => t('FCRepo ID Reverse Override'),
      'id' => 'fcrepo_hash_reverse_id',
    ),
  );
}


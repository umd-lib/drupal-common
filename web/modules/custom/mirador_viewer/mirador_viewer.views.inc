<?php

/**
 * Implements hook_views_data_alter().
 */
function mirador_viewer_views_data_alter(array &$data) {
// dsm($data);
  $data['search_api_index_posters']['fcrepo_id_hash'] = array(
    'title' => t('FCRepo ID Hash'),
    'field' => array(
      'title' => t('FCRepo ID Hash'),
      'id' => 'fcrepo_id_hash',
     ),
  );
}


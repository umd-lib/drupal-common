<?php

/**
 * Implements hook_views_data_alter()
 */
function top_textbooks_views_data_alter(array &$data) {
  $data['search_api_index_top_textbooks']['textbook_availability_field'] = array(
    'title' => t('Textbook Availability'),
    'help' => t('Availability information for textbooks.'),
    'field' => array(
      // ID of the field handler
      'id' => 'textbook_availability_field',
    ),
  );
}
<?php

/**
 * Implements hook_views_data_alter().
 */
function umd_examples_views_data_alter(array &$data) {
  $data['node']['umd_examples_field'] = array(
    'title' => t('Demo Field'),
    'field' => array(
      'title' => t('Demo Field'),
      'help' => t('This is a demo field.'),
      'id' => 'umd_demo_field',
    ),
  );
}

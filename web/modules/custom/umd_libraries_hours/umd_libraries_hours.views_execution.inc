<?php

/**
* Implements hook_views_query_alter().
*/

function umd_libraries_hours_views_query_alter(\Drupal\views\ViewExecutable $view, \Drupal\views\Plugin\views\query\Sql $query) {
  if ($view->id() == 'hours_today') {
    $start = strtotime('today 00:10');
    $end = strtotime('tomorrow 00:10');
    $query->where[] = array(
      'conditions' => array(
       array(
        'field' => 'node__field_hours.field_hours_end_value',
        'value' => [$start, $end],
        'operator' => 'BETWEEN',
       ),
      ),
      'type' => 'AND',
    );
  }
}

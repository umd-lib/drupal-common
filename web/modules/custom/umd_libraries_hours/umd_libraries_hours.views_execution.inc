<?php

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\Plugin\views\query\QueryPluginBase;

/**
* Implements hook_views_query_alter().
*/

function umd_libraries_hours_views_query_alter($view, $query) {
  if ($view->id() == 'hours_today' && ($query instanceof \Drupal\views\Plugin\views\query\Sql)) {
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
  $one_week = 604800;
  if ($view->id() == 'hours_weekly' && ($query instanceof \Drupal\views\Plugin\views\query\Sql)) {
    // PHP Week starts on Monday, and we want Sun-to-Sat
    $start = strtotime('sunday last week') + 600; 
    $end = strtotime('sunday this week') + 600;
    $has_filter = FALSE;
    foreach ($query->where as &$condition_group) {
      foreach ($condition_group['conditions'] as &$condition) {
        if (strpos($condition['field'], 'field_hours_end_value')) {
          $has_filter = TRUE;
          $filter = strstr($condition['field'], '=');
          if ($filter) {
            $epoch_str = (int)substr($filter, 2);
            $date_str = date("Y-m-d", substr($epoch_str, 0, 10));
            $start = strtotime("sunday last week $date_str") + 600;
            $end = strtotime("sunday this week $date_str") + 600;
            \Drupal::logger('hours_weekly')->notice("Provide Date Str $date_str");
            $condition['field'] = "node__field_hours.field_hours_end_value BETWEEN $start AND $end";
          }
        }
      }
    }
    if (!$has_filter) {
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
    \Drupal::logger('hours_weekly')->notice("Added filter from $start to $end");
  }
}

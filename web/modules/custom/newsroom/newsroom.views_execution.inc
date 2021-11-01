<?php

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\Plugin\views\query\QueryPluginBase;

/**
* Implements hook_views_query_alter().
* 
* The hook adds conditions to select the next/previous articles based on 
* the following conditions:
* 
* Previous Article (order matters): 
*  1. Article with the date less than current node
*  2. Article with same date and node id less than current node
* Note: The previous block in the view should have sort configured in the 
* following order:
*  1. Node ID Desc 
*  2. Date Desc 
* 
* Next Article (order matters): 
*  1. Article with same date and node id greater than current node
*  2. Article with the date greater than current node
* Note: The next block in the view should have sort configured in the 
* following order:
*  1. Date Asc 
*  2. Node Asc 
*/
function newsroom_views_query_alter($view, $query) {
  $id_field = 'node_field_data.nid';
  $id_val_param = ':node_field_data_nid';
  $id_val = NULL;
  $date_field = 'node__field_umdt_ct_article_date.field_umdt_ct_article_date_value';
  $date_val_param = ':node__field_umdt_ct_article_date_field_umdt_ct_article_date_value';
  $date_val = NULL;
  if ($view->id() == 'news_navigation' && ($query instanceof \Drupal\views\Plugin\views\query\Sql)) {
    \Drupal::logger('news_navigation')->notice('$view->current_display: ' . $view->current_display);
    foreach ($query->where as &$condition_group) {
      foreach ($condition_group['conditions'] as &$condition) {
        if (str_contains($condition['field'], $id_field)) {
          $matches = preg_grep("/$id_val_param/", array_keys($condition['value']));
          if (count($matches) == 1) {
            $id_val_param = $matches[0];
            $id_val = $condition['value'][$id_val_param];
          }
        }
      }
    }
    foreach ($query->where as &$condition_group) {
      foreach ($condition_group['conditions'] as &$condition) {
        if (str_contains($condition['field'], 'field_umdt_ct_article_date_value')) {
          $matches = preg_grep("/$date_val_param/", array_keys($condition['value']));
          if (count($matches) == 1) {
            $date_val_param = $matches[0];
            $date_val = $condition['value'][$date_val_param];
            unset($condition['value'][$date_val_param]);
          }
          $operator = $view->current_display == 'previous' ? '<' : '>';
          $cond1 = "($date_field = :date_val) AND ($id_field $operator :id_val)";
          $cond2 = "$date_field $operator :date_val";
          if ($view->current_display == 'previous') {
            $condition['field'] = "($cond2) OR ($cond1)";
          } else {
            $condition['field'] = "($cond1) OR ($cond2)";
          }
          $condition['value'][':date_val'] = $date_val;
          $condition['value'][':id_val'] = $id_val;
        }
      }
    }
  }
}

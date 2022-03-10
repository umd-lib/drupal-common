<?php

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\query\Sql;
use Drupal\views\Plugin\views\query\QueryPluginBase;

/**
* Implements hook_views_query_alter().
*/
function bento_views_query_alter($view, $query) {
  if ($view->id() == 'bento_demo') {
    dsm($query);
  }
}

function bento_views_query_substitutions(ViewExecutable $view) {
  // dsm($view->id());
}

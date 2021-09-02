<?php

use Drupal\taxonomy\Entity\Term;
use Drupal\views\ResultRow;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;

/**
 * @file
 * Theme for filterizr_display views.
 */
function template_preprocess_filterizr_display(&$variables) {
  // View options set by user.
  $options = $variables['view']->style_plugin->options;
  $harvested_filters = [];
  if (!empty(trim($options['filter_field']))) {
    // For Option 2
    $filter_field = $options['filter_field'];
    foreach ($variables['view']->result as $result) {
      $node = $result->_entity;
      if ($node->hasField($filter_field)) {
        $tid = $node->get($filter_field)->getString();
        $term_name = \Drupal\taxonomy\Entity\Term::load($tid)->get('name')->value;
        $harvested_filters[$tid] = $term_name;
      } 
    }
  } elseif (!empty(trim($options['taxonomy_vocab']))) {
    // For Option 1
    $vid = $options['taxonomy_vocab'];	// Vocab ID
    $v_depth = NULL;
    $v_parent = 0;	// 0 would be vocab root
    if (!empty(trim($options['taxonomy_depth']))) {
      $v_depth = $options['taxonomy_depth'];
    }
    if (!empty(trim($options['taxonomy_parent']))) {
      // We need to *try* and get the Term ID from the taxonomy string provided.
      $parent_string = $options['taxonomy_parent'];
      $properties = [];
      if (!empty($parent_string)) {
        $properties['name'] = $parent_string;
        $properties['vid'] = $vid;
        $parent_term = \Drupal::entityManager()->getStorage('taxonomy_term')->loadByProperties($properties);
        if (!empty($parent_term)) {
          $pterm = reset($parent_term);
          $v_parent = $pterm->id() > 0 ? $pterm->id() : 0;
        }
      }
    }
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $v_parent, $v_depth, TRUE);
    foreach ($terms as $term) {
      $harvested_filters[$term->id()] = $term->getName();
    }
  } else {
    return NULL;	// Something isn't right. Short circuit and lick wounds.
  }
  if (count($harvested_filters) > 0) {
    asort($harvested_filters);
    // Update options for twig.
    $variables['options'] = $options;
    $variables['header_terms'] = $harvested_filters;
    template_preprocess_views_view_unformatted($variables);	// This is necessary for row output.
  }
}

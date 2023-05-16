<?php

/**
 * @file
 * Functions for umd_terp theme.
 */

use Drupal\node\Entity\Node;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Make all theme settings available to insert in pages/nodes/etc.
 *
 * Added to pages by default in hook_preproccess_node above.
 * Can extend in sub-theme to nodes/etc if needed.
 *
 * Use in theme by var name, as {{twitter_link}} /etc.
 */
function _umd_terp_extend_theme_variables(&$variables) {
  $variables['site_name'] = \Drupal::config('system.site')->get('name');
  $variables['theme_name'] = \Drupal::theme()->getActiveTheme()->getName();
  $variables['site_slogan'] = \Drupal::config('system.site')->get('slogan');
  // $tpath = \Drupal::service('file_url_generator')->generateString($logo_path);
  $drupal_theme_path = \Drupal::service('extension.list.theme')->getPath($variables['theme_name']);
  $tpath = \Drupal::service('file_url_generator')->generateString($drupal_theme_path);
  $variables['theme_path'] = $tpath;
  // $variables['theme_path'] = file_url_transform_relative(file_create_url($drupal_theme_path));
  $logo_path = theme_get_setting('logo.url');
  if (!empty($logo_path)) {
    $file_url_path = \Drupal::service('file_url_generator')->generateString($logo_path);
    $variables['logo_path'] = $file_url_path;
    // $variables['logo_path'] = file_url_transform_relative(file_create_url($logo_path));
  }
  $footer_logo = theme_get_setting('umd_terp_footer_logo_path');
  if (!empty($footer_logo)) {
    $footer_path = \Drupal::service('file_url_generator')->generateString($footer_logo);
    $variables['footer_logo_path'] = $footer_path;
    // $variables['footer_logo_path'] = file_url_transform_relative(file_create_url($footer_logo));
  }

  $theme_settings = \Drupal::config($variables['theme_name'] . '.settings');
  CacheableMetadata::createFromRenderArray($variables)
    ->addCacheableDependency($theme_settings)
    ->applyTo($variables);
  $variables += $theme_settings->getOriginal();
  // Add full assets_path.
  if (!empty($variables['umd_terp_assets_path'])) {
    $variables['assets_path'] = $variables['theme_path'] . $variables['umd_terp_assets_path'];
  }
}

/**
 * Custom function to help with previews/revisions.
 */
function _umd_terp_get_node_by_route() {
  $route_name = \Drupal::routeMatch()->getRouteName();
  $node = FALSE;
  switch ($route_name) {
    case 'entity.node.canonical':
      $node = \Drupal::routeMatch()->getParameter('node');
      break;

    case 'entity.node.preview':
      $node = \Drupal::routeMatch()->getParameter('node_preview');
      break;

    case 'entity.node.revision':
      $revision_id = \Drupal::routeMatch()->getParameter('node_revision');
      if (is_numeric($revision_id)) {
        $node = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($revision_id);
      } else {
        $node = $revision_id;
      }
      break;

    case 'entity.node.latest_version':
      $node = \Drupal::routeMatch()->getParameter('node');
      break;
  }
  if (is_numeric($node)) {
    $node = Node::load($node);
  }
  return !empty($node) ? $node : FALSE;
}

<?php

/**
 * @file
 * Hook reprocesses for umd_terp theme.
 */

use Drupal\Component\Utility\Html;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Core\Language\Language;

/**
 * Implements hook_preprocess_HOOK() for HTML document templates.
 */
function umd_terp_preprocess_html(&$variables) {
  $variables['is_front_page'] = \Drupal::service('path.matcher')->isFrontPage();
  // Add body classes based on path.
  $variables['current_path'] = \Drupal::service('path.current')->getPath();
  $variables['current_path_alias'] = ltrim(\Drupal::service('path_alias.manager')->getAliasByPath($variables['current_path']), '/');
  $variables['attributes']['class'][] = 'path--' . Html::cleanCssIdentifier($variables['current_path_alias'], ['/' => '--']);

  if ($variables['is_front_page']) {
    $variables['attributes']['class'][] = 'home';
  }
  else {
    $variables['attributes']['class'][] = 'internal';
  }
}

/**
 * Implements hook_preprocess_HOOK() for page templates.
 */
function umd_terp_preprocess_page(&$variables) {
  $variables['hero_image'] = '';
  $variables['hero_title'] = $variables['page']['#title'];

  // Make any custom theme settings available in the page tpl.
  _umd_terp_extend_theme_variables($variables);

  if ($node = _umd_terp_get_node_by_route()) {
    // Set hero details.
    if ($node->hasField('field_umdt_ct_hero_image') && $node->get('field_umdt_ct_hero_image')->entity) {
      $hero_uri = $node->get('field_umdt_ct_hero_image')->entity->get('field_media_image')->entity->getFileUri();
      $hero_alt = $node->get('field_umdt_ct_hero_image')->entity->get('field_media_image')->alt;
    }
    if (!empty($hero_uri)) {
      $variables['hero_image'] = ImageStyle::load('optimized')->buildUrl($hero_uri);
      $variables['hero_alt'] = $hero_alt;
    }

    switch ($node->getType()) {
      case 'umd_terp_basic_page':
        $variables['hide_sidebar'] = $node->get('field_umdt_ct_hide_sidebar')->value;
        $variables['right_sidebar'] = FALSE;
        if (isset($variables['umd_terp_sidebar_right']) && $variables['umd_terp_sidebar_right'] == TRUE) {
          $variables['right_sidebar'] = TRUE;
        }
        break;
    }
  }

}

/**
 * Implements hook_preprocess_breadcrumb() for node.html.twig.
 */
function umd_terp_preprocess_breadcrumb(&$variables) {
  $request = \Drupal::request();
  $route_match = \Drupal::routeMatch();
  if (($node = $route_match->getParameter('node')) && $variables['breadcrumb']) {
    if (is_numeric($node)) {
      $node = Node::load($node);
    }
    $variables['breadcrumb'][] = [
      'text' => $node->getTitle(),
    ];
    $variables['#cache']['contexts'][] = 'url';
  }
}

/**
 * Implements template_preprocess_node.
 *
 * Add template suggestions and classes.
 */
function umd_terp_preprocess_node(&$variables) {
  // Add node--node_type--view_mode.html.twig suggestions.
  $variables['theme_hook_suggestions'][] = 'node__' . $variables['elements']['#node']->bundle() . '__' . $variables['view_mode'];
  // Add node--view_mode.html.twig suggestions.
  $variables['theme_hook_suggestions'][] = 'node__' . $variables['view_mode'];
  // Add a class for the view mode.
  if (!$variables['teaser']) {
    $variables['content_attributes']['class'][] = 'view-mode-' . $variables['view_mode'];
  }
  // Allow use of theme varables in node templates.
  _umd_terp_extend_theme_variables($variables);

}

/**
 * Implements hook_preprocess_form_element().
 */
function umd_terp_preprocess_form_element(array &$variables) {
  if (!isset($variables['label'])) {
    return;
  }

  if (isset($variables['element']['#form_id'])) {
    $variables['label']['#form_id'] = $variables['element']['#form_id'];
  }
  if (isset($variables['element']['#type'])) {
    $variables['label']['#form_element_type'] = $variables['element']['#type'];
  }
}

/**
 * Implements hook_preprocess_menu_local_task().
 */
function umd_terp_preprocess_menu_local_task(&$variables) {
  $class = ['nav-link'];
  if ($variables['element']["#active"]) {
    $class[] = 'active';
  }
  $variables['element']['#link']['url']->setOption('attributes', ['class' => $class]);
}

/**
 * Prepares variables for media templates.
 *
 * Default template: media.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - media: An individual media for display.
 */
function umd_terp_preprocess_media(&$variables) {
  $variables['media'] = $variables['elements']['#media'];
}

/**
 * Prepares links for menus
 *
 * @param array $variables
 *   An associative array containing:
 *   - media: An individual media for display.
 */
function umd_terp_preprocess_links(&$variables) {
  $links = $variables['links'];
  if (!empty($links)) {
    $variables['links'] = [];
    $lang_man = \Drupal::service('language_manager');
    foreach ($links as $key => $link) {
      $item = $link;
      if (!empty($item['link'])) {
        if (!empty($item['link']['#options']['language'])) {
          $lang = $item['link']['#options']['language'];
          $lang_id = $lang->getId();
          $lang_name = $lang->getName();
          $item['lang_name'] = $lang_name;
          $item['lang_id'] = $lang_id;
          switch ($lang_id) {
            case "en":
              $item['nationAbbreviation'] = "US";
              $item['nation'] = "USA";
              $item['nativeName'] = "English";
              break;
            case "ja":
              $item['nationAbbreviation'] = "JP";
              $item['nation'] = "Japan";
              $item['nativeName'] = "日本語";
              break;
          }
          if (!empty($lang_id) && !empty($item['link']['#url'])) {
            $current_lang = $lang_man->getLanguage($lang_id);
            $url = $item['link']['#url'];
            $uri_string = $url->toUriString();
            $url_opts = ['language' => $current_lang];
            $new_url = $url::fromUri($uri_string, $url_opts);
            $item['href'] = $new_url->toString();
          }
        }
      }
      // Add the item to the list of links.
      $variables['links'][$key] = $item;
    }
  }
}

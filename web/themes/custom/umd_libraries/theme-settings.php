<?php

/**
 * @file
 * UMD Libraries (umd_libraries), add custom theme settings options here.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function umd_libraries_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {

  $form['umd_terp_social_settings']['hide_social_icons'] = [
    '#type' => 'checkbox',
    '#title' => t('Hide Social Icons'),
    '#default_value' => theme_get_setting('hide_social_icons'),
  ];

  // Header.
  $form['umd_libraries_header_settings'] = [
    '#type' => 'details',
    '#title' => t('UMD Libraries Header Settings'),
  ];
  $form['umd_libraries_header_settings']['show_digital_branding'] = [
    '#type' => 'checkbox',
    '#title' => t('Show Digital Branding'),
    '#description' => t('Display the Digital Collections text alongside the library logo.'),
    '#default_value' => theme_get_setting('show_digital_branding'),
  ];

}


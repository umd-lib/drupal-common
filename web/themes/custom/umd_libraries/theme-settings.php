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
  $form['umd_libraries_header_settings']['add_digital_branding_link'] = [
    '#type' => 'checkbox',
    '#title' => t('Link Digital'),
    '#description' => t('Link Digital header to digital.lib.umd.edu.'),
    '#default_value' => theme_get_setting('add_digital_branding_link'),
  ];
  $form['umd_libraries_header_settings']['override_site_logo_link'] = [
    '#type' => 'checkbox',
    '#title' => t('Override Site Link'),
    '#description' => t('Override site link to www.lib.umd.edu.'),
    '#default_value' => theme_get_setting('override_site_logo_link'),
  ];

  // Footer.
  $form['umd_libraries_footer_settings'] = [
    '#type' => 'details',
    '#title' => t('UMD Libraries Footer Settings'),
  ];
  $form['umd_libraries_footer_settings']['replace_email_text'] = [
    '#type' => 'checkbox',
    '#title' => t('Replace Email Text'),
    '#description' => t('Replace email address text with Contact Us'),
    '#default_value' => theme_get_setting('replace_email_text'),
  ];
  $form['umd_libraries_footer_settings']['exclude_mailto'] = [
    '#type' => 'checkbox',
    '#title' => t('Exclude Mailto'),
    '#description' => t('Exclude MAILTO for email field--for example, if a URL.'),
    '#default_value' => theme_get_setting('exclude_mailto'),
  ];
  $form['umd_libraries_footer_settings']['link_footer_logo'] = [
    '#type' => 'checkbox',
    '#title' => t('Link Footer Logo'),
    '#description' => t('This will link to public website.'),
    '#default_value' => theme_get_setting('link_footer_logo'),
  ];
}


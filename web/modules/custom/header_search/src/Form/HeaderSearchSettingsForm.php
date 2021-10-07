<?php

namespace Drupal\header_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\header_search\Helper\HeaderSearchSettingsHelper;

/**
 * Settings for Header Search target urls.
 */
class HeaderSearchSettingsForm extends ConfigFormBase {

  const SETTINGS = 'header_search.settings';

  /**
   * The HeaderSearchSettingsHelper instance.
   *
   * @var Drupal\header_search\Helper\HeaderSearchSettingsHelper
   */
  protected $configHelper;

  public function __construct() {
    $this->configHelper = HeaderSearchSettingsHelper::getInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'header-search-settings-form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Load the stored values to populate forms.
    $config = $this->config(static::SETTINGS);

    // @see samlauth_attrib module for an example of field to array (and reverse)
    $form['header_search_settings'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Configure the base search urls for the search targets. ' .
          'The url should include the search parameter as the last url query parameter key. ' .
          'Example: https://www.example.com/search?query=') . '</h3>',
    ];

    $form['search_targets'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search Targets'),
      '#description' => $this->t('One mapping per line with the format <strong>Searcher Name|Searcher URL</strong>.'),
      '#default_value' => $this->configHelper->convertSearchTargetsToString($config->get('search_targets')),
      '#rows' => 10,
      '#cols' => 100,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $err_msg = "The url should be of format https://DOMAIN/ENDPOINT?SEARCH_QEURY_PARAM=. Error urls: \n";
    $search_targets_str = $form_state->getValue('search_targets');
    $search_targets = $this->configHelper->parseSearchTargets($search_targets_str);
    $error_urls = [];
    foreach($search_targets as $name => $url) { // phpcs:ignore
      if (filter_var($url, FILTER_VALIDATE_URL) == FALSE) {
        array_push($error_urls, $url);
      }
      elseif (!str_ends_with($url, '=')) {
        array_push($error_urls, $url);
      }
    }
    if (count($error_urls) > 0) {
      $error_urls_str = implode("'\n'", $error_urls);
      $form_state->setErrorByName('search_targets', $this->t($err_msg) . "'$error_urls_str'");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);

    $search_targets_str = $form_state->getValue('search_targets');
    $search_targets = $this->configHelper->parseSearchTargets($search_targets_str);
    $config->set('search_targets', $search_targets);

    $config->save();
    parent::submitForm($form, $form_state);
  }

}

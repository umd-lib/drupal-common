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
      '#markup' => '<h3>' . t('Configure the base search urls for the search targets. ' .
          'The url should include the search parameter as the last url query parameter key. ' .
          'Example: https://www.example.com/search?query=') . '</h3>',
    ];

    foreach($this->configHelper->getSearchTargets() as $target => $name) {
      $form[$target] = [
        '#type' => 'url',
        '#title' => t($name),
        '#default_value' => $config->get($target),
        '#size' => 100,
        '#maxlength' => 100,
        '#required' => TRUE,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $err_msg = 'The url should be of format https://DOMAIN/ENDPOINT?SEARCH_QEURY_PARAM=';
    foreach($this->configHelper->getSearchTargets() as $target => $name) {
      $url = $form_state->getValue($target);
      if (!str_ends_with($url, '=')) {
        $form_state->setErrorByName($target, $this->t($err_msg));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);
    foreach($this->configHelper->getSearchTargets() as $target => $name) {
      $url = $form_state->getValue($target);
      $config->set($target, $url);
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }
}
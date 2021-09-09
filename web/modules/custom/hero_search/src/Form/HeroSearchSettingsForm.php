<?php
namespace Drupal\hero_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

use Drupal\hero_search\Helper\HeroSearchSettingsHelper;

/**
 * Settings for Hero Search target urls.
 */
class HeroSearchSettingsForm extends ConfigFormBase {

  const SETTINGS = 'hero_search.settings';

  protected $configHelper;

  public function __construct() {
    $this->configHelper = HeroSearchSettingsHelper::getInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hero-search-settings-form';
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

    $form['hero_search_settings'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('Configuration for the Hero Search block') . '</h3>',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => $config->get('title'),
      '#size' => 30,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $config->get('placeholder'),
      '#size' => 50,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    $form['search_targets'] = [
      '#type' => 'textarea',
      '#title' => t('Search Targets'),
      '#description' => t('One mapping per line with the format <strong>Searcher Name|Searcher URL</strong>.'),
      '#default_value' => $this->configHelper->convertSearchTargetsToString($config->get('search_targets')),
      '#rows' => 4,
      '#cols' => 100,
      '#required' => TRUE,
    ];

    foreach (HeroSearchSettingsHelper::LINK_FIELDS as $name => $title) {
      $form[$name] = [
        '#type' => 'fieldset',
        '#title' => t($title)
      ];
  
      $form[$name][$name . '_url'] = [
        '#type' => 'url',
        '#title' => t('URL'),
        '#default_value' => $config->get($name . '_url'),
        '#size' => 100,
        '#maxlength' => 100,
      ];
  
      $form[$name][$name . '_text'] = [
        '#type' => 'textfield',
        '#title' => t('Text'),
        '#default_value' => $config->get($name . '_text'),
        '#size' => 50,
        '#maxlength' => 60,
      ];
  
      $form[$name][$name . '_title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#default_value' => $config->get($name . '_title'),
        '#size' => 50,
        '#maxlength' => 60,
      ];
    }
    
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
    foreach($search_targets as $name => $url) {
      if (filter_var($url, FILTER_VALIDATE_URL) == false) {
        array_push($error_urls, $url);
      } elseif (!str_ends_with($url, '=')) {
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
    
    $config->set('title', $form_state->getValue('title'));
    $config->set('placeholder', $form_state->getValue('placeholder'));
    $search_targets_str = $form_state->getValue('search_targets');
    $search_targets = $this->configHelper->parseSearchTargets($search_targets_str);
    $config->set('search_targets', $search_targets);

    foreach(HeroSearchSettingsHelper::LINK_FIELDS as $name => $title) {
      $config->set($name . '_url', $form_state->getValue($name .'_url'));
      $config->set($name . '_text', $form_state->getValue($name .'_text'));
      $config->set($name . '_title', $form_state->getValue($name .'_title'));
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }
}

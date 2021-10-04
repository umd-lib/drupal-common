<?php

namespace Drupal\hero_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\hero_search\Helper\HeroSearchSettingsHelper;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings for Hero Search target urls.
 */
class HeroSearchSettingsForm extends ConfigFormBase {

  use \Drupal\Core\StringTranslation\StringTranslationTrait;

  const SETTINGS = 'hero_search.settings';

  /**
   * The logger instance.
   *
   * @var Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The HeroSearchSettingsHelper instance.
   *
   * @var Drupal\hero_search\Helper\HeroSearchSettingsHelper
   */
  protected $configHelper;

  /**
   * Constructor.
   *
   * @param Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger instance.
   */
  public function __construct(LoggerChannelInterface $logger) {
    $this->logger = $logger;
    $this->configHelper = HeroSearchSettingsHelper::getInstance();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.channel.hero_search')
    );
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
      '#markup' => '<h3>' . $this->t('Configuration for the Hero Search block') . '</h3>',
    ];

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $config->get('title'),
      '#size' => 30,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default Placeholder'),
      '#default_value' => $config->get('default_placeholder'),
      '#size' => 50,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];

    $form['search_targets'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search Targets'),
      '#description' => $this->t('YAML formatted Search Targets. See the README.md file.'),
      '#default_value' => Yaml::dump($config->get('search_targets')),
      '#rows' => 7,
      '#cols' => 100,
      '#required' => TRUE,
    ];

    foreach (HeroSearchSettingsHelper::LINK_FIELDS as $name => $title) {
      $form[$name] = [
        '#type' => 'fieldset',
        '#title' => $this->t($title),
      ];

      $form[$name][$name . '_url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#default_value' => $config->get($name . '_url'),
        '#size' => 100,
        '#maxlength' => 100,
      ];

      $form[$name][$name . '_text'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Text'),
        '#default_value' => $config->get($name . '_text'),
        '#size' => 50,
        '#maxlength' => 60,
      ];

      $form[$name][$name . '_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
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
    $search_targets_str = trim($form_state->getValue('search_targets'));

    // A starting line with "---" is required by the YAML parser, so add it,
    // if it is not present.
    if (!str_starts_with($search_targets_str, "---")) {
      $search_targets_str = "---\n" . $search_targets_str;
    }

    $decoded_search_targets = [];
    try {
      $decoded_search_targets = Yaml::parse($search_targets_str);
    }
    catch (ParseException $e) {
      $error_message = $form['search_targets']['#title'] . " has missing or invalid YAML.";
      $form_state->setErrorByName('search_targets', $error_message);
      return;
    }

    if (count(array_keys($decoded_search_targets)) == 0) {
      $error_message = $form['search_targets']['#title'] . " has missing or invalid YAML.";
      $form_state->setErrorByName('search_targets', $error_message);
      return;
    }

    // Targets with missing or bad URLs.
    $targets_bad_urls = [];
    $error_message = "The 'url' field is missing or invalid for the following search targets " .
                     "(should have format 'https://DOMAIN/ENDPOINT?SEARCH_QUERY_PARAM='): ";
    foreach ($decoded_search_targets as $name => $val) {
      $url = $val['url'];
      if (filter_var($url, FILTER_VALIDATE_URL) == FALSE) {
        $targets_bad_urls[] = $name;
      }
      elseif (!str_ends_with($url, '=')) {
        $targets_bad_urls[] = $name;
      }
    }
    if (count($targets_bad_urls) > 0) {
      $targets_bad_urls_str = implode("'\n,'", $targets_bad_urls);
      $form_state->setErrorByName('search_targets', $this->t($error_message) . "'$targets_bad_urls_str'");
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

    try {
      $search_targets = Yaml::parse($search_targets_str);
      $config->set('search_targets', $search_targets);
    }
    catch (ParseException $pe) {
      // Shouldn't happen, because invalid YAML should be caught by
      // "validateForm" method.
      $this->logger('hero_search')->error("Error parsing 'Search Targets' YAML: " . $pe);
    }

    foreach (HeroSearchSettingsHelper::LINK_FIELDS as $name => $title) {
      $config->set($name . '_url', $form_state->getValue($name . '_url'));
      $config->set($name . '_text', $form_state->getValue($name . '_text'));
      $config->set($name . '_title', $form_state->getValue($name . '_title'));
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}

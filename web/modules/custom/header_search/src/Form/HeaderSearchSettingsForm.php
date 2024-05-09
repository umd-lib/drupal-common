<?php

namespace Drupal\header_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\header_search\Helper\HeaderSearchSettingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Settings for Header Search target urls.
 */
class HeaderSearchSettingsForm extends ConfigFormBase {

  const SETTINGS = 'header_search.settings';

  /**
   * The logger instance.
   *
   * @var Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * The HeaderSearchSettingsHelper instance.
   *
   * @var Drupal\header_search\Helper\HeaderSearchSettingsHelper
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
    $this->configHelper = HeaderSearchSettingsHelper::getInstance();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.channel.header_search')
    );
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

    $form['header_search_settings'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . $this->t('Configuration for the Header Search block') . '</h3>',
    ];

    $form['default_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder'),
      '#default_value' => $this->configHelper->getDefaultSearchPlaceholder(),
      '#size' => 50,
      '#maxlength' => 60,
    ];

    $form['help_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Help URL'),
      '#default_value' => $this->configHelper->getHelpUrl(),
      '#size' => 50,
      '#maxlength' => 60,
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

    $config->set('default_placeholder', $form_state->getValue('default_placeholder'));
    $config->set('help_url', $form_state->getValue('help_url'));

    $search_targets_str = $form_state->getValue('search_targets');
    try {
      $search_targets = Yaml::parse($search_targets_str);
      $config->set('search_targets', $search_targets);
    }
    catch (ParseException $pe) {
      // Shouldn't happen, because invalid YAML should be caught by
      // "validateForm" method.
      $this->logger('header_search')->error("Error parsing 'Search Targets' YAML: " . $pe);
    }

    $config->save();
    parent::submitForm($form, $form_state);
  }

}

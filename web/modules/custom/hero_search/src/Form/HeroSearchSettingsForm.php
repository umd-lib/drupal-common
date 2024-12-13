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

    $form['search_targets'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search Targets'),
      '#description' => $this->t('YAML formatted Search Targets. See the README.md file.'),
      '#default_value' => Yaml::dump($config->get('search_targets')),
      '#rows' => 7,
      '#cols' => 100,
      '#required' => TRUE,
    ];

    $form['search_more_links'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search More Links'),
      '#description' => $this->t('YAML formatted Search More links. See the README.md file.'),
      '#default_value' => Yaml::dump($config->get('search_more_links')),
      '#rows' => 7,
      '#cols' => 100,
      '#required' => TRUE,
    ];

    $form['top_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Top Content'),
      '#description' => $this->t('Update the top content of the hero block'),
      '#description' => $this->t('This markup will be used for the top of the hero block. Use pure HTML rather than YAML for this field.'),
      '#default_value' => $config->get('top_content'),
      '#rows' => 7,
      '#cols' => 100,
    ];

    $form['bottom_content'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Bottom Content'),
      '#description' => $this->t('Update the bottom content of the hero block'),
      '#default_value' => $config->get('bottom_content'),
      '#description' => $this->t('This markup will be used for the bottom of the hero block. Use pure HTML rather than YAML for this field.'),
      '#rows' => 7,
      '#cols' => 100,
    ];

    $form['hero_search_alert'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Alert'),
      '#default_value' => $config->get('hero_search_alert'),
      '#description' => $this->t('This markup will be used for the Hero Alert. Use pure HTML rather than YAML for this field.'),
      '#rows' => 3,
      '#cols' => 100,
    ];

    $form['quick_actions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Quick Actions'),
      '#default_value' => $config->get('quick_actions'),
      '#description' => $this->t('This markup will be added to the Quick Actions. Use pure HTML rather than YAML for this field.'),
      '#cols' => 100,
      '#rows' => 12,
    ];


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $yaml_fields = ['search_targets', 'search_more_links'];

    foreach ($yaml_fields as $yfield) {
      $yfield_str = trim($form_state->getValue($yfield));
    
      // A starting line with "---" is required by the YAML parser, so add it,
      // if it is not present.
      if (!str_starts_with($yfield_str, "---")) {
        $yfield_str = "---\n" . $yfield_str;
      }
      $decoded_yfield = [];

      try {
        $decoded_yfield = Yaml::parse($yfield_str);
      }
      catch (ParseException $e) {
        $error_message = $form[$yfield]['#title'] . " has missing or invalid YAML.";
        $form_state->setErrorByName($yfield, $error_message);
        return;
      }

      if (count(array_keys($decoded_yfield)) == 0) {
        $error_message = $form[$yfield]['#title'] . " has missing or invalid YAML.";
        $form_state->setErrorByName($yfield, $error_message);
        return;
      }
      // Targets with missing or bad URLs.
      $targets_bad_urls = [];
      $error_message = "The 'url' field is missing or invalid for the following values " .
                       "(should have format 'https://DOMAIN/ENDPOINT?SEARCH_QUERY_PARAM='): ";
      foreach ($decoded_yfield as $name => $val) {
        $url = $val['url'];
        if (filter_var($url, FILTER_VALIDATE_URL) == FALSE) {
          $targets_bad_urls[] = $name;
        }
      }
      if (count($targets_bad_urls) > 0) {
        $targets_bad_urls_str = implode("'\n,'", $targets_bad_urls);
        $form_state->setErrorByName($yfield, $this->t($error_message) . "'$targets_bad_urls_str'");
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::SETTINGS);

    $config->set('title', $form_state->getValue('title'));
    $config->set('top_content', $form_state->getValue('top_content'));
    $config->set('bottom_content', $form_state->getValue('bottom_content'));
    $config->set('hero_search_alert', $form_state->getValue('hero_search_alert'));
    $config->set('quick_actions', $form_state->getValue('quick_actions'));

    $yaml_fields = ['search_targets', 'search_more_links'];

    foreach ($yaml_fields as $yfield) {
      $yfield_str = $form_state->getValue($yfield);
      try {
        $yfield_values = Yaml::parse($yfield_str);
        $config->set($yfield, $yfield_values);
      }
      catch (ParseException $pe) {
        // Shouldn't happen, because invalid YAML should be caught by
        // "validateForm" method.
        $this->logger('hero_search')->error("Error parsing 'Hero Search' YAML: " . $pe);
      }
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

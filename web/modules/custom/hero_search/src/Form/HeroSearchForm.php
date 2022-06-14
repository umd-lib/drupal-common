<?php

namespace Drupal\hero_search\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\hero_search\Helper\HeroSearchSettingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements search box displayed atop a hero image.
 */
class HeroSearchForm extends FormBase {

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
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('logger.channel.hero_search')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'hero_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $search_targets = $this->configHelper->getSearchTargets();
    foreach ($search_targets as $search_target_config) {
      $this->buildSearchQueryTextField($form, $search_target_config);
    }

    $form['search_target'] = [
      '#type' => 'radios',
      '#name' => 'search_target',
      '#default_value' => array_key_first($this->configHelper->getSearchTargetOptions()),
      '#options' => $this->configHelper->getSearchTargetOptions(),
    ];

    $alternate_searches = $this->configHelper->getAlternateSearches();
    foreach ($alternate_searches as $alternate_search) {
      $id = 'alternate_search_' . $alternate_search['search_target'];
      $url = $alternate_search['url'];
      $title = $alternate_search['title'];
      $text = $alternate_search['text'];
      $form['alternate_search'][] = [
        '#type' => 'item',
        '#markup' => "<a href='{$url}' title='{$this->t($title)}'>{$this->t($text)}</a>",
        '#attributes' => [
          'id' => $id,
        ],
        '#states' => [
          'visible' => [
            ':input[name="search_target"]' => ['value' => $alternate_search['search_target']],
          ],
        ],
      ];
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    $form['#theme'] = 'hero_search_form';
    return $form;
  }

  /**
   * Constructs a search query textfield for the given configuration.
   *
   * @param array $form
   *   The form to add the search query textfield to.
   * @param array $search_target_config
   *   The search target configuration.
   */
  protected function buildSearchQueryTextField(array &$form, array $search_target_config) {
    $search_target_name = $search_target_config['search_target'];
    $id = Html::getId('search_query_input_' . $search_target_name);

    $form['search_query'][] = [
      '#type' => 'textfield',
      '#name' => 'search_query',
      '#placeholder' => $search_target_config['placeholder'] ?? $this->configHelper->getDefaultSearchPlaceholder(),
      '#size' => 50,
      '#maxlength' => 128,
      '#attributes' => [
        'id' => $id,
        'aria-label' => t('Search resources in @resource', ['@resource' => $search_target_name]),
      ],
      '#states' => [
        'enabled' => [
          ':input[name="search_target"]' => ['value' => $search_target_name],
        ],
        'visible' => [
          ':input[name="search_target"]' => ['value' => $search_target_name],
        ],
        'required' => [
          ':input[name="search_target"]' => ['value' => $search_target_name],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Can't seem to retreive the query text from the "search_query" textbox via
    // the "getValue" or "getValues" method, so pulling the raw value using the
    // "getUserInput" method.
    $user_input = $form_state->getUserInput();
    $query = $user_input['search_query'];

    $target = $form_state->getValue('search_target');

    $target_base_url = $this->configHelper->getSearchTargetUrl($target);
    $url = '/';
    if ($target_base_url == NULL) {
      $this->logger->notice("The base search Url configuration for '$target' is missing!");
    }
    else {
      $url = Url::fromUri($target_base_url . $query)->toString();
    }
    $response = new TrustedRedirectResponse($url);
    $form_state->setResponse($response);
  }

}

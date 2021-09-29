<?php

namespace Drupal\hero_search\Form;

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
    $form['search_query'] = [
      '#type' => 'textfield',
      '#name' => 'search_query',
      '#placeholder' => $this->configHelper->getSearchPlaceholder(),
      '#size' => 50,
      '#maxlength' => 60,
      '#required' => TRUE,
    ];
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
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = $form_state->getValue('search_query');
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

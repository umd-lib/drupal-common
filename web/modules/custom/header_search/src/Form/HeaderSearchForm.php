<?php

namespace Drupal\header_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\header_search\Helper\HeaderSearchSettingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;

/**
 * Implements search box suitable for display in header.
 */
class HeaderSearchForm extends FormBase {

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
    // Instantiates this form class.
    return new static(
      // Load the service required to construct this class.
      $container->get('logger.channel.header_search')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'header_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['search_query'] = [
      '#type' => 'textfield',
      '#name' => 'search_query',
      '#placeholder' => Html::decodeEntities('&#xF002;') . ' ' . $this->configHelper->getDefaultSearchPlaceholder(),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
      '#attributes' => array('class' => array('header-search-input'), 'aria-label' => t('Search library resources')),
    ];
    $form['search_target'] = [
      '#type' => 'select',
      '#name' => 'search_target',
      '#options' => $this->configHelper->getSearchTargetOptions(),
      '#attributes' => array('class' => array('header-search-options'), 'aria-label' => t('library resources options dropdown')),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#attributes' => array('class' => array('header-search-submit')),
    ];
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

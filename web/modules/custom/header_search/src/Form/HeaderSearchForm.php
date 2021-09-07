<?php
/**
 * @file
 * Definition of HeaderSearchForm
 */

namespace Drupal\header_search\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Drupal\header_search\Helper\HeaderSearchSettingsHelper;

/**
 * Implement HeaderSearchForm
*/
class HeaderSearchForm extends FormBase {

  protected $configHelper;

  public function __construct() {
    $this->configHelper = HeaderSearchSettingsHelper::getInstance();
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
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];
    $form['search_target'] = array(
      '#type' => 'select',
      '#name' => 'search_target',
      '#options' => $this->configHelper->getSearchTargetOptions(),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
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
    if ($target_base_url == null) {
      \Drupal::logger('header_search')->notice("The base search Url configuration for '$target' is missing!");
    } else {
      $url = Url::fromUri($target_base_url . $query)->toString();
    }
    $response = new TrustedRedirectResponse($url);
    $form_state->setResponse($response);
  }
}
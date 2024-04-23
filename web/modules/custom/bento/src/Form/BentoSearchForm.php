<?php
/**
 * @file
 * Contains \Drupal\bento\Form\BentoSearchForm.
 */
namespace Drupal\bento\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;

class BentoSearchForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bento_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $defaults = array()) {
    $form['bento_search_heading'] = [
      '#markup' => '<h2 for="edit-bento-search" class="js-form-required form-required">Search All</h2>',
    ];
    $form['bento_search_input'] = [
      '#prefix' => '<div class="bento-search-input">',
      '#suffix' => '</div>'
    ];
    $form['bento_search_input']['bento_search'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#placeholder' => !empty($defaults['search_placeholder']) ? $defaults['search_placeholder'] : t('Search books, articles, journals, and the website...'),
    );
    $form['bento_search_input']['search_results'] = array(
      '#type' => 'value',
      '#value' => !empty($defaults['default_action']) ? $defaults['default_action'] : null,
    );
    $form['bento_search_input']['actions']['#type'] = 'actions';
    $form['bento_search_input']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $redir_page = null;
    $search_action = $form_state->getValue('search_results');
    if (empty($search_action)) {
      $current_path = \Drupal::service('path.current')->getPath();
      $current_page = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
      $redir_page = $current_page;
    } else {
      $redir_page = $search_action;
    }
    $search_str = $form_state->getValue('bento_search');
    $options = [ 'query' => ['query' => $search_str]];
    $url = Url::fromUri('internal:' . $redir_page, $options);
    $response = new TrustedRedirectResponse($url->toString());
    $form_state->setResponse($response);
  }
}

<?php
/**
 * @file
 * Contains \Drupal\reusable_searchbar\Form\ReusableSearchbarForm.
 */
namespace Drupal\reusable_searchbar\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\Html;

class ReusableSearchbarForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'reusable_searchbar_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $defaults = array()) {
    $form['searchbar_search'] = array(
      '#type' => 'textfield',
      '#title' => t('Search'),
      '#placeholder' => !empty($defaults['search_placeholder']) ? $defaults['search_placeholder'] : t('Search collection holdings...'),
    );
    $form['search_results'] = array(
      '#type' => 'value',
      '#value' => !empty($defaults['default_action']) ? $defaults['default_action'] : null,
    );
    $form['search_param'] = array(
      '#type' => 'value',
      '#value' => !empty($defaults['search_param']) ? $defaults['search_param'] : 'query',
    );
    $form['search_facet'] = array(
      '#type' => 'value',
      '#value' => !empty($defaults['search_facet']) ? $defaults['search_facet'] : null,
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $redir_page = null;
    $search_action = $form_state->getValue('search_results');
    $search_param = $form_state->getValue('search_param');
    $is_absolute = false;
    if (empty($search_action)) {
      $current_path = \Drupal::service('path.current')->getPath();
      $current_page = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
      $redir_page = $current_page;
    } else {
      if (!empty(parse_url($search_action)['host'])) {
        $is_absolute = true;
      }
      $redir_page = $search_action;
    }
    $search_str = $form_state->getValue('searchbar_search');
    $search_facet = $form_state->getValue('search_facet');
    $params = [];
    if (!empty($search_str)) {
      $params[$search_param] = $search_str;
    }
    if (!empty($search_facet)) {
      $params["f[0]"] = "collection:" . $search_facet;
    }
    $options = [];
    $options['query'] = $params;
    if (!$is_absolute) {
      $url = Url::fromUri('internal:' . $redir_page, $options);
    } else {
      $options['absolute'] = true;
      $url = Url::fromUri($redir_page, $options);
    } 
    $response = new TrustedRedirectResponse($url->toString());
    $form_state->setResponse($response);
  }
}

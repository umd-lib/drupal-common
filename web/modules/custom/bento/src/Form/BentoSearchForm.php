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
    $bento_search = \Drupal::request()->query->get('bento_search');
    $default_search = null;
    if (!empty($bento_search)) {
      $default_search = $bento_search;
    }

    $form['bento_search'] = array(
      '#type' => 'textfield',
      '#title' => t('Quick Search'),
      '#required' => TRUE,
      '#placeholder' => t('Stuff'),
    );
    $form['search_results'] = array(
      '#type' => 'value',
      '#value' => !empty($defaults['default_action']) ? $defaults['default_action'] : null,
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
    if (empty($search_action)) {
      $current_path = \Drupal::service('path.current')->getPath();
      $current_page = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
      $redir_page = $current_page;
    } else {
      $redir_page = $search_action;
    }
    $search_str = $form_state->getValue('bento_search');
    $options = [ 'query' => ['bento_search' => $search_str]];
    $url = Url::fromUri('internal:' . $redir_page, $options);
    $response = new TrustedRedirectResponse($url->toString());
    $form_state->setResponse($response);
  }
}

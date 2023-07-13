<?php
 
/**
 * @file
 * Definition of Drupal\mirador_viewer\Plugin\views\field\MiradorViewer
 */
 
namespace Drupal\mirador_viewer\Plugin\views\field;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\mirador_viewer\Controller\DisplayMiradorController;
use Drupal\mirador_viewer\Utility\FedoraUtility;
 
/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("mirador_viewer")
 */
class MiradorViewer extends FieldPluginBase {
 
  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }
 
  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['id_field'] = array('default' => 'id');
 
    return $options;
  }
 
  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['unused'] = array(
      '#title' => $this->t('Temporarily Unused'),
      '#type' => 'textfield',
    );
 
    parent::buildOptionsForm($form, $form_state);
  }
 
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $fc = new FedoraUtility();
    $entity = $values->_item;
    $id = $entity->getId();
    $param = \Drupal::routeMatch()->getParameters();
    $raw_param = \Drupal::routeMatch()->getParameter('arg_0');
    parse_str($raw_param, $url_array);
    $query_str = !empty($url_array['query']) ? trim($url_array['query']) : null;
    $full_uri = \Drupal::request()->getRequestUri();
    $pcdm_prefix = null;
    if (!empty($url_array['relpath'])) {
      $pcdm_prefix = $url_array['relpath'];
    } elseif (!empty($full_uri) && (str_contains($full_uri, 'relpath=') || str_contains($full_uri, 'query'))) {
      $trunc_uri = explode('?', $full_uri);
      parse_str(!empty($trunc_uri[1]) ? $trunc_uri[1] : $full_uri, $uri_array);
      foreach ($uri_array as $key => $value) {
        if (str_contains($key, 'relpath')) {
          $pcdm_prefix = $value;
        }
        if ($key == 'query') {
          $query_str = $value;
        }
      }
      if ($pcdm_prefix == null) {
        $pcdm_prefix = $fc->getCollectionPrefix();
      }
    } else {
      $pcdm_prefix = $fc->getCollectionPrefix();
    }
    $pcdm_prefix = str_replace('/', ':', $pcdm_prefix);
    $c = new DisplayMiradorController();
    $render = $c->viewMiradorObject($id, $pcdm_prefix, $query_str);
    if (!empty($render)) {
      return $render;
    }
  }
}

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
dsm($param);
    $raw_param = \Drupal::routeMatch()->getParameter('arg_0');
dsm($raw_param);
dsm("render");
    parse_str($raw_param, $url_array);
    $full_uri = \Drupal::request()->getRequestUri();
    if (!empty($url_array['relpath'])) {
      $collection_prefix = str_replace('/', ':', $url_array['relpath']);
    } elseif (!empty($full_uri) && str_contains($full_uri, 'relpath=')) {
      $uri_split = explode('relpath=', $full_uri);
      if (!empty($uri_split[1])) {
        $collection_prefix = str_replace('/', ':', $uri_split[1]);
      } else {
        $collection_prefix = $fc->getCollectionPrefix();
      }
    } else {
      $collection_prefix = $fc->getCollectionPrefix();
    }
    $c = new DisplayMiradorController();
    $render = $c->viewMiradorObject($id, $collection_prefix);
    if (!empty($render)) {
      return $render;
    }
  }
}

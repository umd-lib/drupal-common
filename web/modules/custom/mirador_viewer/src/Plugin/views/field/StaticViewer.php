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
 * @ViewsField("static_viewer")
 */
class StaticViewer extends FieldPluginBase {
 
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
    $entity = $values->_item;
    $id = $entity->getId();
    $c = new DisplayMiradorController();
    $id = str_replace('solr_document/', '', $id);
    $members = $c->getPcdmMembers($id);

    if (count($members) > 0) {
      $render = $c->viewStaticObject($members);
      if (!empty($render)) {
        return $render;
      }
    }
  }

  public function getMembers($entity, $field_name) {
    $members = array();
    if (!empty($entity->getField($field_name))) {
      foreach ($entity->getField($field_name)->getValues() as $item) {
        $members[] = $item;
      }
      return $members;
    }
  }
}

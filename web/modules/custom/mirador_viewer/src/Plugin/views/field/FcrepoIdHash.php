<?php
 
/**
 * @file
 * Definition of Drupal\d8views\Plugin\views\field\NodeTypeFlagger
 */
 
namespace Drupal\mirador_viewer\Plugin\views\field;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
 
/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("fcrepo_id_hash")
 */
class FcrepoIdHash extends FieldPluginBase {
 
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
    $entity = $values->_entity;
 dsm($values);
    /*if ($node->bundle() == $this->options['node_type']) {
      return $this->t('Hey, I\'m of the type: @type', array('@type' => $this->options['node_type']));
    }
    else {
      return $this->t('Hey, I\'m something else.');
    }*/
  }
}

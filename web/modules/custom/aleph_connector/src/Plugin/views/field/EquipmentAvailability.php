<?php

/**
 * @file
 * Definition of Drupal\aleph_connector\Plugin\views\field\EquipmentAvailability
 */

namespace Drupal\aleph_connector\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\aleph_connector\Controller\AlephController;

/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("aleph_equipment_available")
 */
class EquipmentAvailability extends FieldPluginBase {

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
    return $options;
  }

  /**
   * Provide the options form.
   *
   * @note
   *   More investigation needed to determine how to actually use these options.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['unused'] = array(
      '#title' => $this->t('Unused'),
      '#description' => $this->t('A description can go here.'),
      '#type' => 'textfield',
    );
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $aleph = new AlephController();
    // $field_tst = $this->view->field[$aleph->getQueryField()]->original_value->__toString();
    if (!$this->isValueEmpty($aleph->getQueryField(), TRUE)) {
      if ($bibnum = $this->view->field[$aleph->getQueryField()]->original_value) {
        return $aleph->getBibnumData($bibnum->__toString());
      }
    }
    return FALSE; 
  }
}

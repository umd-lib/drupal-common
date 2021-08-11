<?php

/**
 * @file
 * Definition of Drupal\equipment_tracking\Plugin\views\field\EquipmentAvailability
 */

namespace Drupal\equipment_tracking\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\aleph_connector\Controller\AlephController;
use Drupal\Component\Datetime\DateTimePlus;

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
    if (!$this->isValueEmpty($aleph->getQueryField(), TRUE)) {
      if ($bibnum = $this->view->field[$aleph->getQueryField()]->original_value->__toString() && $bibnum_data = $aleph->getBibnumData($bibnum)) {
        return $this->availabilityField($bibnum, $bibnum_data);
      }
    }
    return FALSE; 
  }

  /**
   * Generate sample page content for umd-examples page.
   */
  public function availabilityField($bibnum, $availability_data) {
    $processed_date = null;
    if ($raw_date = $availability_data['mindue']) {
      try {
        // 2021-07-30-16:30
        $datetime_p = DateTimePlus::createFromFormat('YmdHi', $raw_date);
        $processed_date = $datetime_p->format('g:i A \o\n F j, Y');
      }
      catch (\InvalidArgumentException $e) {
        \Drupal::messenger()->addError('fail1: ' . $e->getMessage());
      }
      catch (\UnexpectedValueException $e) {
        \Drupal::messenger()->addError('fail2: ' . $e->getMessage());
      }
    }
    return [
      '#theme' => 'aleph_equipment_available',
      '#equipment_count' => $availability_data['available'],
      '#equipment_mindue' => $processed_date,
      '#equipment_sysnum' => $bibnum,
    ];
  }
}

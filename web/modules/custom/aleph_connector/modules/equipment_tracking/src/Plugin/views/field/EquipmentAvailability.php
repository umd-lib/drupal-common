<?php

/**
 * @file
 * Definition of Drupal\equipment_tracking\Plugin\views\field\EquipmentAvailability
 */

namespace Drupal\equipment_tracking\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
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
    $bibnums = [];
    $aleph = new AlephController();

    if (!$this->isValueEmpty($aleph->getQueryField(), TRUE)) {
      $field = $this->view->field[$aleph->getQueryField()];
      if ($field instanceof \Drupal\search_api\Plugin\views\field\SearchApiEntityField) {
        $items = $field->getItems($values);
        foreach ($items as $item) {
          $bibnum = $item['value'];
          $bibnums[] = $bibnum;
        }
      }
    }

    if (count($bibnums) > 0) {
      if ($bibnum_data = $aleph->getBibnumData($bibnums)) {
        return $this->availabilityField($bibnums, $bibnum_data);
      }
    }
    return FALSE;
  }

  /**
   * Generate sample page content for umd-examples page.
   */
  public function availabilityField($bibnums, $availability_data) {
    $available_count = 0;

    $earliest_raw_date = null;
    $processed_date = NULL;

    foreach ($availability_data as $datum) {
      $available_count += $datum['available'];

      if ($raw_date = $datum['mindue']) {
        if (!$earliest_raw_date || ($raw_date < $earliest_raw_date)) {
          $earliest_raw_date = $raw_date;
          try {
            // 2021-07-30-16:30
            $datetime_p = DateTimePlus::createFromFormat('YmdHi', $raw_date);
            $processed_date = $datetime_p->format('g:i A \o\n F j, Y');
          }
          catch (\InvalidArgumentException $e) {
            // TODO: Replace this with logger -- \Drupal::messenger()->addError('Error 1: ' . $e->getMessage());
            dpm($e);
          }
          catch (\UnexpectedValueException $e) {
            // TODO: Replace this with logger -- \Drupal::messenger()->addError('Error 2: ' . $e->getMessage());
            dpm($e);
          }
        }
      }
    }

    return [
      '#theme' => 'aleph_equipment_available',
      '#equipment_count' => $available_count,
      '#equipment_mindue' => $processed_date,
      '#equipment_sysnum' => implode(',', $bibnums),
    ];
  }
}

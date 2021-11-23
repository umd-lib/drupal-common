<?php

/**
 * @file
 * Definition of Drupal\top_textbooks\Plugin\views\field\TextbookAvailabilityField
 */

namespace Drupal\top_textbooks\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\top_textbooks\Controller\TextbookAvailabilityController;

/**
 * Field handler to display the textbook availability
 * 
 * @ingroup views_field_handler
 * 
 * @ViewsField("textbook_availability_field")
 */
class TextbookAvailabilityField extends FieldPluginBase {

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  protected function defineOptions() {
    return parent::defineOptions();
  }

  /**
   * Build the options form
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_data) {
    parent::buildOptionsForm($form, $form_data);
  }

  private function renderableAvailabilityData($barcode, $data) {
    $count = array_key_exists('num_available', $data) ? $data['num_available'] : 0;
    $due_date = array_key_exists('next_available_date', $data)? $data['next_available_date'] : null;
    $formatted_date = null;
    if ($due_date != null) {
      $formatted_date = $due_date->format('g:i A \o\n F j, Y');
    }
    return [
      '#theme' => 'textbook_availability_field',
      '#textbook_count' => $count,
      '#textbook_mindue' => $formatted_date,
      '#textbook_sysnum' => $barcode,
    ];
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $tvc = new TextbookAvailabilityController();
    $barcodes = $this->getFieldValues($values, 'bar_code');
    // \Drupal::logger('top_textbooks')->notice('Checking availability for barcodes: ' . var_export($barcodes, true));
    if (count($barcodes) > 0) {
      if ($availability_data = $tvc->getAvailability($barcodes)) {
        return $this->renderableAvailabilityData($barcodes[0], $availability_data);
      } else {
        $title = $this->getFieldValueString($values, 'title');
        \Drupal::logger('top_textbooks')->notice("Availability info not found for '$title' with barcode(s): " . implode(", ", $barcodes));
      }
    } else {
      $title = $this->getFieldValueString($values, 'title');
      \Drupal::logger('top_textbooks')->notice("No barcodes available in Solr index for '$title'");
    }
    return [
      '#theme' => 'textbook_availability_field',
      '#error' => true,
    ];
  }

  private function getFieldValueString($values, $field_name) {
    $field_values = $this->getFieldValues($values, $field_name);
    return implode(", ", $field_values);
  }

  private function getFieldValues($values, $field_name) {
    $field_values = [];
    $objects = $this->view->field[$field_name]->getValue($values);
    foreach($objects as $object) {
      array_push($field_values, is_string($object) ? $object : $object->getOriginalText());
    }
    return $field_values;
  }

}
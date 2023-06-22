<?php

/**
 * @file
 * Contains Drupal\facet_overrides\Form\SettingsForm.
 */

namespace Drupal\facet_overrides\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class FacetOverridesSettingsForm extends ConfigFormBase {

  const SETTINGS = 'facet_overrides.settings';

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'facet_overrides_settings';
  }

  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config(static::SETTINGS);

    $facet_overrides_map = $config->get('facet_overrides');
    $search_overrides_map = $config->get('search_overrides');

    $form['facet_overrides'] = [  
      '#type' => 'textarea',  
      '#title' => $this->t('Facet Overrides'),  
      '#default_value' => $this->allowedValuesString($facet_overrides_map),
      '#description' => $this->t('This is the raw Solr query. It should be patterned as "/page|facet". For example, "/kaporter-correspondence|collection_title_facet:"Katherine Anne Porter".'),
    ];
    $form['search_overrides'] = [  
      '#type' => 'textarea',  
      '#title' => $this->t('Search Overrides'),  
      '#default_value' => $this->allowedValuesString($search_overrides_map),
      '#description' => $this->t('This is the Search API URL query parameter. It should be patterned as "/page|param". For example, "/kaporter-correspondence|collection=Katherine Anne Porter.'),
    ];
    return parent::buildForm($form, $form_state);  
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    $facet_overrides_map = $this->extractAllowedValues($form_state->getValue('facet_overrides'));
    $this->configFactory->getEditable(static::SETTINGS)->set('facet_overrides', $facet_overrides_map)->save();
    $search_overrides_map = $this->extractAllowedValues($form_state->getValue('search_overrides'));
    $this->configFactory->getEditable(static::SETTINGS)->set('search_overrides', $search_overrides_map)->save();
    parent::submitForm($form, $form_state);
  } 

  /**
   * Generates a string representation of an array of 'allowed values'.
   *
   * This string format is suitable for edition in a textarea.
   *
   * @param array $values
   *   An array of values, where array keys are values and array values are
   *   labels.
   *
   * @return string
   *   The string representation of the $values array:
   *    - Values are separated by a carriage return.
   *    - Each value is in the format "value|label" or "value".
   */
  protected function allowedValuesString($values) {
    $lines = [];
    foreach ($values as $key => $value) {
      $lines[] = "$key|$value";
    }
    return implode("\n", $lines);
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = strtolower(trim($matches[1]));
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    return $values;
  }
}

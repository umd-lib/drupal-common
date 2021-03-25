<?php
namespace Drupal\umd_examples\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * An example settings form.
 * It demonstrates a very small subset of available fields.
 *
 * For official documentation:
 * @see https://www.drupal.org/docs/drupal-apis/configuration-api/working-with-configuration-forms
 *
 * For supported form elements:
 * @see https://api.drupal.org/api/drupal/elements/8.9.x
 */
class ExampleSettingsForm extends ConfigFormBase {

  const SETTINGS = 'umd_examples.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'umd-examples-sample-form';
  }

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
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Load the stored values to populate forms.
    $config = $this->config(static::SETTINGS);

    // @see samlauth_attrib module for an example of field to array (and reverse)

    $form['example_item'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('This is an example item. It does nothing but display this text.') . '</h3>',
    ];

    $form['example_hidden'] = [
      '#type' => 'hidden',
      '#value' => 'bogus',
    ];

    $form['example_text'] = [
      '#type' => 'textfield',
      '#title' => t('Text Field'),
      '#description' => t('An example text field.'),
      '#default_value' => $config->get('example_text'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => FALSE,
    ];

    $form['example_description'] = [
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#default_value' => $config->get('example_description'),
    ];

    $form['example_date'] = [
      '#type' => 'date',
      '#title' => t('Example Date'),
      '#default_value' => $config->get('example_date'),
    ];

    $form['example_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => t('Example Fieldset'),
      '#description' => t('Use this to group fields'),
    ];

    $form['example_fieldset']['example_range'] = [
      '#type' => 'range',
      '#title' => t('Example Range'),
      '#min' => 10,
      '#max' => 50,
      '#description' => t('Use this to create a range.'),
      '#default_value' => $config->get('example_range'),
    ];

    $entity_types = ['node' => t('Node'), 'user' => t('User'), 'term' => t('Term')];
    $form['example_fieldset']['example_radios'] = [
      '#type' => 'radios',
      '#title' => t('Example Radios'),
      '#default_value' => $config->get('example_radios'),
      '#description' => t('Example radios'),
      '#options' => $entity_types,
    ];

    $form['example_tabs'] = [
      '#type' => 'vertical_tabs',
      '#default_tab' => 'edit-color',
    ];

    $form['example_ui'] = [
      '#type' => 'details',
      '#title' => t('UI'),
      '#group' => 'example_tabs',
    ];

    $form['example_data'] = [
      '#type' => 'details',
      '#title' => t('Data'),
      '#group' => 'example_tabs',
    ];

    $form['example_ui']['example_color'] = [
      '#type' => 'color',
      '#title' => t('Example Color'),
      '#default_value' => $config->get('example_color'),
      '#description' => t('This field is especially useful in theme forms'),
    ];

    $options = [1 => t('one'), 2 => t('two'), 3 => t('three'), 4 => t('four'), 'other' => t('other')];
    $form['example_data']['example_checkboxes'] = [
      '#type' => 'checkboxes',
      '#title' => t('Example Checkboxes'),
      '#default_value' => $config->get('example_checkboxes'),
      '#description' => t('This will produce an array'),
      '#options' => $options,
    ];

    $form['example_hidden'] = [
      '#type' => 'hidden',
      '#value' => 'Hidden using ' . $config->get('example_radios'),
    ];

    // Note that settings forms provide a submit button for free but using
    // FormAPI for non-settings forms will require a submit element.

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // This method is used for additional validation beyond what Drupal already provides.
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configFactory->getEditable(static::SETTINGS)
      ->set('example_text', $form_state->getValue('example_text'))
      ->set('example_date', $form_state->getValue('example_date'))
      ->set('example_range', $form_state->getValue('example_range'))
      ->set('example_radios', $form_state->getValue('example_radios'))
      ->set('example_color', $form_state->getValue('example_color'))
      ->set('example_description', $form_state->getValue('example_description'))
      ->set('example_checkboxes', $form_state->getValue('example_checkboxes'))
      ->set('example_hidden', $form_state->getValue('example_hidden'))
      ->save();

    // Display the hidden value
    \Drupal::messenger()->addStatus(t('Hidden Value: ') . $form_state->getValue('example_hidden'));

    dsm($form_state->getValue('example_checkboxes'));

    parent::submitForm($form, $form_state);
  }
}

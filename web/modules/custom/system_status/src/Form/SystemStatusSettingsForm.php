<?php
namespace Drupal\system_status\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings for System Status target urls.
 */
class SystemStatusSettingsForm extends ConfigFormBase {

  const SETTINGS = 'system_status.settings';



  const SERVICE_URL_FIELD = 'service_url';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'system-status-settings-form';
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

    $form['system_status_settings'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('Configure the upstream system status JSON service url.') . '</h3>',
    ];

    $form[static::SERVICE_URL_FIELD] = [
      '#type' => 'url',
      '#title' => t('Service URL'),
      '#default_value' => $config->get(static::SERVICE_URL_FIELD),
      '#size' => 50,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue(static::SERVICE_URL_FIELD);
    $this->configFactory->getEditable(static::SETTINGS)->set(static::SERVICE_URL_FIELD, $value)->save();
    parent::submitForm($form, $form_state);
  }
} 

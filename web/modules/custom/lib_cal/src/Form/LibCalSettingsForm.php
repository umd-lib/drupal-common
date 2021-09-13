<?php
namespace Drupal\lib_cal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lib_cal\Helper\LibCalSettingsHelper;
use Drupal\lib_cal\Helper\LibCalApiHelper;

/**
 * Settings for LibCal target urls.
 */
class LibCalSettingsForm extends ConfigFormBase {


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lib-cal-settings-form';
  }

  /** 
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      LibCalSettingsHelper::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Load the stored values to populate forms.
    $config = $this->config(LibCalSettingsHelper::SETTINGS);

    // @see samlauth_attrib module for an example of field to array (and reverse)

    $form['lib_cal_settings'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('Configuration for LibCal integration.') . '</h3>',
    ];

    $form[LibCalSettingsHelper::ENDPOINT] = [
      '#type' => 'url',
      '#title' => t('Endpoint'),
      '#default_value' => $config->get(LibCalSettingsHelper::ENDPOINT),
      '#size' => 50,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form[LibCalSettingsHelper::CLIENT_ID] = [
      '#type' => 'textfield',
      '#title' => t('Client ID'),
      '#default_value' => $config->get(LibCalSettingsHelper::CLIENT_ID),
      '#size' => 50,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form[LibCalSettingsHelper::CLIENT_SECRET] = [
      '#type' => 'textfield',
      '#title' => t('Client Secret'),
      '#default_value' => $config->get(LibCalSettingsHelper::CLIENT_SECRET),
      '#size' => 50,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form[LibCalSettingsHelper::CALENDAR_ID] = [
      '#type' => 'textfield',
      '#title' => t('Calendar ID'),
      '#default_value' => $config->get(LibCalSettingsHelper::CALENDAR_ID),
      '#size' => 50,
      '#maxlength' => 50,
      '#required' => TRUE,
    ];

    $form['lib_cal_auth_test'] = [
      '#type' => 'textarea',
      '#title' => t('LibCal API Auth Test'),
      '#default_value' => $config->get('lib_cal_auth_test'),
      '#disabled' => TRUE,
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
    $endpoint = rtrim($form_state->getValue(LibCalSettingsHelper::ENDPOINT), '/') . '/';  
    $client_id = $form_state->getValue(LibCalSettingsHelper::CLIENT_ID);
    $client_secret = $form_state->getValue(LibCalSettingsHelper::CLIENT_SECRET);
    $calendar_id = $form_state->getValue(LibCalSettingsHelper::CALENDAR_ID);

    $settings = $this->configFactory->getEditable(LibCalSettingsHelper::SETTINGS);

    $settings->set(LibCalSettingsHelper::ENDPOINT, $endpoint)
      ->set(LibCalSettingsHelper::CLIENT_ID, $client_id)
      ->set(LibCalSettingsHelper::CLIENT_SECRET, $client_secret)
      ->set(LibCalSettingsHelper::CALENDAR_ID, $calendar_id)
      ->save();

    $apiHelper = LibCalApiHelper::getInstance($endpoint, $client_id, $client_secret);
    $events = $apiHelper->getEvents($calendar_id);
    $settings->set('lib_cal_auth_test', var_export($events, true))
      ->save();
    parent::submitForm($form, $form_state);
  }
} 

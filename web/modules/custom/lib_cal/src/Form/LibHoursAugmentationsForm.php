<?php
namespace Drupal\lib_cal\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lib_cal\Helper\LibCalSettingsHelper;
use Drupal\lib_cal\Helper\LibCalApiHelper;

/**
 * UI augmentations for Lib Cal Hours.
 */
class LibHoursAugmentationsForm extends ConfigFormBase {

  private $configHelper;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lib-cal-hours-augs-form';
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

    $config = $this->config(LibCalSettingsHelper::SETTINGS);
    $libraries_raw = $config->get(LibCalSettingsHelper::LIBRARIES);
    $this->configHelper = LibCalSettingsHelper::getInstance();
    $libraries = $this->configHelper->getLibrariesOptions();

    foreach ($libraries as $key => $library) {
      $form_key = "libaug-" . $key;
      $form[$form_key] = [
        '#type' => 'textfield',
        '#title' => $library,
        '#description' => t('Output augmentations for @lib', ['@lib' => $library]),
        '#default_value' => $config->get($form_key),
      ];
    }
    
    $form[LibCalSettingsHelper::SHADY_GROVE] = [
      '#type' => 'textfield',
      '#title' => t('Shady Grove Hours Information'),
      '#default_value' => $config->get(LibCalSettingsHelper::SHADY_GROVE),
      '#description' => t('Add example markup'),
    ];

    $form[LibCalSettingsHelper::ALL_LIBRARIES] = [
      '#type' => 'textfield',
      '#title' => t('All Libraries Hours Information'),
      '#default_value' => $config->get(LibCalSettingsHelper::ALL_LIBRARIES),
      '#description' => t('Add example markup'),
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
    $settings = $this->configFactory->getEditable(LibCalSettingsHelper::SETTINGS);
    $libraries = $this->configHelper->getLibrariesOptions();

    foreach ($libraries as $key => $library) {
      $form_key = "libaug-" . $key;
      $lib_data = $form_state->getValue($form_key);
      $settings->set($form_key, $lib_data)->save();
    }
    $all_libraries = $form_state->getValue(LibCalSettingsHelper::ALL_LIBRARIES);
    $shady = $form_state->getValue(LibCalSettingsHelper::SHADY_GROVE);

    $settings->set(LibCalSettingsHelper::ALL_LIBRARIES, $all_libraries)->save();
    $settings->set(LibCalSettingsHelper::SHADY_GROVE, $shady)->save();

    parent::submitForm($form, $form_state);
  }
} 

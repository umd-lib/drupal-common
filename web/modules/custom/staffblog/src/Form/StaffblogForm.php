<?php
/**
 * @file
 * Contains Drupal\umd_su\Form\StaffblogForm.
 */
namespace Drupal\staffblog\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class StaffblogForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'staffblog.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'staffblog_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('staffblog.adminsettings');

    $form['welcome_message'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Welcome message'),
      '#description' => $this->t('Dean\'s Welcome Message for Staff Blog'),
      '#default_value' => $config->get('welcome_message'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);

    $this->config('staffblog.adminsettings')
      ->set('welcome_message', $form_state->getValue('welcome_message'))
      ->save();
  }

} 

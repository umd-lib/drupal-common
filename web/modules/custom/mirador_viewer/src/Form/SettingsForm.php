<?php

/**
 * @file
 * Contains Drupal\mirador_viewer\Form\SettingsForm.
 */

namespace Drupal\mirador_viewer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'mirador_viewer.adminsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'mirador_settings';
  }

  /**  
   * {@inheritdoc}  
   */  
  public function buildForm(array $form, FormStateInterface $form_state) {  
    $config = $this->config('mirador_viewer.adminsettings');  

    $form['iiif_server'] = [  
      '#type' => 'textfield',  
      '#title' => $this->t('IIIF Server'),  
      '#default_value' => $config->get('iiif_server'),
      '#description' => $this->t('E.g., https://iiifdev.lib.umd.edu/ with trailing slash.'),
    ];
    $form['iiif_viewer'] = [
      '#type' => 'textfield',  
      '#title' => $this->t('IIIF Viewer Path'),  
      '#default_value' => $config->get('iiif_viewer'),
      '#description' => $this->t('E.g., viewer/1.2.0/mirador.html'),
    ];
    $form['fcrepo_server'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fcrepo Server'),
      '#default_value' => $config->get('fcrepo_server'),
      '#description' => $this->t('E.g., https://fcrepodev.lib.umd.edu/fcrepo/rest/ with trailing slash.'),
    ];
    $form['error_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Error Message'),
      '#default_value' => $config->get('error_message'),
    ];  

    return parent::buildForm($form, $form_state);  
  }

  /**  
   * {@inheritdoc}  
   */  
  public function submitForm(array &$form, FormStateInterface $form_state) {  
    parent::submitForm($form, $form_state);  

    $this->config('mirador_viewer.adminsettings')  
      ->set('iiif_server', $form_state->getValue('iiif_server'))  
      ->save();

    $this->config('mirador_viewer.adminsettings')  
      ->set('iiif_viewer', $form_state->getValue('iiif_viewer'))  
      ->save();

    $this->config('mirador_viewer.adminsettings')  
      ->set('fcrepo_server', $form_state->getValue('fcrepo_server'))  
      ->save();

    $this->config('mirador_viewer.adminsettings')
      ->set('error_message', $form_state->getValue('error_message'))
      ->save();
  } 

}

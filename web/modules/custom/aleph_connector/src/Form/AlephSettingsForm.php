<?php
namespace Drupal\aleph_connector\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings for Aleph connection services.
 */
class AlephSettingsForm extends ConfigFormBase {

  const SETTINGS = 'aleph_connector.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aleph-connector-settings-form';
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

    $form['aleph_info'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('This form handles all Aleph services connection configuration.') . '</h3>',
    ];

    $form['aleph_base'] = [
      '#type' => 'textfield',
      '#title' => t('Aleph Base URL'),
      '#default_value' => $config->get('aleph_base'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['equipment_endpoint'] = [
      '#type' => 'textfield',
      '#title' => t('Equipment Endpoint'),
      '#default_value' => $config->get('equipment_endpoint'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['equipment_query_field'] = [
      '#type' => 'textfield',
      '#title' => t('Equipment Query Field'),
      '#description' => t('This field will be used to query the Aleph api for current equipment data'),
      '#default_value' => $config->get('equipment_query_field'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['equipment_test'] = [
      '#type' => 'textarea',
      '#title' => t('Equipment Test'),
      '#default_value' => $config->get('equipment_test'),
      '#disabled' => TRUE,
    ];

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
    // Some URL cleanup
    $aleph_base = rtrim($form_state->getValue('aleph_base'), '/') . '/';
    $equipment_endpoint = ltrim($form_state->getValue('equipment_endpoint'), '/');
    $query_field = $form_state->getValue('equipment_query_field');

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $aleph_base . $equipment_endpoint);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);

    if (!curl_errno($curl)) {
      switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
        case 200:
          \Drupal::messenger()->addStatus(t('Connection successful!'));
          // $json = json_encode($xml[0]->body->table->tr[2]->td[0]->attributes()['class']);
          break;
        default:
          \Drupal::messenger()->addError(t('Connection returned non-200 code:' . $http_code));
      }
    } else {
      \Drupal::messenger()->addError(t('Connection error.'));
    }

    $xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
    $rows = $xml->xpath('//tr');
    $aleph_data = [];
    foreach ($rows as $row) {
      $vals = $row->xpath('//td[@class]');
      $row_data = [];
      $tmp_sysnum = null;
      foreach ($row->td as $td) {
        // \Drupal::messenger()->addStatus((string)$td);
        // \Drupal::messenger()->addStatus(json_encode($td->attributes()['class']));
        // \Drupal::messenger()->addStatus((string)$td->attributes()['class']);
        if ((string)$td->attributes()['class'] == 'sysnum') {
          $tmp_sysnum = (string)$td;
        }
        if ((string)$td->attributes()['class'] == 'available') {
          $row_data['available'] = (int)$td;
        }
        if ((string)$td->attributes()['class'] == 'mindue') {
          $row_data['mindue'] = (string)$td;
        }
      }
      if ($tmp_sysnum != null) {
        $aleph_data[$tmp_sysnum] = $row_data;
      }
    }
    \Drupal::messenger()->addStatus(json_encode($aleph_data));

    

    curl_close($curl);

    $this->configFactory->getEditable(static::SETTINGS)
      ->set('equipment_endpoint', $equipment_endpoint)
      ->set('aleph_base', $aleph_base)
      ->set('equipment_test', $output)
      ->set('equipment_query_field', $query_field)
      ->save();
    parent::submitForm($form, $form_state);
  }
}

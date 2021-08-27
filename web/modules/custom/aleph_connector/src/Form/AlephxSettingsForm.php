<?php
namespace Drupal\aleph_connector\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings for Aleph connection services.
 */
class AlephxSettingsForm extends ConfigFormBase {

  const SETTINGS = 'aleph_connector.xsettings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'aleph-connector-xsettings-form';
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

    $form['aleph_x_info'] = [
      '#type' => 'item',
      '#markup' => '<h3>' . t('The following section has configuration of Aleph X Services') . '</h3>',
    ];

    $form['base'] = [
      '#type' => 'textfield',
      '#title' => t('Aleph X Base URL'),
      '#default_value' => $config->get('base'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['adm_library'] = [
      '#type' => 'textfield',
      '#title' => t('Adm Library'),
      '#default_value' => $config->get('adm_library'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['bib_library'] = [
      '#type' => 'textfield',
      '#title' => t('Bib Library'),
      '#default_value' => $config->get('bib_library'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['sub_library'] = [
      '#type' => 'textfield',
      '#title' => t('Sub Library'),
      '#default_value' => $config->get('sub_library'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['collection'] = [
      '#type' => 'textfield',
      '#title' => t('Collection'),
      '#default_value' => $config->get('collection'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['test_barcode'] = [
      '#type' => 'textfield',
      '#title' => t('Texbook Barcode (for testing connection)'),
      '#default_value' => $config->get('test_barcode'),
      '#size' => 25,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];

    $form['textbook_test'] = [
      '#type' => 'textarea',
      '#title' => t('Aleph X Read Item Test'),
      '#default_value' => $config->get('textbook_test'),
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
    $base = rtrim($form_state->getValue('base'), '/') . '/';
    $adm_library = $form_state->getValue('adm_library');
    $bib_library = $form_state->getValue('bib_library');
    $sub_library = $form_state->getValue('sub_library');
    $collection = $form_state->getValue('collection');
    $test_barcode = $form_state->getValue('test_barcode');

    $curl = curl_init();
    $url_options = '?op=read-item&library=' . $adm_library . '&item_barcode=' . $test_barcode . '&translate=N';
    curl_setopt($curl, CURLOPT_URL, $base . $url_options);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $textbook_output = curl_exec($curl);

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

    $xml = simplexml_load_string($textbook_output, "SimpleXMLElement", LIBXML_NOCDATA);
    $z30_match = $xml->xpath('(//read-item/z30)');
    if ($z30_match != null) {
      $z30 = $z30_match[0];
    }
    $sys_num = '';
    if ($z30 != null) {
      $sys_num = $z30->{'z30-doc-number'};
    }

    \Drupal::messenger()->addStatus('Textbook Test z30: ' . json_encode($z30));
    \Drupal::messenger()->addStatus('Textbook Test Sys Num: ' . $sys_num);

    curl_close($curl);

    $this->configFactory->getEditable(static::SETTINGS)
      ->set('base', $base)
      ->set('adm_library', $adm_library)
      ->set('bib_library', $bib_library)
      ->set('sub_library', $sub_library)
      ->set('collection', $collection)
      ->set('test_barcode', $test_barcode)
      ->set('textbook_test', $textbook_output)
      ->save();
    parent::submitForm($form, $form_state);
  }
}

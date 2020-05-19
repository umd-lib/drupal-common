<?php  
namespace Drupal\message_digest_admin\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class MessageDigestAdminHistory extends ConfigFormBase {  

    protected function getEditableConfigNames() {  
        return [  
          'message_digest_admin.adminsettings',  
        ];  
      } 
    public function getFormId() {  
        return 'tab_form';  
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('message_digest_admin.adminsettings');

    $form['history'] = [
      '#type' => 'table',
      '#caption' => t('History'),
      '#header' => [t('Date'), t('Title'), t('Email'), t('Type')],
    ];

    $history = message_digest_admin_history();
    $i = 0;
    while ($result = $history->fetchObject()) {
      $timestamp = $result->timestamp;
      $date = \Drupal::service('date.formatter')->format($timestamp);
      $email = $result->email;
      $title = $result->title;
      $type = $result->type;
      $form['history'][$i]['date'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $date,
      ];
      $form['history'][$i]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $title,
      ];
      $form['history'][$i]['email'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $email,
      ];
      $form['history'][$i]['type'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $type,
      ];
      $i++;
    }
    if ($i == 0) {
      $form['current'][0]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => t('No history'),
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('message_digest_admin.adminsettings')
      ->set('welcome_message', $form_state->getValue('welcome_message'))
      ->save();
  }  

}  

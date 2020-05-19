<?php  
namespace Drupal\message_digest_admin\Form;  
use Drupal\Core\Form\ConfigFormBase;  
use Drupal\Core\Form\FormStateInterface;  

class MessageDigestAdminStaged extends ConfigFormBase {  

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

    $form['current'] = [
      '#type' => 'table',
      '#caption' => t('Staged Content'),
      '#header' => [t('Title'), t('Edit')],
    ];

    $staged = message_digest_admin_qmessage_digest('UNSENT');
    $i = 0;
    while ($result = $staged->fetchObject()) {
      $nid = $result->field_node_reference_target_id;
      $link = message_digest_admin_genpath($nid, $result->title);
      $edit = message_digest_admin_editpath($nid);
      $form['current'][$i]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $link,
      ];
      $form['current'][$i]['edit'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $edit,
      ];
      $i++;
    }
    if ($i == 0) {
      $form['current'][0]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => t('No staged content'),
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

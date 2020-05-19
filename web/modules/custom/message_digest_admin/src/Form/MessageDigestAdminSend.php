<?php  
namespace Drupal\message_digest_admin\Form;  

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MessageDigestAdminSend extends FormBase {  

  /**
   * {@inheritdoc}
   */
  public function getFormId() {  
    return 'send-tab-form';  
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['info'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => t('This form circumvents the automated digest sending process to send completed digests to library staff. It should probably only be used if cron fails to fire for some reason. Any messages included in this digest will be set to a locked sent state and can only be reset directly in the database to as to prevent inadvertent resending.')
    ];

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
    } else {
      $form['verify'] = [
        '#type' => 'checkbox',
        '#title' => t('Check to confirm sending completed digest')
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Send'),
        '#button_type' => 'primary',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('verify')) {
      $form_state->setErrorByName('verify', t('Must verify to proceed with send'));
    }
    if (!$uid = message_digest_admin_digestuser()) {
      $form_state->setErrorByName('submit', t('Digest User not set in settings.'));
    }
    $form_state->setValue('uid', $uid);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid = $form_state->getValue('uid');
    $user_count = message_digest_admin_setdigestuser($uid);
    $notifier_count = message_digest_admin_setnotifier();

    $raw = message_digest_admin_rawunsent($uid);

    message_digest_admin_processdigest($uid);
    $post_count = message_digest_admin_setnotifier('message_digest:never', '1');
    
    \Drupal::messenger()->addStatus(t('Completed digest sent to user %uid.', ['%uid' => $uid]));

    $title = message_notify_staff_gettitle();

    $history = message_digest_admin_recordhistory($title, 'send', $uid, serialize($raw));

  }  
}  

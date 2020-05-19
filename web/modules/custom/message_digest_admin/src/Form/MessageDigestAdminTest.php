<?php  
namespace Drupal\message_digest_admin\Form;  

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MessageDigestAdminTest extends FormBase {  

  /**
   * {@inheritdoc}
   */
  public function getFormId() {  
    return 'test-tab-form';  
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    if (message_digest_admin_testpurge()) {
      \Drupal::messenger()->addStatus(t('Digest table is in mixed state! (i.e., shows both sent and unsent messages). Purge old messages before sending test message or risk sending digest with old and new content.'));
    }

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
      $form['email'] = [
        '#type' => 'email',
        '#title' => t('Test Email Address'),
        '#description' => t('Warning: This email address must have a corresponding Drupal user.'),
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
    $email = $form_state->getValue('email');
    $uid = message_digest_admin_getuid($email);

    if (!$uid) {
      $form_state->setErrorByName('email', t('User with email %email does not exist', ['%email' => $email]));
    } else {
      $form_state->setValue('uid', $uid);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $uid = $form_state->getValue('uid');
    $user_count = message_digest_admin_setdigestuser($uid);
    $notifier_count = message_digest_admin_setnotifier();
    $reset_count = message_digest_admin_resetsent($uid);

    $raw = message_digest_admin_rawunsent($uid);

    \Drupal::messenger()->addStatus(t('UID configured to send to %uid. User: %user_count. Notifier: %notifier_count. Reset: %reset_count.',
      ['%uid' => $uid, '%user_count' => $user_count, '%notifier_count' => $notifier_count, '%reset_count' => $reset_count]));

    message_digest_admin_processdigest($uid);
    $reset_count = message_digest_admin_resetsent($uid);

    $title = message_notify_staff_gettitle();

    $history = message_digest_admin_recordhistory($title, 'test', $uid, serialize($raw));
  }  
}  

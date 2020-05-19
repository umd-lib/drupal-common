<?php  
namespace Drupal\message_digest_admin\Form;
  
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class MessageDigestAdminPurge extends FormBase {  

    /**
     * {@inheritdoc}.
     */
    public function getFormId() {  
      return 'purge-tab-form';  
    }

    /**
     * {@inheritdoc}.
     */
    public function buildForm(array $form, FormStateInterface $form_state) {

    $form['info'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => t('Clear digest table of messages which have already been sent to the staff. This means messages with <strong>sent=1</strong> and <strong>notifier=message_digest:never</strong>.'),
    ];
    $form['sent'] = [
      '#type' => 'table',
      '#caption' => t('Sent Content'),
      '#header' => [t('Title'), t('Status')],
    ];

    $sent = message_digest_admin_qmessage_digest('SENT');
    $status_table = ['message_digest:never' => t('Ready for purge'), 'message_digest:weekly' => t('Needs evaluation'), 'message_digest:ten_minutes' => t('Needs evaluation')];
    $i = 0;
    while ($result = $sent->fetchObject()) {
      $nid = $result->field_node_reference_target_id;
      $notifier = $result->notifier;
      $link = message_digest_admin_genpath($nid, $result->title);
      $form['sent'][$i]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $link,
      ];
      $form['sent'][$i]['status'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $status_table[$notifier],
      ];
      $i++;
    }
    if ($i == 0) {
      $form['sent'][0]['title'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => t('No old content'),
      ];
    }
    
    $form['legend'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => t('This subset of the <b>message_digest</b> table includes all sent items meaning <strong>sent=1</strong>. If an item has been sent via cron or the <em>Send</em> tab, items also have the notifier updated to <strong>message_digest:never</strong> to prevent inadvertent resending. If an item has a different notifier but is also <strong>sent=1</strong>, it could indicate some backend confusion and may need further evaluation.'),
    ];

    if ($i != 0) {
      $form['verify'] = [
        '#type' => 'checkbox',
        '#title' => t('Check to confirm purge')
      ];
      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Purge'),
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
      $form_state->setErrorByName('verify', t('Must verify to proceed with purge'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $purged = message_digest_admin_purge();

    \Drupal::messenger()->addStatus(t('%purged record(s) cleared from digest table.', ['%purged' => $purged]));
  }  

}  

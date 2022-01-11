<?php
/**
 * @file
 * Definition of Drupal\ask_us\Plugin\Block\AskUsBlock
 */

namespace Drupal\ask_us\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the AskUsBlock
 *
 * @Block(
 *   id = "ask_us_util_block",
 *   admin_label = @Translation("Ask Us Utility Block"),
 *   category = @Translation("custom"),
 * )
 */
class AskUsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();
    $is_mobile = $blockConfig['is_mobile'];

    $template = 'ask_a_librarian_util';
    if ($is_mobile) {
      $template = 'ask_a_librarian_util_mobile';
    }
    return [
      '#theme' => $template,
      '#ask_title' => $blockConfig['ask_title'],
      '#faq_form' => $blockConfig['faq_form'],
      '#libchat_id' => $blockConfig['libchat_id'],
      '#link_1_url' => $blockConfig['link_1_url'],
      '#link_1_text' => $blockConfig['link_1_text'],
      '#link_2_url' => $blockConfig['link_2_url'],
      '#link_2_text' => $blockConfig['link_2_text'],
      '#cache' => [
        'max-age' => 3600,
      ]
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form = parent::blockForm($form, $form_state);

    $form['libchat_id'] = [
      '#type' => 'textfield',
      '#title' => t('LibChat ID'),
      '#description' => t('E.g., libchat_ab67...'),
      '#default_value' =>  isset($config['libchat_id']) ? $config['libchat_id'] : null,
    ];
    $form['ask_title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' =>  isset($config['ask_title']) ? $config['ask_title'] : null,
    ];
    $form['faq_form'] = [
      '#type' => 'textfield',
      '#title' => t('FAQ Form Submit Text'),
      '#default_value' =>  isset($config['faq_form']) ? $config['faq_form'] : null,
      '#required' => TRUE,
    ];
    $form['link_1_url'] = [
      '#type' => 'textfield',
      '#title' => t('Link One URL'),
      '#default_value' =>  isset($config['link_1_url']) ? $config['link_1_url'] : null,
      '#required' => TRUE,
    ];
    $form['link_1_text'] = [
      '#type' => 'textfield',
      '#title' => t('Link One Text'),
      '#default_value' =>  isset($config['link_1_text']) ? $config['link_1_text'] : null,
      '#required' => TRUE,
    ];
    $form['link_2_url'] = [
      '#type' => 'textfield',
      '#title' => t('Link Two URL'),
      '#default_value' =>  isset($config['link_2_url']) ? $config['link_2_url'] : null,
      '#required' => TRUE,
    ];
    $form['link_2_text'] = [
      '#type' => 'textfield',
      '#title' => t('Link Two Text'),
      '#default_value' =>  isset($config['link_2_text']) ? $config['link_2_text'] : null,
      '#required' => TRUE,
    ];
    $form['is_mobile'] = [
      '#type' => 'checkbox',
      '#title' => t('Is Mobile Block?'),
      '#default_value' => isset($config['is_mobile']) ? $config['is_mobile'] : NULL,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('libchat_id', $form_state->getValue('libchat_id'));
    $this->setConfigurationValue('faq_form', $form_state->getValue('faq_form'));
    $this->setConfigurationValue('ask_title', $form_state->getValue('ask_title'));
    $this->setConfigurationValue('link_1_url', $form_state->getValue('link_1_url'));
    $this->setConfigurationValue('link_1_text', $form_state->getValue('link_1_text'));
    $this->setConfigurationValue('link_2_url', $form_state->getValue('link_2_url'));
    $this->setConfigurationValue('link_2_text', $form_state->getValue('link_2_text'));
    $this->setConfigurationValue('is_mobile', $form_state->getValue('is_mobile'));
  }
}

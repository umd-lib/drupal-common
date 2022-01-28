<?php
/**
 * @file
 * Definition of Drupal\lib_cal\Plugin\Block\LibButtonBlock
 */

namespace Drupal\lib_cal\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lib_cal\Controller\LibHoursController;
use Drupal\lib_cal\Helper\LibCalSettingsHelper;

/**
 * Implements the LibHoursBlock
 * 
 * @Block(
 *   id = "lib_button",
 *   admin_label = @Translation("Lib Button"),
 *   category = @Translation("custom"),
 * )
 */
class LibButtonBlock extends BlockBase {

  private $configHelper;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();

    return [
      '#theme' => 'lib_button',
      '#button_text' => $blockConfig['button_text'],
      '#button_url' => $blockConfig['button_url'],
      '#cache' => [
        'max-age' => 3600,
      ]
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $this->configHelper = LibCalSettingsHelper::getInstance();

    $form['button_text'] = [
      '#type' => 'textfield',
      '#title' => t('Button Text'),
      '#default_value' =>  isset($config['button_text']) ? $config['button_text'] : null,
      '#required' => TRUE,
    ];
    $form['button_url'] = [
      '#type' => 'textfield',
      '#title' => t('Button URL'),
      '#default_value' =>  isset($config['button_url']) ? $config['button_url'] : null,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('button_text', $form_state->getValue('button_text'));
    $this->setConfigurationValue('button_url', $form_state->getValue('button_url'));
  }
}

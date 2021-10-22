<?php
/**
 * @file
 * Definition of Drupal\lib_cal\Plugin\Block\LibHoursTodayBlock
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
 *   id = "lib_hours_today",
 *   admin_label = @Translation("Lib Cal Hours"),
 *   category = @Translation("custom"),
 * )
 */
class LibHoursTodayBlock extends BlockBase {

  private $configHelper;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();
    $libHoursController = new LibHoursController();

    if ($blockConfig['weekly_display']) {
      $template = 'lib_hours_range';
      $hours = $libHoursController->getThisWeek($blockConfig['libraries']);
    } else {
      $template = 'lib_hours_today';
      $hours = $libHoursController->getToday($blockConfig['libraries']);
    }

    return [
      '#theme' => $template,
      '#hours' => $hours,
      '#branch_prefix' => $blockConfig['branch_prefix'],
      '#branch_suffix' => $blockConfig['branch_suffix'],
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
    $this->configHelper = LibCalSettingsHelper::getInstance();

    $form['libraries'] = [
      '#type' => 'select',
      '#title' => t('Libraries'),
      '#default_value' =>  isset($config['libraries']) ? explode(',',$config['libraries']) : array(),
      '#required' => TRUE,
      '#options' => $this->configHelper->getLibrariesOptions(),
      '#multiple' => TRUE,
    ];
    $form['branch_prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Branch Prefix'),
      '#description' => t('E.g., Today\'s'),
      '#default_value' =>  isset($config['branch_prefix']) ? $config['branch_prefix'] : null,
    ];
    $form['branch_suffix'] = [
      '#type' => 'textfield',
      '#title' => t('Branch Suffix'),
      '#description' => t('E.g., Hours'),
      '#default_value' =>  isset($config['branch_suffix']) ? $config['branch_suffix'] : null,
    ];
    $form['weekly_display'] = [
      '#type' => 'checkbox',
      '#title' => t('Weekly Display'),
      '#description' => t('Disply the current week\'s hours'),
      '#default_value' => isset($config['weekly_display']) ? $config['weekly_display'] : NULL,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $libraries = $form_state->getValue('libraries');

    // the api wants a comma-seperated string.
    $libraries = implode(',', $libraries);
    $this->setConfigurationValue('libraries', $libraries);
    $this->setConfigurationValue('branch_prefix', $form_state->getValue('branch_prefix'));
    $this->setConfigurationValue('branch_suffix', $form_state->getValue('branch_suffix'));
    $this->setConfigurationValue('weekly_display', $form_state->getValue('weekly_display'));
  }
}

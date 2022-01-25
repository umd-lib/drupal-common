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
    $is_mobile = false;

    if ($blockConfig['weekly_display']) {
      $template = 'lib_hours_range';
      $hours = $libHoursController->getThisWeek($blockConfig['libraries']);
    } else {
      switch ($blockConfig['display_type']) {
        case 'today':
          $template = 'lib_hours_today';
          $hours = $libHoursController->getToday($blockConfig['libraries']);
          $hours = $this->sortLocationsHeirarchy($hours);
          break;
        case 'weekly':
          $template = 'lib_hours_range';
          $hours = $libHoursController->getThisWeek($blockConfig['libraries']);
          break;
        case 'utility_nav':
          $template = 'lib_hours_today_util';
          $hours = $libHoursController->getToday($blockConfig['libraries']);
          $is_mobile = $blockConfig['is_mobile'];
          break;
         default:
          $template = 'lib_hours_today';
          break;
      }
    }

    $row_class = 'lib-hours-constrained';
    $grid_class = null;
    $current_date = null;
    if ($blockConfig['grid_display']) {
      $row_class = 'row';
      $grid_class = 'col-800-4';
    }
    if ($blockConfig['date_display']) {
      $current_date = date("c");
    }

    $hours_class = 'hours-main-grid';
    if (count($hours) == 1) {
      $hours_class = 'hours-main';
    }

    return [
      '#theme' => $template,
      '#hours' => $hours,
      '#branch_prefix' => $blockConfig['branch_prefix'],
      '#branch_suffix' => $blockConfig['branch_suffix'],
      '#row_class' => $row_class,
      '#grid_class' => $grid_class,
      '#hours_class' => $hours_class,
      '#current_date' => $current_date,
      '#is_mobile' => $is_mobile,
      '#shady_grove_url' => $blockConfig['shady_grove_url'],
      '#all_libraries_url' => $blockConfig['all_libraries_url'],
      '#cache' => [
        'max-age' => 0,
      ]
    ];
  }

  function sortLocationsHeirarchy($hours) {
    $children = [];
    foreach ($hours as $key => $loc) {
      if (!empty($loc['parent_lid'])) {
        $loc['name'] = '|chev| ' . $loc['name'];
        $children[$loc['parent_lid']][] = $loc;
        unset($hours[$key]);
      }
    }
    $output = [];
    foreach ($hours as $loc) {
      $plid = $loc['lid'];
      $output[] = $loc;
      if (!empty($children[$plid])) {
        foreach ($children[$plid] as $child) {
          $output[] = $child;
        }
      }
    }
    return $output;
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
    $form['all_libraries_url'] = [
      '#type' => 'textfield',
      '#title' => t('All Libraries URL'),
      '#default_value' =>  isset($config['all_libraries_url']) ? $config['all_libraries_url'] : null,
    ];
    $form['shady_grove_url'] = [
      '#type' => 'textfield',
      '#title' => t('Shady Grove Hours URL'),
      '#default_value' =>  isset($config['shady_grove_url']) ? $config['shady_grove_url'] : null,
    ];
    $form['weekly_display'] = [
      '#type' => 'checkbox',
      '#title' => t('Weekly Display? (Deprecated)'),
      '#description' => t('Use the Display Type from now on. Will be removed after demo period is over.'),
      '#default_value' => isset($config['weekly_display']) ? $config['weekly_display'] : NULL,
    ];
    $display_types = ['today' => t('Today'), 'weekly' => t('Weekly'), 'utility_nav' => t('Utility Nav')];
    $form['display_type'] = [
      '#type' => 'select',
      '#title' => t('Display Type'),
      '#default_value' => isset($config['display_type']) ? $config['display_type'] : null,
      '#required' => TRUE,
      '#options' => $display_types,
    ];
    $form['is_mobile'] = [
      '#type' => 'checkbox',
      '#title' => t('Is Mobile Block?'),
      '#description' => t('Note: Only affects Utility Nav displays. This option is otherwise ignored.'),
      '#default_value' => isset($config['is_mobile']) ? $config['is_mobile'] : NULL,
    ];
    $form['grid_display'] = [
      '#type' => 'checkbox',
      '#title' => t('Grid Display?'),
      '#description' => t('If unchecked, defaults to list display.'),
      '#default_value' => isset($config['grid_display']) ? $config['grid_display'] : NULL,
    ];
    $form['date_display'] = [
      '#type' => 'checkbox',
      '#title' => t('Show current date?'),
      '#default_value' => isset($config['date_display']) ? $config['date_display'] : NULL,
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
    $this->setConfigurationValue('shady_grove_url', $form_state->getValue('shady_grove_url'));
    $this->setConfigurationValue('all_libraries_url', $form_state->getValue('all_libraries_url'));
    $this->setConfigurationValue('branch_suffix', $form_state->getValue('branch_suffix'));
    $this->setConfigurationValue('weekly_display', $form_state->getValue('weekly_display'));
    $this->setConfigurationValue('grid_display', $form_state->getValue('grid_display'));
    $this->setConfigurationValue('date_display', $form_state->getValue('date_display'));
    $this->setConfigurationValue('display_type', $form_state->getValue('display_type'));
    $this->setConfigurationValue('is_mobile', $form_state->getValue('is_mobile'));
  }
}

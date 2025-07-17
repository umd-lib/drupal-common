<?php

namespace Drupal\block_ajax\Form;

use Drupal\block\BlockForm;
use Drupal\block_ajax\AjaxBlocks;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Override form for block instance forms.
 */
class AjaxBlockForm extends BlockForm {

  /**
   * The Ajax Blocks service.
   *
   * @var \Drupal\block_ajax\AjaxBlocks
   */
  protected $ajaxBlocksService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->ajaxBlocksService = $container->get('block_ajax.ajax_blocks');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    $block = $form_state->getFormObject()->getEntity();
    $settings = $block->get('settings');

    $form['settings']['block_ajax'] = [
      '#type' => 'details',
      '#title' => $this->t('Ajax block'),
      '#description' => $this->t('Configure settings for Ajax block.'),
      '#tree' => TRUE,
      '#open' => FALSE,
      '#access' => $this->ajaxBlocksService->hasAccess(),
    ];

    $form['settings']['block_ajax']['is_ajax'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Load block via Ajax'),
      '#description' => $this->t('If checked, block will be loaded via Ajax method.'),
      '#default_value' => $settings['block_ajax']['is_ajax'] ?? FALSE,
    ];

    $form['settings']['block_ajax']['max_age'] = [
      '#type' => 'select',
      '#title' => $this->t('Max age'),
      '#default_value' => $settings['block_ajax']['max_age'] ?? 0,
      '#options' => $this->ajaxBlocksService->getMaxAgeOptions(),
      '#description' => $this->t('The maximum time/age ajax block to be cached for.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['settings']['block_ajax']['show_spinner'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show spinner'),
      '#description' => $this->t('If checked, spinner will be displayed when loading via Ajax method.'),
      '#default_value' => $settings['block_ajax']['show_spinner'] ?? FALSE,
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['settings']['block_ajax']['placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder text'),
      '#description' => $this->t('Set placeholder text which appears before Ajax block is loaded.'),
      '#default_value' => $settings['block_ajax']['placeholder'] ?? '',
      '#size' => 30,
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    // Ajax defaults form elements.
    $form['settings']['block_ajax']['ajax_defaults'] = [
      '#type' => 'details',
      '#title' => $this->t('Ajax defaults'),
      '#description' => $this->t('Configure settings for Ajax defaults.'),
      '#open' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $method = $settings["block_ajax"]["ajax_defaults"]["method"] ?? 'POST';
    $form['settings']['block_ajax']['ajax_defaults']['method'] = [
      '#type' => 'radios',
      '#title' => $this->t('Method'),
      '#default_value' => $method,
      '#options' => ['POST' => 'POST', 'GET' => 'GET'],
      '#description' => $this->t('The method which use ajax for request.'),
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $timeout = $settings["block_ajax"]["ajax_defaults"]["timeout"] ?? '10000';
    $form['settings']['block_ajax']['ajax_defaults']['timeout'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Timeout'),
      '#description' => $this->t('Set the timeout in millisecond for request.'),
      '#default_value' => $timeout,
      '#size' => 30,
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $others = $settings["block_ajax"]["ajax_defaults"]["others"] ?? ['async', 0];
    $form['settings']['block_ajax']['ajax_defaults']['others'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Others'),
      '#options' => ['async' => $this->t('Async'), 'cache' => $this->t('Cache')],
      '#default_value' => $others,
      '#size' => 30,
      '#states' => [
        'visible' => [
          ':input[name="settings[block_ajax][is_ajax]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Get block entity.
    $block = $this->getEntity();

    // Get block ajax settings.
    $block_ajax = $form_state->getValue('settings')['block_ajax'];

    // Get block settings and set block_ajax as well.
    $settings = $block->get('settings');
    $settings['block_ajax'] = $block_ajax;

    // Set final settings.
    $block->set('settings', $settings);

    // Invalidate ajax blocks cache tags.
    if (!empty($block_ajax['is_ajax'])) {
      $this->ajaxBlocksService->invalidateAjaxBlocks();
    }
  }

}

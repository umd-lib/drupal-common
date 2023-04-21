<?php

namespace Drupal\reusable_searchbar\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing;
use Drupal\Core\Url;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormBase;

/**
 * Provides the Reusable Search block.
 *
 * @Block(
 *   id = "reusable_searchbar_search",
 *   admin_label = @Translation("Reusable Searchbar"),
 *   category = @Translation("Reusable Searchbar"),
 * )
 */
class ReusableSearchbarBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  protected $formBuilder;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The id for the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin implementaton.
   * @param Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The "form_builder" service instance to use.
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      FormBuilderInterface $formBuilder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $blockConfig = $this->getConfiguration();
    $search_action = $blockConfig['search_page'];
    $search_placeholder = $blockConfig['search_placeholder'];
    $search_param = $blockConfig['search_param'];
    $search_facet = !empty($blockConfig['search_facet']) ? $blockConfig['search_facet'] : null;
    $form_defaults = array();
    $form_defaults['default_action'] = null;
    $form_defaults['search_placeholder'] = null;
    $form_defaults['search_param'] = $search_param;
    $form_defaults['search_facet'] = $search_facet;
    if (!empty($search_action)) {
      $form_defaults['default_action'] = $search_action;
    }
    if (!empty($search_placeholder)) {
      $form_defaults['search_placeholder'] = $search_placeholder;
    }
    $form = $this->formBuilder->getForm('Drupal\reusable_searchbar\Form\ReusableSearchbarForm', $form_defaults);
    return [
      '#theme' => 'reusable_searchbar_search_block',
      '#reusable_searchbar_search_form' => $form,
      '#cache' => [
        'max-age' => 3600,
      ],
      '#attached' => [
        'library' => [
          'reusable_searchbar/reusable_searchbar',
        ],
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['search_page'] = [
      '#type' => 'textfield',
      '#title' => t('Search Page Override'),
      '#default_value' =>  isset($config['search_page']) ? $config['search_page'] : null,
      '#description' => t('Leave blank to use block page.'),
    ];
    $form['search_param'] = [
      '#type' => 'textfield',
      '#title' => t('Search Parameter'),
      '#default_value' =>  isset($config['search_param']) ? $config['search_param'] : 'query',
      '#description' => t('Leave this as the default (query) in most cases.'),
    ];
    $form['search_facet'] = [
      '#type' => 'textfield',
      '#title' => t('Search Facet'),
      '#default_value' =>  isset($config['search_facet']) ? $config['search_facet'] : null,
      '#description' => t('Filter on which facet (if any)'),
    ];
    $form['search_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Search Placeholder'),
      '#default_value' =>  isset($config['search_placeholder']) ? $config['search_placeholder'] : null,
      '#description' => t('E.g., Search collection...'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('search_page', $form_state->getValue('search_page'));
    $this->setConfigurationValue('search_param', $form_state->getValue('search_param'));
    $this->setConfigurationValue('search_facet', $form_state->getValue('search_facet'));
    $this->setConfigurationValue('search_placeholder', $form_state->getValue('search_placeholder'));
  }
}

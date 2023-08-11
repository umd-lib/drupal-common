<?php

namespace Drupal\bento_generic\Plugin\Block;

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
 * Provides the Bento Generic Search block.
 *
 * @Block(
 *   id = "bento_generic_search",
 *   admin_label = @Translation("Bento Generic: Search Form"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoGenericSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $search_title = $blockConfig['search_title'];
    $form_defaults = array();
    $form_defaults['default_action'] = null;
    $form_defaults['search_placeholder'] = null;
    if (!empty($search_action)) {
      $nid = EntityAutocomplete::extractEntityIdFromAutocompleteInput($search_action);
      if (!empty($nid) && $nid > 0) {
        $url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $nid]);
        if (!empty($url)) {
          $action_url = $url->toString();
          $form_defaults['default_action'] = $action_url;
        }
      }
    }
    if (!empty($search_placeholder)) {
      $form_defaults['search_placeholder'] = $search_placeholder;
    }
    if (!empty($search_title)) {
      $form_defaults['search_title'] = $search_title;
    }
    $form = $this->formBuilder->getForm('Drupal\bento_generic\Form\BentoGenericSearchForm', $form_defaults);
    return [
      '#theme' => 'bento_generic_search_block',
      '#bento_generic_search_form' => $form,
      '#cache' => [
        'max-age' => 3600,
      ],
      '#attached' => [
        'library' => [
          'bento_generic/form_util',
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
      '#autocomplete_route_name' => 'bento_generic.autocomplete.urls',
    ];
    $form['search_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Search Placeholder'),
      '#default_value' =>  isset($config['search_placeholder']) ? $config['search_placeholder'] : null,
      '#description' => t('E.g., Search books and more!'),
    ];
    $form['search_title'] = [
      '#type' => 'textfield',
      '#title' => t('Search Title'),
      '#default_value' =>  isset($config['search_title']) ? $config['search_title'] : null,
      '#description' => t('If empty, "Search" will be used.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('search_page', $form_state->getValue('search_page'));
    $this->setConfigurationValue('search_placeholder', $form_state->getValue('search_placeholder'));
    $this->setConfigurationValue('search_title', $form_state->getValue('search_title'));
  }
}

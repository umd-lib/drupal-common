<?php

namespace Drupal\bento\Plugin\Block;

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
 * Provides the Bento Search block.
 *
 * @Block(
 *   id = "bento_search",
 *   admin_label = @Translation("Bento: Search Form"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $search_heading = $blockConfig['search_heading'];
    $form_defaults = array();
    $form_defaults['default_action'] = null;
    $form_defaults['search_placeholder'] = null;
    $form_defaults['search_heading'] = null;
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
    if (!empty($search_heading)) {
      $form_defaults['search_heading'] = $search_heading;
    }
    $form = $this->formBuilder->getForm('Drupal\bento\Form\BentoSearchForm', $form_defaults);
    return [
      '#theme' => 'bento_search_block',
      '#bento_search_form' => $form,
      '#cache' => [
        'max-age' => 3600,
      ],
      '#attached' => [
        'library' => [
          'bento/form_util',
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
      '#autocomplete_route_name' => 'bento.autocomplete.urls',
    ];
    $form['search_placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Search Placeholder'),
      '#default_value' =>  isset($config['search_placeholder']) ? $config['search_placeholder'] : null,
      '#description' => t('E.g., Search books and more!'),
    ];
    $form['search_heading'] = [
      '#type' => 'textfield',
      '#title' => t('Search Heading'),
      '#default_value' =>  isset($config['search_heading']) ? $config['search_heading'] : null,
      '#description' => t('E.g., Search All'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('search_page', $form_state->getValue('search_page'));
    $this->setConfigurationValue('search_placeholder', $form_state->getValue('search_placeholder'));
    $this->setConfigurationValue('search_heading', $form_state->getValue('search_heading'));
  }
}

<?php

namespace Drupal\facet_overrides\Plugin\Block;

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
 * Provides a no results block for checked facet.
 *
 * @Block(
 *   id = "no_facet_results",
 *   admin_label = @Translation("No Facet Results"),
 *   category = @Translation("Search"),
 * )
 */
class NoFacetResultsBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $raw_message = $blockConfig['no_results_message'];
    $rendered_message = $raw_message;

    $full_uri = \Drupal::request()->getRequestUri();
    $parts = parse_url($full_uri);

    $facet_search = null;
    if (!empty($parts)) {
      $query_string = !empty($parts['query']) ? $parts['query'] : null;
      if (!empty($query_string)) {
        parse_str($query_string, $query_array);
        if (!empty($query_array['query'])) {
          $rendered_message = str_replace('%query%', $query_array['query'], $rendered_message);
        } else {
          $rendered_message = "No results.";
        }
        $collection = null;
        if (!empty($query_array['f'])) {
          $facets = $query_array['f'];
          foreach ($facets as $facet) {
            if (str_contains($facet, 'collection')) {
              $collection = $facet;
              break;
            }
          }
        }
        if (!empty($collection)) {
          $query_out = ['query' => ['f' => [$collection]]];
          $url = Url::fromUri('base:/searchnew', $query_out);
          $facet_search = 'Try another search in: <a href="' . $url->toString() . '">' . str_replace("collection:", '', $collection) . '</a>.';
        }
      }
    }

    return [
      '#theme' => 'facet_overrides_no_results',
      '#no_results_message' => $rendered_message,
      '#facet_search' => $facet_search,
      '#cache' => [
        'max-age' => 0,
      ],
      '#attached' => [
        'library' => [
          'facet_overrides/facet_overrides',
        ],
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['no_results_message'] = [
      '#type' => 'textfield',
      '#title' => t('No Results Message'),
      '#default_value' =>  isset($config['no_results_message']) ? $config['no_results_message'] : null,
      '#description' => t('No results message with placeholders. Use the %query% placeholder, and it will be replaced with URL value.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('no_results_message', $form_state->getValue('no_results_message'));
  }
}

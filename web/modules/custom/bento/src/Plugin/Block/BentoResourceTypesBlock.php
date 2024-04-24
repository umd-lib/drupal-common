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
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Provides the Bento Resource Types block.
 *
 * @Block(
 *   id = "bento_resource_types",
 *   admin_label = @Translation("Bento: Resource Types"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoResourceTypesBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $resource_types = $blockConfig['resource_types'];
    $resource_types_heading = $blockConfig['resource_types_heading'];
    $query = \Drupal::request()->query->get('query');
    $has_query = TRUE;
    if (empty($query) || $query == '') {
      $has_query = FALSE;
    }
    if (!empty($resource_types)) {
      $replaced_resources = [];
      foreach ($resource_types as $type => $resource) {
        if ($has_query) {
          $resource['url'] = str_replace('%placeholder%', $query, $resource['url']);
          $replaced_resources[$type] = $resource;
        } else {
          $resource['url'] = $resource['url_empty'];
          $replaced_resources[$type] = $resource;
        }
      }
    }

    return [
      '#theme' => 'bento_resource_types_block',
      '#resource_types' => $replaced_resources,
      '#resource_types_heading' => $resource_types_heading,
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
 
    $form['resource_types_heading'] = [
      '#type' => 'textfield',
      '#title' => t('Block Header'),
      '#default_value' =>  !empty($config['resource_types_heading']) ? $config['resource_types_heading'] : t('Resource Types'),
    ];
    $form['resource_types'] = [
      '#type' => 'textarea',
      '#title' => t('Resource Types'),
      '#default_value' =>  Yaml::dump($config['resource_types']),
      '#description' => t('A YAML formatted list of searchers and their anchor links. See the README.md file.'),
      '#rows' => 7,
      '#cols' => 100,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    $yaml_fields = ['resource_types'];

    foreach ($yaml_fields as $yfield) {
      $yfield_str = trim($form_state->getValue($yfield));

      // A starting line with "---" is required by the YAML parser, so add it,
      // if it is not present.
      if (!str_starts_with($yfield_str, "---")) {
        $yfield_str = "---\n" . $yfield_str;
      }
      $decoded_yfield = [];

      try {
        $decoded_yfield = Yaml::parse($yfield_str);
      }
      catch (ParseException $e) {
        $error_message = $form[$yfield]['#title'] . " has missing or invalid YAML.";
        $form_state->setErrorByName($yfield, $error_message);
        return;
      }

      if (count(array_keys($decoded_yfield)) == 0) {
        $error_message = $form[$yfield]['#title'] . " has missing or invalid YAML.";
        $form_state->setErrorByName($yfield, $error_message);
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('resource_types_heading', $form_state->getValue('resource_types_heading'));

    $yaml_fields = ['resource_types'];

    foreach ($yaml_fields as $yfield) {
      $yfield_str = $form_state->getValue($yfield);
      try {
        $yfield_values = Yaml::parse($yfield_str);
        $this->setConfigurationValue($yfield, $yfield_values);
      }
      catch (ParseException $pe) {
        // Shouldn't happen, because invalid YAML should be caught by
        // "validateForm" method.
        $this->logger('bento')->error("Error parsing 'search categories' YAML: " . $pe);
      }
    }
  }

}

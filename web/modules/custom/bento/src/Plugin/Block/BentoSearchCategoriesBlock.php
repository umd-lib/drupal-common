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
 * Provides the Bento Search block.
 *
 * @Block(
 *   id = "bento_search_categories",
 *   admin_label = @Translation("Bento: Search Categories"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoSearchCategoriesBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $search_categories = $blockConfig['search_categories'];
    $search_categories_heading = $blockConfig['search_categories_heading'];

    return [
      '#theme' => 'bento_search_categories_block',
      '#search_categories' => $search_categories,
      '#search_categories_heading' => $search_categories_heading,
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();
 
    $form['search_categories_heading'] = [
      '#type' => 'textfield',
      '#title' => t('Block Header'),
      '#default_value' =>  !empty($config['search_categories_header']) ? $config['search_categories_header'] : t('Search Categories'),
    ];
    $form['search_categories'] = [
      '#type' => 'textarea',
      '#title' => t('Search Categories'),
      '#default_value' =>  Yaml::dump($config['search_categories']),
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
    $yaml_fields = ['search_categories'];

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
    $this->setConfigurationValue('search_categories_heading', $form_state->getValue('search_categories_heading'));

    $yaml_fields = ['search_categories'];

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

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
 * Provides the Bento Generic Summary block.
 *
 * @Block(
 *   id = "bento_generic_summary",
 *   admin_label = @Translation("Bento: Summary"),
 *   category = @Translation("Bento Quick Search"),
 * )
 */
class BentoGenericSummaryBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
    $search_targets_raw = $blockConfig['search_targets'];
    $search_targets = [];
    if (!empty($search_targets_raw)) {
      $search_targets = $this->extractAllowedValues($search_targets_raw); 
    }
    return [
      '#theme' => 'bento_generic_summary_block',
      '#search_targets' => $search_targets,
      '#attached' => [
        'library' => [
          'bento/results_util',
        ],
      ],
    ];
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->getConfiguration();

    $form['target_info'] = [
      '#type' => 'item',
      '#markup' => t('This is the optional Summary block, which can be added to the top of a Bento search page.'),
    ];
    $available_targets = "
      bento-lib-answers-summary|FAQs from Ask Us!<br />
      bento-lib-guides-summary|Research Guides<br />
      bento-world-cat-discovery-api-summary|Books and More<br />
      bento-world-cat-discovery-api-article-summary|Articles<br />
      bento-libraries-website-summary|Library's Website<br />
      bento-database-finder-summary|Databases<br />
    ";
    $form['target_suggestions'] = [
      '#type' => 'item',
      '#title' => t('Available Targets'),
      '#markup' => $available_targets,
    ];
    $form['search_targets'] = [
      '#type' => 'textarea',
      '#title' => t('Search Targets'),
      '#default_value' =>  isset($config['search_targets']) ? $config['search_targets'] : null,
      '#description' => t('One search target per line, pipe seperated. E.g., bento-world-cat-discovery-api-summary|Books and More'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('search_targets', $form_state->getValue('search_targets'));
  }

  /**
   * Extracts the allowed values array from the allowed_values element.
   *
   * @param string $string
   *   The raw string to extract values from.
   *
   * @return array|null
   *   The array of extracted key/value pairs, or NULL if the string is invalid.
   *
   * @see \Drupal\options\Plugin\Field\FieldType\ListItemBase::allowedValuesString()
   */
  protected static function extractAllowedValues($string) {
    $values = [];

    $list = explode("\n", $string);
    $list = array_map('trim', $list);
    $list = array_filter($list, 'strlen');

    $generated_keys = $explicit_keys = FALSE;
    foreach ($list as $position => $text) {
      // Check for an explicit key.
      $matches = [];
      if (preg_match('/(.*)\|(.*)/', $text, $matches)) {
        // Trim key and value to avoid unwanted spaces issues.
        $key = strtolower(trim($matches[1]));
        $value = trim($matches[2]);
        $explicit_keys = TRUE;
      }
      else {
        return;
      }

      $values[$key] = $value;
    }

    return $values;
  }
}

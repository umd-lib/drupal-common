<?php

/**
 * @file
 * Definition of Drupal\equipment_tracking\Plugin\views\field\EquipmentAvailability
 */

namespace Drupal\equipment_tracking\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\aleph_connector\Controller\AlephController;
use Drupal\Component\Datetime\DateTimePlus;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("aleph_equipment_available")
 */
class EquipmentAvailability extends FieldPluginBase {

  /**
   * The logger instance.
   *
   * @var Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelInterface $logger
   *   The logger instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->definition = $plugin_definition + $configuration;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      // Inject a logger instance.
      $container->get('logger.factory')->get('equipment_availability')
    );
  }

  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    return $options;
  }

  /**
   * Provide the options form.
   *
   * @note
   *   More investigation needed to determine how to actually use these options.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['unused'] = array(
      '#title' => $this->t('Unused'),
      '#description' => $this->t('A description can go here.'),
      '#type' => 'textfield',
    );
    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $bibnums = [];
    $aleph = new AlephController();

    if (!$this->isValueEmpty($aleph->getQueryField(), TRUE)) {
      $field = $this->view->field[$aleph->getQueryField()];
      if ($field instanceof \Drupal\search_api\Plugin\views\field\SearchApiEntityField) {
        $items = $field->getItems($values);
        foreach ($items as $item) {
          $bibnum = $item['value'];
          $bibnums[] = $bibnum;
        }
      }
    }

    if (count($bibnums) > 0) {
      if ($bibnum_data = $aleph->getBibnumData($bibnums)) {
        return $this->availabilityField($bibnums, $bibnum_data);
      }
    }
    return FALSE;
  }

  /**
   * Generate sample page content for umd-examples page.
   */
  public function availabilityField($bibnums, $availability_data) {
    $available_count = 0;
    $earliest_raw_date = null;
    $processed_date = NULL;

    foreach ($availability_data as $datum) {
      $available_count += $datum['available'];

      if ($raw_date = ($datum['mindue'])) {
        if (!$earliest_raw_date || ($raw_date < $earliest_raw_date)) {
          $earliest_raw_date = $raw_date;
          try {
            // Expected raw_data format: 202107301630
            // (i.e. 16:30 on July 30, 2021)
            $datetime_p = DateTimePlus::createFromFormat('YmdHi', $raw_date);
            $processed_date = $datetime_p->format('g:i A \o\n F j, Y');
          }
          catch (\InvalidArgumentException $e) {
            $this->logger->error("InvalidArgumentException: Error parsing date: '$raw_date'.");
          }
          catch (\UnexpectedValueException $e) {
            $this->logger->error("UnexpectedValueException: Error parsing date: '$raw_date'.", $e);
          }
        }
      }
    }

    return [
      '#theme' => 'aleph_equipment_available',
      '#equipment_count' => $available_count,
      '#equipment_mindue' => $processed_date,
      '#equipment_sysnum' => implode(',', $bibnums),
    ];
  }
}

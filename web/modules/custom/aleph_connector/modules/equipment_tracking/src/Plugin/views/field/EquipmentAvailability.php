<?php

namespace Drupal\equipment_tracking\Plugin\views\field;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\aleph_connector\Controller\AlephController;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\search_api\Plugin\views\field\SearchApiEntityField;
use Drupal\Component\Datetime\DateTimePlus;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler for equipment availibility.
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
   * Constructs an EquipmentAvailability object.
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger) {
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
   * {@inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // Get the field to query from the configuration.
    $aleph = new AlephController();
    $equipment_query_field = $aleph->getQueryField();
    if ($this->isValueEmpty($equipment_query_field, TRUE)) {
      // Exit if value has not been configured.
      return [
        '#theme' => 'aleph_equipment_available',
        '#error' => true,
      ];
    }

    // Retrieve the list of bibnums for the item in the row.
    $bibnums = [];
    $field = $this->view->field[$equipment_query_field];
    if ($field instanceof SearchApiEntityField) {
      $items = $field->getItems($values);
      foreach ($items as $item) {
        $bibnum = $item['value'];
        $bibnums[] = $bibnum;
      }
    }

    if (count($bibnums) > 0) {
      // Generate the render array from the availability data.
      if ($bibnum_data = $aleph->getBibnumData($bibnums)) {
        return $this->availabilityField($bibnums, $bibnum_data);
      }
    }
    return [
      '#theme' => 'aleph_equipment_available',
      '#error' => true,
    ];
  }

  /**
   * Returns a render array containing availability data for the given bibnums.
   *
   * @param array $bibnums
   *   An array of bibnums for a single equipment record.
   * @param array $availability_data
   *   The equipment availability data from Aleph.
   *
   * @return array
   *   A render array containing availability data for the given
   *   bibnums.
   */
  public function availabilityField(array $bibnums, array $availability_data) {
    $available_count = 0;
    $earliest_raw_date = NULL;
    $processed_date = NULL;
    $errors = false;
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
            $errors = true;
            $this->logger->error("InvalidArgumentException: Error parsing date: '$raw_date'.");
          }
          catch (\UnexpectedValueException $e) {
            $errors = true;
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
      '#error' => $available_count <= 0 && errors == true ? true : false,
    ];
  }

}

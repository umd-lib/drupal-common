<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "get_parent"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: get_parent
 *   source: text
 * @endcode
 *
 */
class GetParent extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $acronym = strtoupper($value);
    $database = \Drupal::database();
    $query = $database->select('node_revision__field_short_name', 'sn');
    $query->fields('sn', array('entity_id'));
    $query->condition('field_short_name_value', $acronym, '=');
    $result = $query->execute()->fetchField();

    return $result;
  }

} 

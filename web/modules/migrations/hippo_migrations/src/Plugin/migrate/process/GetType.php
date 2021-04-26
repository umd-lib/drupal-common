<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "get_type"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: get_type
 *   source: text
 * @endcode
 *
 */
class GetType extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $value = str_replace('_', ' ', $value);
    return ucwords($value);
  }

} 

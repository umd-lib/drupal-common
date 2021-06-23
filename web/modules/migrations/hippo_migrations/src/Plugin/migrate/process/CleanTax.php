<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "clean_tax"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: clean_tax
 *   source: text
 * @endcode
 *
 */
class CleanTax extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $value = strtolower($value);
    $value = str_replace('_', ' ', $value);
    $value = ucwords($value);

    return $value;
  }

} 

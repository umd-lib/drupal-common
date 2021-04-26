<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "date_custom"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: date_custom
 *   source: text
 * @endcode
 *
 */
class DateCustom extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $d = new DateTime($value);
    return $d->format('U');
  }

} 

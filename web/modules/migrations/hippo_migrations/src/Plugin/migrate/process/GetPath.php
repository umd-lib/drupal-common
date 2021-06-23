<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "get_path"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: get_path
 *   source: text
 * @endcode
 *
 */
class GetPath extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return str_replace('http://localhost:8080/site/libi', '', $value);
  }

} 

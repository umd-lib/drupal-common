<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Get path alias from a Hippo URL
 * @MigrateProcessPlugin(
 *   id = "get_category"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: get_category
 *   source: text
 * @endcode
 *
 */
class GetCategory extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $categories = ['minutes','policies','workplans','links','guidelines'];

    foreach ($categories as $category) {
      if (strpos($value, $category)) {
        return ucfirst($category);
      }
    }
    return;
  }

} 

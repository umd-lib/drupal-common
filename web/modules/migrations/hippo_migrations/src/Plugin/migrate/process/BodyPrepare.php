<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Break a binaries array into friendlier pieces
 * @MigrateProcessPlugin(
 *   id = "body_prepare"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: body_prepare
 *   source: body
 * @endcode
 *
 */
class BodyPrepare extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($values, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    // Binary Links migrate
    $values = str_replace('<a data-hippo-link="', '<a href="/sites/default/files/imported/', $values);
    
    // Image migrate
    $values = str_replace('data-hippo-link="', 'src="/sites/default/files/imported/', $values);

    return $values;
  }

} 

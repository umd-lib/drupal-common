<?php

namespace Drupal\hippo_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Break a binaries array into friendlier pieces
 * @MigrateProcessPlugin(
 *   id = "binaries_prepare"
 * )
 *
 * Do the following:
 * @code
 * field_text:
 *   plugin: binaries_prepare
 *   source: binaries_array
 * @endcode
 *
 */
class BinariesPrepare extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($values, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $images = array();
    print_r($values);
    if (!empty($values['type']) && $values['type'] == 'binary' && !empty($values['url'])) {
      print($values['url']);
      return $values['url'];
    }
    return null;
  }

} 

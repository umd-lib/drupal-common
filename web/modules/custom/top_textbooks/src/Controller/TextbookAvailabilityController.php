<?php

/**
 * @file
 * Definition for Drupal\Controller\TextbookAvailabilityController
 */

namespace Drupal\top_textbooks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\aleph_connector\Controller\AlephxController;

/**
 * Controller class for Textbook Availability
 */
class TextbookAvailabilityController extends ControllerBase implements TrustedCallbackInterface {

  public function getAvalability(array $barcodes) {
    $availability = [];
  }

  private function emptyResult() {
    return array(
      'num_available' => 0,
      'next_available_date' => null,
    );
  }

  private function getTextbookAvailabilityData($items, $barcodes) {
    $data = $this->emptyResult();

    $available_conditions = ['On Shelf', 'McK Four Hour Loan'];
    $unavailable_conditions = ['On Hold'];
    foreach($items as $item) {
      if (in_array((string) $item->barcode, $barcodes)) {
        if (in_array((string) $item->{'due-date'}, $available_conditions)) {
          $data['num_available']++;
          continue;
        } else if (in_array((string) $item->{'due-date'}, $unavailable_conditions)) {
          continue;
        } else if ($data['num_available'] == 0) {
          $datetime_str = (string) $item->{'due-date'} . ' ' . (string) $item->{'due-hour'};
          $datetime = date_create_from_format("m/d/y h:i a", $datetime_str);
          if ($datetime) {
            if (($data['next_available_date'] == null) || ($datetime < $data['next_available_date'])) {
              $data['next_available_date'] = $datetime;
            }
          } else {
            \Drupal::logger('top_textbooks')->notice('Error parsing date time: ' . $datetime_str);
            return FALSE;
          }
        }
      }
    }
    return $data;
  }

  public function getAvailability($barcodes) {
    $sys_no = null;
    $valid_barcodes = [];
    $aleph = new AlephxController();
    $items = $aleph->readValidItems($barcodes);
    if ($items != null) {
      if (count($items) < 1) {
        // \Drupal::logger('top_textbooks')->notice('No read-item results for: ' . var_export($barcodes, true));
        return $this->emptyResult();
      }
    } else {
      \Drupal::logger('top_textbooks')->notice('No valid items for: ' . var_export($barcodes, true));
    }
    foreach($items as $item) {
      array_push($valid_barcodes, (string) $item->{'z30-barcode'});
      if ($sys_no == null) {
        $sys_no = str_pad((string) $item->{'z30-doc-number'}, 9, "0",STR_PAD_LEFT);
      }
    }
    if ($sys_no != null) {
      $items = $aleph->getCircItems($sys_no);
      if (($items != null) && count($items) < 1) {
        // \Drupal::logger('top_textbooks')->notice('No circ-item results for: ' . $sys_no);
        return $this->emptyResult();
      }
      return $this->getTextbookAvailabilityData($items, $valid_barcodes);
    } else {
      \Drupal::logger('top_textbooks')->notice('Sys_No not found: Cannot retrieve circ-items without sys_no');
    }
    return FALSE;
  }


  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['getAvailability'];
  }

}
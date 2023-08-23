<?php

/**
 * @file
 * Definition of Drupal\aleph_connector\Controller\AlephController
 */

namespace Drupal\aleph_connector\Controller;

use Drupal\aleph_connector\Helper\AlephxConfigHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Controller class for Aleph X services.
 */
class AlephxController extends ControllerBase implements TrustedCallbackInterface {

  protected $config;

  public function __construct() {
    $this->config = AlephxConfigHelper::getInstance();
  }

  private function processCurlError($curl) {
    $error_msg = 'Connection error while retrieving textboox availability.';
    if (!curl_errno($curl)) {
      if (curl_getinfo($curl, CURLINFO_HTTP_CODE) != 200) {
        $response_code = 'HTTP Response Code: ' . curl_getinfo($curl, CURLINFO_HTTP_CODE);
        \Drupal::logger('aleph_connector')->notice($error_msg);
        \Drupal::logger('aleph_connector')->notice($response_code);
        return TRUE; 
      }
    } else {
      $err_no = 'Curl error: ' . curl_errno($curl);
      \Drupal::logger('aleph_connector')->notice($error_msg);
      \Drupal::logger('aleph_connector')->notice($err_no);
      return TRUE;
    }
    return FALSE;
  }

  private function getZ30Vals($xml_string) {
    $xml = simplexml_load_string($xml_string, "SimpleXMLElement", LIBXML_NOCDATA);
    $z30_match = $xml->xpath('//read-item/z30');
    if ($z30_match != null) {
      return $z30_match[0];
    }
  }

  private function getItems($xml_string) {
    $xml = simplexml_load_string($xml_string, "SimpleXMLElement", LIBXML_NOCDATA);
    return $xml->xpath('//circ-status/item-data');
  }

  private function isValidLocation($item) {
    $sublib = null;
    $col = null;
    if ($sublib_check = $this->checkStringCast($item->{'z30-sub-library'})) {
      $sublib = $sublib_check;
    }
    if ($col_check = $this->checkStringCast($item->{'z30-collection'})) {
      $col = $col_check;
    }
    return ($this->config->getSubLibrary() == $sublib) && 
            ($this->config->getCollection() == $col);
  }

  public function checkStringCast($val) {
    if (empty($val)) {
      return null;
    }

    if (is_object($val) and method_exists($val, '__toString')) {
      return (string)$val;
    }
    return null;
  }

  private function readItem($barcode) {
    $base = $this->config->getBase();
    $adm_library = $this->config->getAdmLibrary();
    $curl = curl_init();
    $url_options = '?op=read-item&library=' . $adm_library . '&item_barcode=' . trim($barcode) . '&translate=N';
    curl_setopt($curl, CURLOPT_URL, $base . $url_options);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    $has_error = $this->processCurlError($curl);
    curl_close($curl);
    if ($has_error) {
      $error_msg = 'Failed to read-item at: ' . $base . $url_options;
      \Drupal::logger('aleph_connector')->notice($error_msg);
      return null;
    } else {
      return $output;
    }
  }

  private function getCircStatus($sys_no) {
    $base = $this->config->getBase();
    $bib_library = $this->config->getBibLibrary();
    $curl = curl_init();
    $url_options = '?op=circ-status&library=' . $bib_library . '&translate=N&sys_no=' . $sys_no;
    curl_setopt($curl, CURLOPT_URL, $base . $url_options);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    $has_error = $this->processCurlError($curl);
    curl_close($curl);
    if ($has_error) {
      $error_msg = 'Failed to get circ-status at: ' . $base . $url_options;
      \Drupal::logger('aleph_connector')->notice($error_msg);
      return null;
    } else {
      return $output;
    }
  }

  public function readValidItems($barcodes) {
    $items = [];
    $invalid_items = [];
    foreach($barcodes as $barcode) {  
      $output = $this->readItem($barcode);
      if ($output == null) {
        continue;
      }
      $item_map = $this->getZ30Vals($output);
      if ($item_map != null) {
        if (!$this->isValidLocation($item_map)) {
          array_push($invalid_items, $item_map);
          continue;
        }
        array_push($items, $item_map);
      }
    }
    if (count($items) == 0 && count($invalid_items) > 0) {
      \Drupal::logger('aleph_connector')->notice("No items found in desired location! Items: " . var_export($invalid_items, true));
    }
    return $items;
  }

  public function getCircItems($sys_no) {
    $items = [];
    $output = $this->getCircStatus($sys_no);
    if ($output != null) {
      $items = $this->getItems($output);
    }
    return $items;
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['readValidItems', 'getCircItems'];
  }
}

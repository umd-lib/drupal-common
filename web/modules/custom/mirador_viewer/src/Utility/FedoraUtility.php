<?php

namespace Drupal\mirador_viewer\Utility;

use Symfony\Component\Serializer\Exception\UnexpectedValueException;

class FedoraUtility {

  public $config;

  public function __construct() {
    $this->config = \Drupal::config('mirador_viewer.adminsettings');
  }

  public function getIIIFServer() {
    $val = $this->config->get('iiif_server');
    if (empty($val)) {
      throw new UnexpectedValueException('IIIF Server must be set.');
    }
    return $val;
  }

  public function getIIIFViewer() {
    $val = $this->config->get('iiif_viewer');
    if (empty($val)) {
      throw new UnexpectedValueException('IIIF viewer path must be set.');
    }
    return $val;
  }

  public function getIIIFRestrictedViewer() {
    $val = $this->config->get('iiif_viewer_restricted');
    if (empty($val)) {
      return NULL;
    }
    return $val;
  }

  public function getRestrictedCollections() {
    $val = $this->config->get('restricted_collections');
    if (empty($val)) {
      return [];
    }
    return $val;
  }


  public function getIIIFError() {
    $val = $this->config->get('error_message');
    if (empty($val)) {
      return NULL;
    }
    return $val;
  }

  public function getFcrepoServer() {
    $val = $this->config->get('fcrepo_server');
    if (empty($val)) {
      throw new UnexpectedValueException('Fcrepo Server must be set.');
    }
    return $val;
  }

  public function getCollectionPrefix() {
    $val = $this->config->get('fcrepo_prefix');
    if (empty($val)) {
      throw new UnexpectedValueException('Prefix must be set.');
    }
    return $val;
  }

  /**
   * Take a SearchAPI Solr ID and return item hash
   */
  public function getFedoraItemHash($id) {
    $parts = explode('/', $id);
    // TODO: test the hash before returning
    return count($parts) > 4 ? end($parts) : NULL;
  }

  /**
   * Returns a pair tree prefixed hash id.
   *
   * @param hash
   *          a fedora object hash id (example:
   *          6bdc4c4e-f937-4c37-92cc-daf42a5cd4c5)
   * @return a pair tree prefixed hash id (example:
   *         6b/dc/4c/4e/6bdc4c4e-f937-4c37-92cc-daf42a5cd4c5)
   */
  public function addPairTreePrefix($hash) {
    $hash_split = explode('-', $hash);
    if (count($hash_split) < 2) {
      throw new UnexpectedValueException('Invalid hash.');
    }
    $tree_length = 2;
    $prefix_split = str_split($hash_split[0], $tree_length);
    return implode("/", $prefix_split) . '/' . $hash;
  }

  /*
   * Generate new Document ID if the document belongs to Fedora Database
   *
   * @param String
   *
   * @return String
   */
  public function generateFedoraDatabaseDocumentID($id) {
    parse_str($id, $url_array);
    $full_uri = \Drupal::request()->getRequestUri();

    $fc_base = $this->getFcrepoServer();
    if (!empty($url_array['relpath'])) {
      $id = array_key_first($url_array);
      $pcdm_prefix = str_replace(':', '/', $url_array['relpath']);
    } elseif (!empty($full_uri) && str_contains($full_uri, 'relpath=')) {
      parse_str($full_uri, $uri_array);
      $pcdm_prefix = null;
      foreach ($uri_array as $key => $value) {
        if (str_contains($key, 'relpath')) {
          $pcdm_prefix = $value;
        }
      }
      if ($pcdm_prefix == null) {
        $pcdm_prefix = $this->getCollectionPrefix();
      }
      $pcdm_prefix = str_replace(':', '/', $pcdm_prefix);
    } else {
      $pcdm_prefix = $this->getCollectionPrefix();
    }
    $pcdm_path = trim($pcdm_prefix, '/') . '/' . $this->addPairTreePrefix($id);
    return $fc_base . $pcdm_path;
  }
}


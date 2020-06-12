<?php

namespace Drupal\mirador_viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;

class DisplayMiradorController extends ControllerBase implements TrustedCallbackInterface {

  private $config;

  public function __construct() {
    $this->config = \Drupal::config('mirador_viewer.adminsettings');
  }

  public function viewObject($object_id) {
    $object_title = $this->querySolr($object_id);
    return [
      '#theme' => 'mirador_viewer',
      '#title' => $object_title ? $object_title : $this->t('Object Display'),
      '#iiif_server' => $this->config->get('iiif_server'),
      '#iiif_viewer' => $this->config->get('iiif_viewer'),
      '#error_message' => $this->config->get('error_message'),
      '#object_id' => $object_id,
    ];
  }

  /**
   * Returns a pair tree prefixed hash id.
   *
   * @param hashId
   *          a fedora object hash id (example:
   *          6bdc4c4e-f937-4c37-92cc-daf42a5cd4c5)
   * @return prefixedHashId a pair tree prefixed hash id (example:
   *         6b/dc/4c/4e/6bdc4c4e-f937-4c37-92cc-daf42a5cd4c5)
   */
  public function addPairTreePrefix($hash) {
    $hash_split = explode('-', $hash);
    $tree_length = 2;
    $prefix_split = str_split($hash_split[0], $tree_length);
    return implode("/", $prefix_split) . '/' . $hash;
  }

  /*
   * Generate new Document ID if the document belongs to Fedora Database
   *
   * @param HstRequestContext
   *
   * @param Properties
   *
   * @param String
   *
   * @return String
   */
  public function generateFedoraDatabaseDocumentID($id) {
    $fc_base = $this->config->get('fcrepo_server');
    $pcdm_prefix = "pcdm/";

    if ($fc_base == NULL) {
      // throw exception
    }

    $pcdm_path = $pcdm_prefix . $this->addPairTreePrefix($id);    

    return $fc_base . $pcdm_path;
  }

  /**
   * Search by ID
   */
  public function querySolr($id) {
    $index = \Drupal\search_api\Entity\Index::load($this->config->get('mirador_index'));
    $query = $index->query();
    $query->setSearchId($id);
    $query->addCondition('id', $this->generateFedoraDatabaseDocumentID($id));
    $results =  $query->execute();
    $items = $results->getResultItems();

    $item = reset($items);

    if (!empty($item)) {
      if ($object_title = $item->getField('display_title')->getValues()[0]) {
        return $object_title; 
      }
    }
  }


  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['generateFedoraDatabaseDocumentID', 'addPairTreePrefix', 'viewObject'];
  }

}

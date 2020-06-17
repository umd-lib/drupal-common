<?php

namespace Drupal\mirador_viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\mirador_viewer\Utility\FedoraUtility;

class DisplayMiradorController extends ControllerBase implements TrustedCallbackInterface {

  private $fc;

  public function __construct() {
    $this->fc = new FedoraUtility();
  }

  public function viewObject($object_id, $derive = true) {
    $object_id = $derive ? $this->fc->getFedoraItemHash($object_id) : $object_id;
    if (empty($object_id)) {
      return NULL;
    } 
    return [
      '#theme' => 'mirador_viewer',
      '#iiif_server' => $this->fc->getIIIFServer(),
      '#iiif_viewer' => $this->fc->getIIIFViewer(),
      '#error_message' => $this->fc->getIIIFError(),
      '#object_id' => $object_id,
    ];
  }

  /**
   * Reference method for querying Solr by ID
   */
  public function querySolr($id) {
    $index = \Drupal\search_api\Entity\Index::load($this->config->get('mirador_index'));
    $query = $index->query();
    $query->setSearchId($id);
    $query->addCondition('id', $this->fc->generateFedoraDatabaseDocumentID($id));
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
    return ['querySolr', 'viewObject'];
  }

}

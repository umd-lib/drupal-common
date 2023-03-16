<?php

namespace Drupal\mirador_viewer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\mirador_viewer\Utility\FedoraUtility;
use Drupal\search_api\Entity\Index;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;

class DisplayMiradorController extends ControllerBase implements TrustedCallbackInterface {

  private $fc;

  public function __construct() {
    $this->fc = new FedoraUtility();
  }

  public function viewMiradorObject($object_id, $collection_id, $derive = true) {
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
      '#collection_id' => $collection_id,
    ];
  }

  /**
   * Theme a static html object for views.
   * Note: This really only works for whpool.
   *
   * @return renderable object 
   */
  public function viewStaticObject($content) {
    if (count($content) < 1) {
       return NULL;
    }

    if (empty($content['body'])) {
      $body = '<p>Content not currently available. Try again later.</p>';
    } else {
      $body = $content['body'];
    }
    $renderable =  [
      '#theme' => 'static_viewer',
      '#body' => $body,
      '#attachments' => !empty($content['attachments']) ? $content['attachments'] : array(),
      '#images' => !empty($content['images']) ? $content['images'] : array(),
    ];
    return \Drupal::service('renderer')->render($renderable);
  }

  /**
   * Reference method for querying Solr by ID
   * @note: Unused
   */
  public function querySolr($id) {
    $index = \Drupal\search_api\Entity\Index::load($this->config->get('mirador_index'));
    $query = $index->query();
    $query->setSearchId($id);
    $full_id = $this->fc->generateFedoraDatabaseDocumentID($id);
    $query->addCondition('id', $full_id);
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
   * Solarium query for FCRepo html_file.
   *
   * @return HTML
   */
  public function getPcdmMembers($id) {
    $index = \Drupal\search_api\Entity\Index::load($this->fc->config->get('mirador_index'));
    $backend = $index->getServerInstance()->getBackend();

    // This effectively bypasses search_api and goes to Solarium, which is more permissive
    //   in what you can do but lacks CMS integrations.
    $connector = $backend->getSolrConnector();
    $query = $connector->getSelectQuery();
    $query->setFields(array('*','members:[subquery]'));
    $query->addParam('members.files.q', '{!terms f=pcdm_file_of v=$row.id}');
    $query->addParam('members.files.fl', 'id,mime_type');
    $query->addParam('members.fl', 'id,title,files:[subquery]');
    $query->addParam('members.q', '{!terms f=pcdm_member_of v=$row.id}');
    $query->createFilterQuery('id')->setQuery('id:' . str_replace(':', '\:', $id));

    $results = $connector->execute($query);

    if ($results->count() > 0) {
      // Copy to different variable to avoid pass-by-ref notice.
      $docs = $results->getDocuments(); 
      $doc = reset($docs);
      foreach ($doc as $field => $value) {
         if ($field == 'members' && !empty($value['docs'])) {
           $members = $value['docs'];
         }
      }
    }

    // @note Picking apart an array like this is not advised.
    // @todo Evaluate Solarium closer and find better methods.
    $content = array();
    $image_mimes = array('image/jpeg', 'image/png', 'image/apng', 'image/jpg');
    if (!empty($members) && count($members) > 0) {
      foreach ($members as $member) {
        if (!empty($member['title'][0])) {
          $title = $member['title'][0];
        }
        if (!empty($member['files'][0]['id'])) {
          $id = $member['files'][0]['id'];
        }
        if (!empty($member['files'][0]['mime_type'])) {
          $mime = $member['files'][0]['mime_type'];
        }

        if (!empty($title) && !empty($id) && !empty($mime)) {
          if ($title == 'Body') {
            $content['body'] = $this->getFedoraHtml($id); // getfedorahtml
          } elseif (in_array($mime, $image_mimes)) {
            $content['images'][] = $id;
          } else {
            $content['attachments'][] = $id;
          }
        }
        $title = null; $id = null; $mime = null;
      }
    }

    return $content;    
  }

  /**
   * Solarium query for FCRepo html_file.
   * @note: Unused
   *
   * @return HTML
   */
  public function getCustomRequest($id) {
    $index = \Drupal\search_api\Entity\Index::load($this->fc->config->get('mirador_index'));
    $backend = $index->getServerInstance()->getBackend();
    $connector = $backend->getSolrConnector();
    $query = $connector->getSelectQuery();
    $query->setFields(array('*','html_file:[subquery]'));
    $query->addParam('html_file.fl', 'id');
    $query->addParam('html_file.fq', 'mime_type:text/html');
    $query->addParam('html_file.q', '{!term f=pcdm_file_of v=$row.extracted_text_source}');
    $query->createFilterQuery('extracted_text_source')->setQuery('extracted_text_source:' . str_replace(':', '\:', $id));
    $query->createFilterQuery('rdf_type')->setQuery('rdf_type:oa\:Annotation');

    $results = $connector->execute($query);

    if ($results->count() > 0) {
      $doc = reset($results->getDocuments());
      foreach ($doc as $field => $value) {
         if ($field == 'display_title' && $value == "Body") {
         }
         if ($field == 'html_file' && !empty($value['docs'][0]['id'])) {
           $html_ref = $value['docs'][0]['id'];
           break;
         }
      }
    }

    $data = null;
    $count = array();
    if (!empty($html_ref)) {
      $content['data'] = $this->getFedoraHtml($html_ref);
      $content['is_body'] = 'true';
    } else {
      $content['data'] = $html_ref;
      $content['is_body'] = 'false';
    }
    return $content;
  }

  /**
   * Retrieve HTML from Fedora server
   *
   * @return HTML
   */
  function getFedoraHtml($uri) {
    // Defined in the Mirador settings form.
    // @todo Make naming more generic and less Mirador.
    $token = $this->fc->config->get('fcrepo_token');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $uri);
    $authorization = "Authorization: Bearer ". $token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: text/html' , $authorization ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }


  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['querySolr', 'viewMiradorObject', 'viewStaticObject'];
  }

}

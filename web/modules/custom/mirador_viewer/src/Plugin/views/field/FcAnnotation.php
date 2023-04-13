<?php
 
/**
 * @file
 * Definition of Drupal\mirador_viewer\Plugin\views\field\FcAnnotation
 */
 
namespace Drupal\mirador_viewer\Plugin\views\field;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\NodeType;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\mirador_viewer\Controller\DisplayMiradorController;
use Drupal\mirador_viewer\Utility\FedoraUtility;
use Drupal\search_api\Entity\Index;
use Solarium\QueryType\Select\Query\Query;
use Drupal\search_api_solr\Plugin\search_api\backend\SearchApiSolrBackend;
 
/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("fc_annotation")
 */
class FcAnnotation extends FieldPluginBase {
 
  /**
   * @{inheritdoc}
   */
  public function query() {
    // Leave empty to avoid a query on this field.
  }
 
  /**
   * Define the available options
   * @return array
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['id_field'] = array('default' => 'id');
 
    return $options;
  }
 
  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
  }
 
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $full_uri = \Drupal::request()->getRequestUri();
    dsm($full_uri);
    $query_array = explode('?', $full_uri);
    $render = null;
    if (!empty($query_array[1])) {
      parse_str($query_array[1], $url_array);
      if (!empty($url_array['query'])) { 
        $entity = $values->_item;
        $id = str_replace('solr_document/', '', $entity->getId());
        $render = $this->getAnnotations($id, $url_array['query']);
      }
    }
    if (!empty($render)) {
      return $render;
    }
  }

  /**
   * Get annotations from query and ID
   */
  protected function getAnnotations($docID, $q) {
    $fc = new FedoraUtility();
    $id_str = 'extracted_text_source:(' . str_replace(':', '\\:', $docID) . ')';
    $index = \Drupal\search_api\Entity\Index::load($fc->config->get('mirador_index'));
    $backend = $index->getServerInstance()->getBackend();

    // This effectively bypasses search_api and goes to Solarium, which is more permissive
    //   in what you can do but lacks CMS integrations.
    $connector = $backend->getSolrConnector();
    $query = $connector->getSelectQuery();
    $query->setFields(array('id', 'extracted_text', 'extracted_text_source'));
    $query->addParam('rows', '100');
    $query->addParam('hl', 'true');
    $query->addParam('hl.fragsize', '500');
    $query->addParam('hl.fl', 'extracted_text');
    $query->addParam('hl.simple.pre', '<b>');
    $query->addParam('hl.simple.post', '</b>');
    $query->createFilterQuery('rdf_type')->setQuery('rdf_type:oa\:Annotation');
    $query->createFilterQuery('source')->setQuery($id_str);
    $query->setQuery($q);

    $results = $connector->execute($query);

    $output = null;
    if ($results->count() > 0) {
      // Copy to different variable to avoid pass-by-ref notice.
      $docs = $results->getDocuments();
      $doc = reset($docs);
      foreach ($doc as $field => $value) {
         if ($field == 'members' && !empty($value['docs'])) {
           $members = $value['docs'];
           $output .= 'ding';
         }
      }
    }
    return $output;
  }
}

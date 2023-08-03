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
    $query->createFilterQuery('rdf_type')->setQuery('rdf_type:oa\:Annotation');
    $query->createFilterQuery('source')->setQuery($id_str);
    if (!preg_match('/^(["\']).*\1$/m', $q)) {
      $replace_me = [ '[', ']', '{', '}', '*', '+', '(', ')', ':', '%' ];
      $q = str_replace($replace_me, '', $q);
    }
    $full_query = "(text:(($q))^2 text_ja:(($q))^1 text_ja_latn:(($q))^1)";
    $query->setQuery($full_query);
    $hl = $query->getHighlighting();
    $hl->setFields('extracted_text');
    $hl->setSimplePrefix('<strong>');
    $hl->setSimplePostfix('</strong>');
    $hl->setFragSize('500');

    $results = $connector->execute($query);
    $highlighting = $results->getHighlighting();

    $output = null;
    $extracts = [];
    $pattern = '/\|\d+,\d+,\d+,\d+/i';

    if ($highlighting->count() > 0) {
      foreach ($highlighting as $highDoc) {
        foreach ($highDoc as $f => $v) {
          if ($f == 'extracted_text') {
            foreach ($v as $highlight) {
              $extracts[] = preg_replace($pattern, '', $highlight);
            }
          }
        }
      }
    }
    return count($extracts) > 0 ? implode("...", $extracts) : null;
  }
}

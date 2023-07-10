<?php

namespace Drupal\mirador_viewer\EventSubscriber;

use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters the query where necessary to implement business logic.
 *
 * @package Drupal\mirador_viewer\EventSubscriber
 */
class SolrQueryAlterEventSubscriber implements EventSubscriberInterface {
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SearchApiSolrEvents::POST_CONVERT_QUERY => 'postConvQuery',
      SearchApiSolrEvents::POST_EXTRACT_RESULTS => 'postExtractResults',
      SearchApiSolrEvents::PRE_QUERY => 'preQuery',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function postExtractResults(PostExtractResultsEvent $event): void {
    // $results = $event->getSolariumResult();
    $results = $event->getSearchApiResultSet();
    if ($results->getResultCount() != 1) {
      return;
    }
    $result_items = $results->getResultItems();
    $new_files_array = [];
    foreach ($result_items as $key => $item) {
      $extra = $item->getExtraData('search_api_solr_document');
      if (!empty($extra)) {
        $files = $extra->__get('files');
        if (!empty($files['docs'])) {
          foreach ($files['docs'] as $f_doc) {
            if (!empty($f_doc['mime_type']) && $f_doc['mime_type'] == 'application/pdf') {
              $new_files_array[] = $f_doc['id'];
            }
          }
        }
      }
      $pcdm_files = $item->getField('pcdm_files');
      if (!empty($pcdm_files)) {
         $pcdm_files->setValues($new_files_array);
      }
    }
    $results->setResultItems($result_items);
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $search_query = $event->getSearchApiQuery();
    $search_index = $search_query->getIndex();
    if ($search_index) {
      $index_id = $search_index->id();
      if ($index_id != 'fcrepo') {
        return;
      }
    }
    $query = $event->getSolariumQuery();
    $query->addField('annotation_source_type:[subquery]');
    $query->addField('files:[subquery]');
    $query->addParam('files.q', '{!terms f=id v=$row.pcdm_files}');
    $query->addParam('files.fl', 'id,title,filename,mime_type');
    $query->addParam('files.fq', 'mime_type:application/pdf');
  }


  /**
   * {@inheritdoc}
   */
  public function postConvQuery(PostConvertedQueryEvent $event): void {
    $query = $event->getSearchApiQuery();
    $search_index = $query->getIndex();
    if ($search_index) {
      $index_id = $search_index->id();
      if ($index_id != 'fcrepo') {
        return;
      }
    }
    $solarium_query = $event->getSolariumQuery();
    $raw_query = $solarium_query->getQuery();
    $query_str = "{!type=graph from=id to=extracted_text_source maxDepth=1 q.op=AND} ";

    if (!empty($raw_query)) {
      $query_str .= $raw_query;
    }

    $solarium_query->setQuery($query_str);
  }
}

<?php

namespace Drupal\mirador_viewer\EventSubscriber;

use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
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
    $results = $event->getSolariumResult();
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $query = $event->getSolariumQuery();
    $query->addField('annotation_source_type:[subquery]');
  }


  /**
   * {@inheritdoc}
   */
  public function postConvQuery(PostConvertedQueryEvent $event): void {
    $query = $event->getSearchApiQuery();
    $solarium_query = $event->getSolariumQuery();
    $raw_query = $solarium_query->getQuery();
    $query_str = "{!type=graph from=id to=extracted_text_source maxDepth=1 q.op=AND} ";

    if (!empty($raw_query)) {
      $query_str .= $raw_query;
    }

    dsm($query_str);
    $solarium_query->setQuery($query_str);
    // dsm($solarium_query);
  }
}

<?php

namespace Drupal\facet_overrides\EventSubscriber;

use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Alters the query where necessary to implement business logic.
 *
 * @package Drupal\facet_overrides\EventSubscriber
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
      SearchApiEvents::QUERY_PRE_EXECUTE => 'queryAlter',
    ];
  }

  /**
   * Reacts to the query alter event.
   *
   * @param \Drupal\search_api\Event\QueryPreExecuteEvent $event
   *   The query alter event.
   */
  public function queryAlter(QueryPreExecuteEvent $event) {
    $query = $event->getQuery();

    if ($query->getIndex()->getServerInstance()->supportsFeature('search_api_facets')) {
      $facet_source = 'search_api:' . str_replace(':', '__', $query->getSearchId());
      dsm($query);
    }
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

    dsm($solarium_query);

    // $solarium_query->setQuery($query_str);
  }
}

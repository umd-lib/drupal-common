<?php

namespace Drupal\mirador_viewer\EventSubscriber;

use Drupal\search_api_solr\Event\PreQueryEvent;
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
      SearchApiSolrEvents::PRE_QUERY => 'preQuery',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preQuery(PreQueryEvent $event): void {
    $query = $event->getSearchApiQuery();
    $solarium_query = $event->getSolariumQuery();
    $keys = $query->getKeys();

    $query_str = "{!type=graph from=id to=extracted_text_source maxDepth=1 q.op=AND} ";
    if (!empty($keys)) {
      if (!empty($keys['#conjunction'])) {
        $conjunction = $keys['#conjunction'];
dsm($conjunction);
        // unset($keys['#conjunction']);
        $query_str = $query_str . implode(" " . $conjunction . " ", $keys);
dsm($query_str);
      }
    }
//     $solarium_query->setQuery($query_str);
// dsm($solarium_query);
  }
}

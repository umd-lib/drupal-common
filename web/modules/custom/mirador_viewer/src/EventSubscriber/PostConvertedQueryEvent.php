<?php

/**
 * @file
 * Contains Drupal\mirador_viewer\EventSubscriber\PostConvertedQueryEvent.
 */

namespace Drupal\mirador_viewer\EventSubscriber;

use symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSetInterface;

/**
 * Search Alter subscriber class.
 * This is to inject additional query args into Solarium query.
 * @see https://solarium.readthedocs.io/en/stable/customizing-solarium/#plugin-system
 * @see https://git.drupalcode.org/project/search_api_solr/-/blob/4.x/src/Event/SearchApiSolrEvents.php
 */

class PostConvertedQueryEvent implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getsubscribedEvents() {
    return [
      'SearchApiSolrEvents::POST_CONVERT_QUERY' => 'alterSolrQuery'
    ];
  }
   
  /**
   * Our callback function processQuery.
   */
  public function alterSolrQuery(PostConvertedQueryEvent $event) {
    // This needs to check that it only applies to particular indexes so as not to affect
    // other searches that use this module.
    $build = $event->getBuild();
    $solarium_query = $event->getSolariumQuery();
  }
}


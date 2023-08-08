<?php

namespace Drupal\facet_overrides\EventSubscriber;

use Drupal\search_api_solr\Event\PostConvertedQueryEvent;
use Drupal\search_api_solr\Event\PreQueryEvent;
use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api_solr\Event\PostExtractResultsEvent;
use Drupal\search_api_solr\Event\SearchApiSolrEvents;
use Drupal\search_api\Event\SearchApiEvents;
use Drupal\search_api_solr\Event\PostExtractFacetsEvent;
use Drupal\search_api\Query\QueryInterface as SapiQueryInterface;
use Solarium\Core\Query\QueryInterface as SolariumQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Solarium\QueryType\Select\Query\FilterQuery;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Alters the query where necessary to implement business logic.
 *
 * @package Drupal\facet_overrides\EventSubscriber
 */
class SolrQueryAlterEventSubscriber implements EventSubscriberInterface {

  const SETTINGS = 'facet_overrides.settings';

  /**
   * A configuration object containing samlauth settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Construct a new UserGroupsSyncEventSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory->get(static::SETTINGS);
  }

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

    if (!$facet_overrides = $this->config->get('facet_overrides')) {
      // Nothing to override. Just return.
      return;
    }

    $current_path = \Drupal::service('path.current')->getPath();
    $current_uri = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);

    if (empty($facet_overrides[$current_uri])) {
      return;
    }

    $solarium_query = $event->getSolariumQuery();
    $special_fq = new FilterQuery();
    $special_fq->setKey('special_fq');
    $special_fq->setQuery($facet_overrides[$current_uri]);
    $solarium_query->addFilterQuery($special_fq);
    $fq = $solarium_query->getFilterQueries();
  }
}

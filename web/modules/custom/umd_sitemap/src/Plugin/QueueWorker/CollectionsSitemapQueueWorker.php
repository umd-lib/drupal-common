<?php

namespace Drupal\umd_sitemap\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\Entity\File;
use Drupal\search_api\Entity\Index;
use Drupal\mirador_viewer\Utility\FedoraUtility;

/**
 * @QueueWorker(
 *   id = "collections_sitemap_worker",
 *   title = @Translation("Collections Sitemap Worker"),
 *   cron = {"time" = 60}
 * )
 */
class CollectionsSitemapQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritDoc}
   */
  public function processItem($data) {
    $required_fields = ['sitemap', 'filter'];
    foreach ($required_fields as $required) {
      if (empty($data[$required])) {
        \Drupal::logger('umd_sitemap')->notice('sitemap and filter fields are required');
        return;
      }
    }
    $sitemap = trim($data['sitemap']);
    $filter = trim($data['filter']);
    $index = \Drupal\search_api\Entity\Index::load('fcrepo');
    if (empty($index)) {
      \Drupal::logger('umd_sitemap')->notice('Index not available');
      return;
    }
    
    $start = 0;
    $end = 500;
    $cnt = 500;
    $results_count = 501;
    
    $urls = [];

    while ($end < $results_count) {
      \Drupal::logger('umd_sitemap')->notice($start . ' ' . $end . ' ' . $results_count);
      $query = $index->query();
      $query->addCondition('is_discoverable', TRUE);
      $query->addCondition('presentation_set_label', $filter);
      $query->range($start, $end);
      $query->sort('id');
      $results = $query->execute();

      $results_count = $results->getResultCount();
      if (empty($results) || $results_count == 0) {
        \Drupal::logger('umd_sitemap')->notice('No results for ' . $filter);
        return;
      }

      $this->fc = new FedoraUtility();

      foreach ($results as $result) {
        $id = $result->getId();
        if (!empty($id)) {
          $id = str_replace('solr_document/', '', $id);
          $collection = $result->getField('collection')->getValues()[0];
          \Drupal::logger('umd_sitemap')->notice($collection);

          $collection_raw = explode("/rest/", $collection);

          $collection_prefix = "pcdm";
          if ($collection_raw[1]) {
            $collection_check = explode("/", $collection_raw[1]);
            if ($collection_check[0] == "dc") {
              $collection_prefix = str_replace("//", "::", end($collection_raw));
            }
          }

          $short_id = $this->fc->getFedoraItemHash($id);
          if (!empty($short_id)) {
            \Drupal::logger('umd_sitemap')->notice($short_id);
            $processed_url = '/result/id/' . $short_id . '?relpath=' . $collection_prefix;
            $urls[] = $processed_url;
          }
        }
      }

      $start = $end;
      $end = $end + $cnt;
      \Drupal::logger('umd_sitemap')->notice($start . ' ' . $end . ' ' . $results_count);
    }

    if (count($urls) > 0) {
      $data = implode(PHP_EOL, $urls);
    }
    $filename = 'public://' . $sitemap . '.txt';
    $file_repo = \Drupal::service('file.repository');
    $file_repo->writeData($data, $filename, FileSystemInterface::EXISTS_REPLACE);
    \Drupal::logger('umd_sitemap')->notice('processing umd sitemap queue completed for ' . $sitemap);
  }
}

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
        return;
      }
    }
    $index = \Drupal\search_api\Entity\Index::load('fcrepo');
    if (empty($index)) {
      return;
    }
    $query = $index->query();
    $query->addCondition('is_discoverable', 1, '=');
    $query->addCondition('presentation_set_label', $data['filter'], '=');
    $query->range(1, 1000);
    $results = $query->execute();

    if (empty($results) || count($results) == 0) {
      return;
    }

    $urls = [];
    $this->fc = new FedoraUtility();

    foreach ($results as $result) {
      $id = $result->getId();
      \Drupal::logger('umd_sitemap')->notice($id);
      if (!empty($id)) {
        $id = str_replace('solr_document/', '', $id);

        $short_id = $this->fc->getFedoraItemHash($id);
        if (!empty($short_id)) {
          $urls[] = $short_id;
        }
      }
    }

    $data = json_encode($urls);
    $filename = 'public://collection-sitemaps/' . $data['sitemap'] . '.txt';
    $file_repo = \Drupal::service('file.repository');
    $file_repo->writeData($data, $filename, FileSystemInterface::EXISTS_REPLACE);
  }

}

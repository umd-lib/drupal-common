<?php

namespace Drupal\umd_examples\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Example controller class for generating page content from admin form data.
 *
 * @see
 *   https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Security%21TrustedCallbackInterface.php/interface/TrustedCallbackInterface/8.8.x
 */
class ExamplesController extends ControllerBase implements TrustedCallbackInterface {

  protected $config;

  public function __construct() {
    $this->config = \Drupal::config('umd_examples.settings');
  }

  public function samplePage() {
    $this->solrQuery($this->config->get('example_radios'));
    return [
      '#theme' => 'umd_example_template',
      '#example_text' => $this->config->get('example_text'),
      '#example_description' => $this->config->get('example_description'),
      '#example_date' => $this->config->get('example_date'),
      '#example_radios' => $this->config->get('example_radios'),
      '#example_color' => $this->config->get('example_color'),
      '#example_hidden' => $this->config->get('example_hidden'),
      '#example_checkboxes' => $this->config->get('example_checkboxes'),
      '#example_entities' => $this->entityQuery($this->config->get('example_radios')),
      '#example_documents' => $this->solrQuery($this->config->get('example_radios')),
    ];
  }

  /**
   * Return count of entities of a given type.
   *
   * @param
   *   type - Entity Type (e.g., node, user, term)
   * @return
   *   count - results count (int)
   * @see
   *   https://api.drupal.org/api/drupal/core!lib!Drupal.php/function/Drupal%3A%3AentityQuery/8.2.x
   */
  private function entityQuery($type) {
    $query = \Drupal::entityQuery($type)
      ->condition('status', 1);

    return $query->count()->execute();
  }

  /**
   * Return count of solr documents of a given type.
   *
   * @param
   *   type - Entity Type
   * @return
   *   count - results count (int)
   * @see
   *   https://www.drupal.org/docs/8/modules/search-api/developer-documentation/executing-a-search-in-code
   */
  private function solrQuery($type) {
    $index = \Drupal\search_api\Entity\Index::load('drupal');
    $query = $index->query();
    $query->addCondition('search_api_datasource', 'entity:' . $type);
    return $query->execute()->getResultCount();

    /**
     * Alternately process results though this should generally be handled
     * using Drupal Views.
     *
     * $results =  $query->execute();
     * $items = $results->getResultItems();
     * $item = reset($items);
     * if (!empty($item)) {
     *  if ($object_title = $item->getField('display_title')->getValues()[0]) {
     *    return $object_title;
     *  }
     * }
     */
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['samplePage'];
  }
}

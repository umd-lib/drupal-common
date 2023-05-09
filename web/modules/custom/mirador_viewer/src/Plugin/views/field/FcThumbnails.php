<?php
 
/**
 * @file
 * Definition of Drupal\mirador_viewer\Plugin\views\field\FcThumbnails
 */
 
namespace Drupal\mirador_viewer\Plugin\views\field;
 
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\search_api\Plugin\views\query\SearchApiQuery;
use Drupal\search_api\SearchApiException;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\mirador_viewer\Utility\FedoraUtility;
 
/**
 * Field handler to flag the node type.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("fcrepo_thumbnails")
 */
class FcThumbnails extends FieldPluginBase {

  protected $fc;
 
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
    $options['thumbnail_dimensions'] = ['default' => '150,250'];
 
    return $options;
  }
 
  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['thumbnail_dimensions'] = array(
      '#title' => $this->t('Thumbnail Dimensions'),
      '#type' => 'textfield',
      '#description' => t('Use format WWW,HHH. For example, 250,350 equals 250w x 350h.'),
    );
 
    parent::buildOptionsForm($form, $form_state);
  }
 
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $this->fc = new FedoraUtility();
    $iiif = $this->fc->getIIIFServer();
    $entity = $values->_item;
    $id = $entity->getId();
    if (empty($id)) {
      return;
    }
    $dimensions = !empty($this->options['thumbnail_dimensions']) ? $this->options['thumbnail_dimensions'] : '75,150';
    $issue_field = $entity->getField('containing_issue');
    if (!empty($issue_field)) {
      $issue_id = reset($issue_field->getValues());
    }
    $component_field = $entity->getField('component_not_tokenized');
    if (!empty($component_field)) {
      $component = reset($component_field->getValues());
    }
    $page_field = $entity->getField('page_number');
    $page_no = null;
    if (!empty($page_field)) {
      $page_no = reset($page_field->getValues());
    }
    $collection_field = $entity->getField('collection');
    if (!empty($collection_field)) {
      $collection = $this->getCollectionId(reset($collection_field->getValues()));
    } else {
      $collection = "pcdm";
    }
    if ($component == "Article" && !empty($issue_id)) {
      $short_id = $this->fc->getFedoraItemHash($issue_id);
    } else {
      $short_id = $this->fc->getFedoraItemHash($id);
    }
    $pcdm_link = $iiif . "manifests/fcrepo:" . $collection . "::" . $short_id . "/manifest";
    if (!empty($pcdm_link)) {
      return $this->getThumbnailUrl($pcdm_link, $page_no, $dimensions);
    }
  }

  protected function getThumbnailUrl($pcdm_link, $page_no, $dimensions) {
    $json = file_get_contents($pcdm_link);
    $obj = json_decode($json, true);
    if (!empty($obj['sequences'])) {
      $sequence = reset($obj['sequences']);
      $page_number = is_numeric($page_no) && $page_no - 1 > 0 ? $page_no - 1 : (int) 0;
      if (!empty($sequence['canvases'][$page_number]['thumbnail']['@id'])) {
        return $this->injectThumbnailDimensions($sequence['canvases'][$page_number]['thumbnail']['@id'], $dimensions);
      }
    }
    return null;
  }

  protected function injectThumbnailDimensions($link_id, $dimensions) {
    $link_arr = explode('/', $link_id);
    $total = count($link_arr);
    $dim_index = $total - 3;
    $link_arr[$dim_index] = $dimensions;
    return implode('/', $link_arr);
  }

  protected function getCollectionId($collection_id) {
    if (empty($collection_id)) {
      return null;
    }
    $collection_array = explode("/rest/", $collection_id);
    if (!empty($collection_array[1])) {
      $pcdm_check = explode("/", $collection_array[1]);
      if ($pcdm_check[0] == "dc") {
        return str_replace("/", ":", $collection_array[1]);
      }
      return "pcdm";
    }
  }
}

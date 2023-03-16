<?php
 
/**
 * @file
 * Definition of Drupal\mirador_viewer\Plugin\views\field\LDParser
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
 * @ViewsField("json_ld_parser")
 */
class LDParser extends FieldPluginBase {

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
    $options['id_field'] = array('default' => 'id');
 
    return $options;
  }
 
  /**
   * Provide the options form.
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['query_key'] = array(
      '#title' => $this->t('JSON-LD Query Key'),
      '#description' => $this->t('Return this value from the JSON-LD (if exists)'),
      '#type' => 'textfield',
    );
 
    parent::buildOptionsForm($form, $form_state);
  }
 
  /**
   * @{inheritdoc}
   */
  public function render(ResultRow $values) {
    $this->fc = new FedoraUtility();
    $entity = $values->_item;
    $id = $entity->getId();
    $short_id = $this->fc->getFedoraItemHash($id);
    if (!empty($short_id)) {
      return $short_id;
    }
  }

  public function get_json_ld($uri) {
    $content = file_get_contents($uri);

    // retrieve the JSON data
    $dom = new DomDocument();
    @$dom->loadHTML($content);

    // parse for json+ld
    $xpath = new domxpath($dom);
    $jsonScripts = $xpath->query( '//script[@type="application/ld+json"]' );
    $json = trim( $jsonScripts->item(0)->nodeValue );
    $data = json_decode( $json, true );

    // you can now use this array to query the data you want
    $duration = $data['duration'];
    echo "Duration: $duration" ;

    $director = $data['director']['name'] ;
    echo "Director : $director" ;
  }
}

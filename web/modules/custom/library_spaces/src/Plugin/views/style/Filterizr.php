<?php

namespace Drupal\filterizr_display\Plugin\views\style;

use Drupal\core\form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;

/**
 * Applies Filterizr to a Views resultset.
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "filterizr_display",
 *   title = @Translation("Filterizr"),
 *   help = @Translation("Applies Filterizr JS to a resultset."),
 *   theme = "filterizr_display",
 *   display_types = { "normal" }
 * )
 */
class Filterizr extends StylePluginBase {
  protected $usesOptions = true;
  protected $usesRowPlugin = true;

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['path'] = array('default' => 'filterizr');
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $options = $this->getVocabularies();
    $form['taxonomy_vocab'] = array(
      '#type' => 'select',
      '#title' => t('OPTION 1: Filter Vocab'),
      '#default_value' => (isset($this->options['taxonomy_vocab'])) ? $this->options['taxonomy_vocab'] : NULL,
      '#description' => t('The vocabulary used for filtering the results.'),
      '#options' => $options,
    );
    $form['taxonomy_depth'] = array(
      '#type' => 'number',
      '#title' => t('OPTION 1: Taxonomy Depth'),
      '#description' => t('The number of levels of the tree to return. Leave NULL to return all levels.'),
      '#default_value' => (isset($this->options['taxonomy_depth'])) ? $this->options['taxonomy_depth'] : NULL,
    );
    $form['taxonomy_parent'] = array(
      '#type' => 'textfield',
      '#description' => t('The parent term name from which to generate filters. Leave empty to generate from root.'),
      '#title' => t('OPTION 1: Parent Term'),
      '#default_value' => (isset($this->options['taxonomy_parent'])) ? $this->options['taxonomy_parent'] : NULL,
    );
    $form['filter_field'] = array(
      '#type' => 'textfield',
      '#title' => t('OPTION 2: Filter Field'),
      '#default_value' => (isset($this->options['filter_field'])) ? $this->options['filter_field'] : NULL,
      '#description' => t('The machine name of a Content Type\'s taxonomy field used for filtering. Setting this field will override any OPTION 1 configuration. This should be the machine name of the field attached to the relevant content type. E.g., field_umdt_ct_person_departments. The advantage of using this is that unused terms will be omitted from display, but this may not always be desired. Leave this blank if not in use.'),
    );
    $form['example'] = array(
      '#title' => t('Example Field Output'),
      '#type' => 'textarea',
      '#value' => '    <div class="filtr-item" data-category="TERM_ID" data-sort="TITLE">
      PHOTO
      <div class="desc">
        TITLE
      </div>
    </div>',
      '#description' => t('To make use of this, create a Custom Text field and reference hidden fields as tokens using this HTML as a starting point for output. Do not strip any classes, but you may be able to augment the markup. It is likely all fields will need to be hidden and tokenized except for the Custom Text field.'),
    );
  }

  protected function getVocabularies() {
    $vocabularies = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->loadMultiple();
    $options = [];
    foreach ($vocabularies as $id => $value) {
      $options[$id] = $id;
    }
    return $options;
  }

}

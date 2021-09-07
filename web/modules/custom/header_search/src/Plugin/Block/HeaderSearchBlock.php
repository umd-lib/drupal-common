<?php
/**
 * @file
 * Definition of Drupal\header_search\Plugin\Block\HeaderSearchBlock
 */

namespace Drupal\header_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Implements the HeaderSearchBlock
 * 
 * @Block(
 *   id = "header_search",
 *   admin_label = @Translation("Header Search"),
 *   category = @Translation("custom"),
 * )
 */
class HeaderSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $form = \Drupal::formBuilder()->getForm('Drupal\header_search\Form\HeaderSearchForm');
    return [
      '#theme' => 'header_search_block',
      '#header_search_form' => $form,
    ];
  }
}
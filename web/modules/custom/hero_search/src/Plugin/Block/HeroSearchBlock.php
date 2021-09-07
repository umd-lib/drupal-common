<?php
/**
 * @file
 * Definition of Drupal\hero_search\Plugin\Block\HeroSearchBlock
 */

namespace Drupal\hero_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\hero_search\Helper\HeroSearchSettingsHelper;

/**
 * Implements the HeroSearchBlock
 * 
 * @Block(
 *   id = "hero_search",
 *   admin_label = @Translation("Hero Search"),
 *   category = @Translation("custom"),
 * )
 */
class HeroSearchBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $configHelper = HeroSearchSettingsHelper::getInstance();
    $form = \Drupal::formBuilder()->getForm('Drupal\hero_search\Form\HeroSearchForm');
    $rendered_form = \Drupal::service('renderer')->render($form);
    $buttons = array_filter([
      $configHelper->getLinkField('button1'),
      $configHelper->getLinkField('button2'),
      $configHelper->getLinkField('button3'),
      $configHelper->getLinkField('button4'),
    ]);
    $top_right_link = $configHelper->getLinkField('top_right_link');
    $bottom_left_link = $configHelper->getLinkField('bottom_left_link');
    $bottom_right_link = $configHelper->getLinkField('bottom_right_link');
    $title = $configHelper->getSearchTitle();
    return [
      '#theme' => 'hero_search_block',
      '#hero_search_title' => $title,
      '#hero_search_form' => $rendered_form,
      '#hero_search_buttons' => $buttons,
      '#hero_search_top_right_link' => $top_right_link,
      '#hero_search_bottom_left_link' => $bottom_left_link,
      '#hero_search_bottom_right_link' => $bottom_right_link,
    ];
  }
}

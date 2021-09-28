<?php

namespace Drupal\hero_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\hero_search\Helper\HeroSearchSettingsHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Implements the HeroSearchBlock.
 *
 * @Block(
 *   id = "hero_search",
 *   admin_label = @Translation("Hero Search"),
 *   category = @Translation("custom"),
 * )
 */
class HeroSearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected $formBuilder;
  protected $renderer;

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      FormBuilderInterface $formBuilder,
      RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
    $this->renderer = $renderer;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $configHelper = HeroSearchSettingsHelper::getInstance();
    $form = $this->formBuilder->getForm('Drupal\hero_search\Form\HeroSearchForm');
    $rendered_form = $this->renderer->render($form);
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

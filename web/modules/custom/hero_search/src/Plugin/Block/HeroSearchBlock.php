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

  /**
   * Form builder service.
   *
   * @var Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  protected $formBuilder;

  /**
   * Renderer service.
   *
   * @var Drupal\Core\Plugin\ContainerFactoryPluginInterface
   */
  protected $renderer;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   Configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The id for the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin implementaton.
   * @param Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   The "form_builder" service instance to use.
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   The "renderer" service instance to use.
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
   * {@inheritdoc}
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
    $hero_search_alert = $configHelper->getAlert();
    $discover_links = $configHelper->getDiscoverLinks();
    $top_content = $configHelper->getTopContent();
    $bottom_content = $configHelper->getBottomContent();
    $search_more_links = $configHelper->getSearchMoreLinks();
    $quick_actions = $configHelper->getQuickActions();
    $title = $configHelper->getSearchTitle();
    return [
      '#theme' => 'hero_search_block',
      '#hero_search_title' => $title,
      '#hero_search_form' => $rendered_form,
      '#hero_search_alert' => $hero_search_alert,
      '#hero_discover_links' => $discover_links,
      '#hero_search_more_links' => $search_more_links,
      '#hero_top_content' => $top_content,
      '#hero_bottom_content' => $bottom_content,
      '#hero_quick_actions' => $quick_actions,
    ];
  }

}

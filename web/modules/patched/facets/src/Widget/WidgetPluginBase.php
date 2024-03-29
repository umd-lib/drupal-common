<?php

namespace Drupal\facets\Widget;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Url;
use Drupal\facets\FacetInterface;
use Drupal\facets\Result\Result;
use Drupal\facets\Result\ResultInterface;

// UMD Customization
use Symfony\Component\HttpFoundation\Request;
// End UMD Customization

/**
 * A base class for widgets that implements most of the boilerplate.
 */
abstract class WidgetPluginBase extends PluginBase implements WidgetPluginInterface {

  /**
   * Show the amount of results next to the result.
   *
   * @var bool
   */
  protected $showNumbers;

  /**
   * The facet the widget is being built for.
   *
   * @var \Drupal\facets\FacetInterface
   */
  protected $facet;

  // UMD Customization
  protected $umd_config;
  protected $current_uri;
  // End UMD Customization

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    // UMD Customization
    if (\Drupal::moduleHandler()->moduleExists('facet_overrides')) {
      $this->umd_config = \Drupal::config('facet_overrides.settings');
    }
    // End UMD Customization
    $current_path = \Drupal::service('path.current')->getPath();
    $this->current_uri = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);
  }

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $this->facet = $facet;

    $items = array_map(function (Result $result) use ($facet) {
      if (empty($result->getUrl())) {
        return $this->buildResultItem($result);
      }
      else {
        // When the facet is being build in an AJAX request, and the facetsource
        // is a block, we need to update the url to use the current request url.
        if ($result->getUrl()->isRouted() && $result->getUrl()->getRouteName() === 'facets.block.ajax') {
          $request = \Drupal::request();
          $url_object = \Drupal::service('path.validator')
            ->getUrlIfValid($request->getPathInfo());
          if ($url_object) {
            $url = $result->getUrl();
            $options = $url->getOptions();
            $route_params = $url_object->getRouteParameters();
            $route_name = $url_object->getRouteName();
            $result->setUrl(new Url($route_name, $route_params, $options));
          }
        }

        return $this->buildListItems($facet, $result);
      }
    }, $facet->getResults());

    $widget = $facet->getWidget();

    return [
      '#theme' => $this->getFacetItemListThemeHook($facet),
      '#facet' => $facet,
      '#items' => $items,
      '#attributes' => [
        'data-drupal-facet-id' => $facet->id(),
        'data-drupal-facet-alias' => $facet->getUrlAlias(),
        'class' => [$facet->getActiveItems() ? 'facet-active' : 'facet-inactive'],
      ],
      '#context' => !empty($widget['type']) ? ['list_style' => $widget['type']] : [],
    ];
  }

  /**
   * Provides a full array of possible theme functions to try for a given hook.
   *
   * This allows the following template suggestions:
   *  - facets-item-list--WIDGET_TYPE--FACET_ID
   *  - facets-item-list--WIDGET_TYPE
   *  - facets-item-list.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet whose output is being generated.
   *
   * @return string
   *   A theme hook name with suggestions, suitable for the #theme property.
   */
  protected function getFacetItemListThemeHook(FacetInterface $facet) {
    $type = $facet->getWidget()['type'] ?? 'std';
    return 'facets_item_list__' . $type . '__' . $facet->id();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['show_numbers' => FALSE];
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = NestedArray::mergeDeep(
      $this->defaultConfiguration(),
      $configuration
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getQueryType() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, FacetInterface $facet) {
    $form['show_numbers'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show the amount of results'),
      '#default_value' => $this->getConfiguration()['show_numbers'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Builds a renderable array of result items.
   *
   * @param \Drupal\facets\FacetInterface $facet
   *   The facet we need to build.
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   A renderable array of the result.
   */
  protected function buildListItems(FacetInterface $facet, ResultInterface $result) {
    $classes = ['facet-item'];

    // UMD Customization
    if (\Drupal::moduleHandler()->moduleExists('facet_overrides')) {
      $items = $this->prepareUMDLink($result);
    } else {
      $items = $this->prepareLink($result);
    }
    // End UMD Customization

    $children = $result->getChildren();
    // Check if we need to expand this result.
    if ($children && ($this->facet->getExpandHierarchy() || $result->isActive() || $result->hasActiveChildren())) {

      $child_items = [];
      $classes[] = 'facet-item--expanded';
      foreach ($children as $child) {
        $child_items[] = $this->buildListItems($facet, $child);
      }

      $items['children'] = [
        '#theme' => $this->getFacetItemListThemeHook($facet),
        '#items' => $child_items,
      ];

      if ($result->hasActiveChildren()) {
        $classes[] = 'facet-item--active-trail';
      }

    }
    else {
      if ($children) {
        $classes[] = 'facet-item--collapsed';
      }
    }

    if ($result->isActive()) {
      $items['#attributes']['class'][] = 'is-active';
    }

    $items['#wrapper_attributes'] = ['class' => $classes];
    $items['#attributes']['data-drupal-facet-item-id'] = Html::getClass($this->facet->getUrlAlias() . '-' . strtr($result->getRawValue(), ' \'\"', '---'));
    $items['#attributes']['data-drupal-facet-item-value'] = $result->getRawValue();
    $items['#attributes']['data-drupal-facet-item-count'] = $result->getCount();
    return $items;
  }

  /**
   * Returns the text or link for an item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   A result item.
   *
   * @return array
   *   The item as a render array.
   */
  protected function prepareLink(ResultInterface $result) {
    $item = $this->buildResultItem($result);

    if (!is_null($result->getUrl())) {
      $item = (new Link($item, $result->getUrl()))->toRenderable();
    }

    return $item;
  }

  /**
   * UMD Customized version of prepareLink method above.
   * All documentation applies.
   */
  protected function prepareUMDLink(ResultInterface $result) {
    $item = $this->buildResultItem($result);

    if (!is_null($result->getUrl())) {
      $item_url = $result->getUrl();
      $item_options = $item_url->getOptions();
      if (!empty($item_options['query']['f'])) {
        if (!empty($this->current_uri)) {
          if ($search_overrides = $this->umd_config->get('search_overrides')) {
            if (!empty($search_overrides[$this->current_uri])) {
              array_push($item_options['query']['f'], $search_overrides[$this->current_uri]);
              $item_url->setOptions($item_options);
              $result->setUrl($item_url);
            }
          }
        }
      }
      $item = (new Link($item, $result->getUrl()))->toRenderable();
    }
    return $item;
  }
  // End UMD Customization

  /**
   * Builds a facet result item.
   *
   * @param \Drupal\facets\Result\ResultInterface $result
   *   The result item.
   *
   * @return array
   *   The facet result item as a render array.
   */
  protected function buildResultItem(ResultInterface $result) {
    $count = $result->getCount();
    return [
      '#theme' => 'facets_result_item',
      '#is_active' => $result->isActive(),
      '#value' => $result->getDisplayValue(),
      '#show_count' => $this->getConfiguration()['show_numbers'] && ($count !== NULL),
      '#count' => $count,
      '#facet' => $result->getFacet(),
      '#raw_value' => $result->getRawValue(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isPropertyRequired($name, $type) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsFacet(FacetInterface $facet) {
    return TRUE;
  }

}

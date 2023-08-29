<?php

namespace Drupal\fcrepo_breadcrumbs\Breadcrumb;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\views\ViewExecutable;
use Drupal\views\Entity\View;
use Drupal\views\Views;

class ViewsBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * @inheritdoc
   */
  public function applies(RouteMatchInterface $route_match) {
    // This breadcrumb apply only for some views.
    $parameters = $route_match->getParameters()->all();

    if (!empty($parameters['view_id'])) {

       $views_id = array(
         'fcrepo_detail_page',
       );

       if (in_array($parameters['view_id'], $views_id)) {
         foreach ($parameters as $key => $value) {
         }
         return TRUE;
       }
       return FALSE;
    }
  }

  /**
   * @inheritdoc
   */
  public function build(RouteMatchInterface $route_match) {

    $collection_route = 'view.fcrepo_search.page_1';
    $search_name = "Search";
    $collection_name = "Collection";
    $parameters = $route_match->getParameters()->all();
    $options = ['attributes' => ['id' => 'dynamic-collection-breadcrumb']];
    
    $breadcrumb = new Breadcrumb();
    $breadcrumb
      ->addCacheContexts([
      'url.path.parent',
    ]);
    $breadcrumb
      ->addLink(Link::createFromRoute($this
      ->t('Home'), '<front>'));
    $breadcrumb
      ->addLink(Link::createFromRoute($this
      ->t($search_name), $collection_route, [], $options));
    $breadcrumb
      ->addLink(Link::createFromRoute($this
      ->t($collection_name), $collection_route, [], $options));
    return $breadcrumb;
  }

}

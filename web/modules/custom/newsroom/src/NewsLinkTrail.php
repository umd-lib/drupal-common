<?php

namespace Drupal\newsroom;

use Drupal\context\ContextManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * Allow the active trail to be set manually.
 * 
 * This sets the active trail for the dynamically added news page 
 * menu links. For other pages, it defaults to the MenuActiveTrail
 * implementation.
 */
class NewsLinkTrail extends MenuActiveTrail {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link plugin manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A route match object for finding the active link.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Context\ContextManager $context_manager
   *   The context manager.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, RouteMatchInterface $route_match, CacheBackendInterface $cache, LockBackendInterface $lock) {
    parent::__construct($menu_link_manager, $route_match, $cache, $lock);
    $this->tags[] = 'news_link_trail';
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      if ($node->getType() == 'umd_terp_article') {
        $instance = $this->menuLinkManager->getInstance(['id' => 'newsroom.news_article']);
        return $instance;
      }
    }
    return parent::getActiveLink($menu_name);
  }

}

<?php

namespace Drupal\umd_dynamic_menu_trail;

use Drupal\context\ContextManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;

/**
 * 
 */
class UmdDynamicActiveTrail extends MenuActiveTrail {
  
  private $config;

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
    $this->tags[] = 'umd_dynamic_active_trail';
    $this->config = \Drupal::getContainer()->get('config.factory')->getEditable('umd_dynamic_menu_trail.mapping_config');
  }

  /**
   * {@inheritdoc}
   */
  public function getActiveLink($menu_name = NULL) {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      if ($menu_link_id = $this->getNodeTypeMenuMapping($node->getType())) {
        $instance = $this->menuLinkManager->getInstance(['id' => $menu_link_id]);
        return $instance;
      }
    }
    return parent::getActiveLink($menu_name);
  }
  
  private function getNodeTypeMenuMapping($node_type) {
    if ($val = $this->config->get($node_type)) {
      return $val;
    }
  }
  
  public function addNodeTypeMapping($node_type, $menu_link_id) {
    \Drupal::logger('umd_dynamic_menu_trail')->notice("Adding mapping for $node_type -> $menu_link_id");
    $this->config->set($node_type, $menu_link_id)->save();
  }
  
  public function removeNodeTypeMapping($node_type) {
    \Drupal::logger('umd_dynamic_menu_trail')->notice("Removing mapping for $node_type");
    $this->config->clear($node_type)->save();
  }

}

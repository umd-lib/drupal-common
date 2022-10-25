<?php
namespace Drupal\umd_careers\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Url;
use Drupal\Core\Menu;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;

/**
 * Represents a menu link for careers item
 */
class CareersMenuLink extends MenuLinkDefault {

  // Check if the current node is a career node
  private function getCareersNode() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
      if ($node->getType() == 'umd_library_careers') {
        return $node;
      }
    }
    return NULL;
  }

  // Only enable for umd_terp_article node pages
  public function isEnabled() {
    if ($node = $this->getCareersNode()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  // Return the title for careers menu link
  public function getTitle() {
    if ($node = $this->getCareersNode()) {
      $nid = $node->id();
      $title = $node->getTitle();
      return strlen($title) > 30 ? substr($title,0,27) . '...' : $title;
    } else {
      return "Dynamic Careers Menu Placeholder";
    }
  }

  /**
   * Return the route parameters for the menu link.
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    $url = Url::fromRoute('<current>');
    $arr = explode('/about/careers/listing/', $url->toString());
    if (count($arr) > 1) {
      $params = explode('/', $arr[1]);
      if (count($params) == 2) {
        return [
          'year_month' => $params[0],
          'title' => $params[1]
        ];
      }
    }
    return [
      'year_month' => NULL,
      'title' => NULL
    ];
  }
}

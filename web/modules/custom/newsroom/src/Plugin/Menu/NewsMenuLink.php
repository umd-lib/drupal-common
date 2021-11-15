<?php
namespace Drupal\newsroom\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Url;

/**
 * Represents a menu link for newsroom article
 */
class NewsMenuLink extends MenuLinkDefault {

  // Check if the current node is a umd_terp_article node
  private function getUmdTerpArticleNode() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
      if ($node->getType() == 'umd_terp_article') {
        return $node;
      }
    }
    return NULL;
  }

  // Only enable for umd_terp_article node pages
  public function isEnabled() {
    if ($node = $this->getUmdTerpArticleNode()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  // Return the title for umd_terp_article menu link
  public function getTitle() {
    if ($node = $this->getUmdTerpArticleNode()) {
      $nid = $node->id();
      $title = $node->getTitle();
      return strlen($title) > 30 ? substr($title,0,27) . '...' : $title;
    } else {
      return "Dynamic News Article Menu Placeholder";
    }
  }

  /**
   * Return the route parameters for the menu link.
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    $url = Url::fromRoute('<current>');
    $arr = explode('/about/news/', $url->toString());
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
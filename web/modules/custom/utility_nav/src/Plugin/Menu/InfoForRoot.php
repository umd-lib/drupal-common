<?php
namespace Drupal\utility_nav\Plugin\Menu;

use Drupal\Core\Menu\MenuLinkDefault;
use Drupal\Core\Url;

/**
 * Represents a menu link for umd_terp_person_extensions person
 */
class InfoForRoot extends MenuLinkDefault {

  // Check if the current node is a umd_terp_person node
  private function getInfoForRootNode() {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      $nid = $node->id();
dsm($nid);
dsm($node->getType());
      if ($node->getType() == 'umd_terp_basic_page') {
        return $node;
      }
    }
    return NULL;
  }

  // Only enable for umd_terp_person node pages
  public function isEnabled() {
    if ($node = $this->getInfoForRootNode()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

  // Return the title for umd_terp_person menu link
  public function getTitle() {
    if ($node = $this->getInfoForRootNode()) {
      $nid = $node->id();
      $title = $node->getTitle();
dsm($title);
      return strlen($title) > 30 ? substr($title,0,27) . '...' : $title;
    } else {
      return "Dynamic Information For Placeholder";
    }
  }

  /**
   * Return the route parameters for the menu link.
   * {@inheritdoc}
   */
  public function getRouteParameters() {
    return ['name' => NULL];
    $url = Url::fromRoute('<current>');
    $arr = explode('/information-for/', $url->toString());
    if (count($arr) > 1) {
      return [
        'name' => $arr[1]
      ];
    }
    return [
      'name' => NULL
    ];
  }
}

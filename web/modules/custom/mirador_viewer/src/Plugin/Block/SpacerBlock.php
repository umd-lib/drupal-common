<?php

namespace Drupal\mirador_viewer\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides a sidebar spacer Block.
 */

#[Block(
  id: "Sidebar Spacer",
  admin_label: new TranslatableMarkup("Sidebar Spacer"),
  category: new TranslatableMarkup("Sidebar Spacer")
)]

class SpacerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#theme' => 'sidebar_spacer'
    ];
  }

}


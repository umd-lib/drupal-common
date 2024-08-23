<?php

namespace Drupal\umd_commands\Commands;

use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drupal\block\Entity\Block;
use Symfony\Component\Console\Input\InputOption;

class GeneralCommands extends DrushCommands
{

  /**
   * Enable header alerts block
   *
   * @command umd-commands:enable-alert
   *
   * @usage drush umd-commands:enable-alert
   *
   * @aliases umd-enable-alert
   *
   * @param $alert_body
   */
  public function enableHeaderAlert(string $alert_body = null) : void {
    if ($this->blockVisibilityActions('umd_terp_headeralert', TRUE, $alert_body)) {
      $this->output()->writeln('Alert Enabled');
      return;
    }
    $this->output()->writeln('Nothing done');
  }

  /**
   * Disable header alerts block
   *
   * @command umd-commands:disable-alert
   *
   * @usage drush umd-commands:disable-alert
   *
   * @aliases umd-disable-alert
   */
  public function disableHeaderAlert() : void {
    if ($this->blockVisibilityActions('umd_terp_headeralert', FALSE)) {
      $this->output()->writeln('Alert Disabled');
      return;
    }
    $this->output()->writeln('Nothing done');
  }

  private function blockVisibilityActions($block_name, $status, $body_text = null) : bool {
    $block = Block::load($block_name);
    if (empty($block)) {
      return false;
    }
    if (!empty($body_text)) {
      $new_body_data = [
        'value' => $body_text,
        'format' => 'full_html',
      ];
      $block->set('body', $new_body_data);
    }
    $block->setStatus($status);
    return $block->save();
  }

}

<?php

namespace Drupal\message_notify_staff\Commands;

use Drupal\Core\Session\UserSession;
use Drush\Commands\DrushCommands;
use Drupal\Core\Session\AccountSwitcherInterface;
use Drupal\message_digest;

class MessageNotifyStaffCommands extends DrushCommands {

  /**
   * The account switcher service.
   *
   * @var \Drupal\Core\Session\AccountSwitcherInterface
   */
  protected $accountSwitcher;

  /**
   * SimplesitemapCommands constructor.
   *
   * @param \Drupal\Core\Session\AccountSwitcherInterface $account_switcher
   *   The account switching service.
   */
  public function __construct(AccountSwitcherInterface $account_switcher) {
    $this->accountSwitcher = $account_switcher;
  }

  /**
   * Fire drush digest command.
   *
   * @usage drush send-digest 
   *   Send digest command.
   *
   * @command send-digest
   */
  public function sendDigest() {
    /** @var \Drupal\message_digest\DigestManagerInterface $digest_manager */
    // Switch to root user (--user option was removed from drush 9).
    $this->accountSwitcher->switchTo(new UserSession(['uid' => 1]));

    /** @var \Drupal\message_digest\DigestManagerInterface $digest_manager */
    $digest_manager = \Drupal::service('message_digest.manager');
  
    // Process message digests.
    $digest_manager->processDigests();

    // Cleanup old messages.
    $digest_manager->cleanupOldMessages();

    $queue_name = 'message_digest';

    /** @var $queue_manager \Drupal\Core\Queue\QueueWorkerManagerInterface */
    $queue_manager = \Drupal::service('plugin.manager.queue_worker');

    /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');

    // Make sure every queue exists. There is no harm in trying to recreate
    // an existing queue.
    $info = $queue_manager->getDefinition($queue_name);
    $queue_factory->get($queue_name)->createQueue();
    $queue_worker = $queue_manager->createInstance($queue_name);
    $queue = $queue_factory->get($queue_name);

    try {
      if ($item = $queue->claimItem()) {
        // Process and delete item
        $queue_worker->processItem($item->data);
        $queue->deleteItem($item);
      }
      else {
        // If we cannot claim an item we must be done processing this queue.
      }
    } catch (RequeueException $e) {
      // The worker requested the task be immediately requeued.
      $queue->releaseItem($item);
      echo $e->getMessage();
    } catch (SuspendQueueException $e) {
      // If the worker indicates there is a problem with the whole queue,
      // release the item and skip to the next queue.
      $queue->releaseItem($item);
      echo $e->getMessage();
    } catch (\Exception $e) {
      // In case of any other kind of exception, log it and leave the item
      // in the queue to be processed again later.
      echo $e->getMessage();
    }    







    $this->accountSwitcher->switchBack();
  }
}

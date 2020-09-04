<?php

namespace Drupal\samlauth_attrib\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that catches samlauth  user_sync events to provision roles.
 */
class UserGroupsSyncEventSubscriber implements EventSubscriberInterface {

  const SETTINGS = 'samlauth_attrib.settings';
  const EVENT = 'samlauth.user_sync';

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * A configuration object containing samlauth settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Construct a new UserGroupsSyncEventSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $this->logger = $logger;
    $this->config = $config_factory->get(static::SETTINGS);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[static::SETTINGS][] = ['onUserGroupsSync'];
    return $events;
  }

  /**
   * Performs actions to synchronize roles with SAML data on login.
   *
   * @param \Drupal\samlauth_attrib\Event\SamlauthUserSyncEvent $event
   *   The event.
   */
  public function onUserGroupsSync(SamlauthUserSyncEvent $event) {
    $attributes = $event->getAttributes();
    if (!$grouper_attrib = $this->config->get('grouper_attrib')) {
      $this->logger->warning(t('Grouper Attribute not set'));
      return;
    }
    if (!empty($attributes[$grouper_attrib][0])) {
      $groups = $attributes[$grouper_attrib];
      $account = $event->getAccount();

      if (!$account->id() > 0) {
        $this->logger->notice('Bad or anonymous account. No roles provisioned.');
        return;
      }

      $roles = $this->config->get('grouper_map');

      $allRoles = $account->getRoles(TRUE);

      $updated_roles = [];
      foreach ($groups as $group) {
        if ($role = $roles[strtolower($group)]) {
          array_push($updated_roles, $role);
        }
      }
      if (count($updated_roles) > 0) {
        $this->resetRoles($account);
        foreach ($updated_roles as $updated_role) {
          $account->addRole($updated_role);
        }
      } else {
        $this->logger->notice('User has no roles');
        $this->resetRoles($account);
      }
      $account->save();
    }
  }

  /**
   * Remove all current roles.
   *
   * @param \Drupal\Core\Session\Account $account
   *   A valid account
   */
  protected function resetRoles($account) {
    if ($account->getRoles() > 0) {
      foreach ($account->getRoles() as $role) {
        $account->removeRole($role);
      }
    }
  }

}

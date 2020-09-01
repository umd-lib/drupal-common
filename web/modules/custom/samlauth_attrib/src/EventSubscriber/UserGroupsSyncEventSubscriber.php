<?php

namespace Drupal\samlauth_attrib\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Drupal\samlauth\EventSubscriber\UserSyncEventSubscriber;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that synchronizes user properties on a user_sync event.
 *
 * This is basic module functionality, partially driven by config options. It's
 * split out into an event subscriber so that the logic is easier to tweak for
 * individual sites. (Set message or not? Completely break off login if an
 * account with the same name is found, or continue with a non-renamed account?
 * etc.)
 */
class UserGroupsSyncEventSubscriber extends UserSyncEventSubscriber {

  protected $userSyncEventSubscriber;

  /**
   * Construct a new SamlGroupsauthUserSyncSubscriber.
   *
   * @param Drupal\samlauth\EventSubscriber\UserSyncEventSubscriber $user_sync
   */
  public function __construct(UserSyncEventSubscriber $user_sync, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, TypedDataManagerInterface $typed_data_manager, EmailValidator $email_validator, LoggerInterface $logger) {
    $this->config = $config_factory->get('samlauth.authentication');
    $this->userSyncEventSubscriber = $user_sync;
    parent::__construct($config_factory, $entity_type_manager, $typed_data_manager, $email_validator, $logger);
  }

  /**
   * {@inheritdoc}
   */
  public function onUserSync(SamlauthUserSyncEvent $event) {
    $this->userSyncEventSubscriber->onUserSync($event);
    print "Test event.";
  }

}

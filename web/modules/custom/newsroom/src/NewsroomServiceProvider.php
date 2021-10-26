<?php

namespace Drupal\newsroom;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\DependencyInjection\Reference;

class NewsroomServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Get the service we want to modify.
    $definition = $container->getDefinition('menu.active_trail');
    // Make the active trail use our service.
    $definition->setClass(NewsLinkTrail::class);
  }
}
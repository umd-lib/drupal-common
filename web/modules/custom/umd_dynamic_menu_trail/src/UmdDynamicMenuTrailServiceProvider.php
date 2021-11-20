<?php

namespace Drupal\umd_dynamic_menu_trail;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\umd_dynamic_menu_trail\UmdDynamicActiveTrail;

class UmdDynamicMenuTrailServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Get the service we want to modify.
    $definition = $container->getDefinition('menu.active_trail');
    // Make the active trail use our service.
    $definition->setClass(UmdDynamicActiveTrail::class);
  }
}
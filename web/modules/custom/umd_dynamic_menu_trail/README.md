# UMD Dynamic Menu Trail module

This module provides the active trail service that sets the active trail
information for dynamic menu links added by other UMD module.

Any module that adds dynamic menu links should use its install/uninstall hooks
to add/remove the mapping configuration.

## Adding node to menu link mapping:

The install hook on the module should include the below code to map the
dynamic menu link to the appropriate node type.

```
/**
 * Install mymodule configuration.
 */
function mymodule_install() {
  $node_type = 'nodetype';
  $menu_link_id = 'mymodule.mymenu';
  \Drupal::service('menu.active_trail')->addNodeTypeMapping($node_type, $menu_link_id);
}
```

## Removing node to menu link mapping:

The uninstall hook on the module should remove the mapping.

```
/**
 * Uninstall mymodule configuration.
 */
function mymodule_uninstall() {
  $node_type = 'nodetype';
  \Drupal::service('menu.active_trail')->removeNodeTypeMapping($node_type);
}
```
# Utility Navigation module

This module provides a utility navigation menu as a static HTML block.

A static HTML block was used, instead of core Drupal menu functionality, as
the desired appearance of the menu required greater HTML flexibility than seemed
available with menus.

This module uses the "fixed_block_content" module
(<https://www.drupal.org/project/fixed_block_content>) to provide a default
implementation of the menu in the YAML configuration.

## Setup

Enable the "Utility Nav" module in "Manage | Extend", or run the following
"drush" command:

```
> drush --yes pm:enable utility_nav
```

## Configuration

A default implementation of the utility navigation menu is provided via YAML
configuration.

The utility navigation menu can be modified by changing the editing the
"Utility Navigation Fixed Block" block on the
"Manage | Structure | Block Layout | Custom block library" page
(/admin/structure/block/block-content).

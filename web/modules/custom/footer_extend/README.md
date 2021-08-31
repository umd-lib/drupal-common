# Footer Extend module

This module adds an custom block type (footer_addon) that can be used to place styled links into the footer addon space in the umd_terp theme.

Also, this creates the second footer menu (Footer2) to be displayed the footer.

## Setup

Enable the "Footer Extend" module in "Manage | Extend"

**NOTE:** After module installation, the `block--block-content-footer-addon.html.twig` file needs to be copied to the `umd_terp` theme. Likewise, it should be deleted on uninstall.

```
# After module installation
mkdir web/themes/frozen/umd_terp/templates/block/
cp web/modules/custom/footer_extend/templates/block--block-content-footer-addon.html.twig web/themes/frozen/umd_terp/templates/block/block--block-content-footer-addon.html.twig

# After module uninstall
rm web/themes/frozen/umd_terp/templates/block/block--block-content-footer-addon.html.twig
```

Also, clear the cache for the template changes to take effect.

## Theming

css/footer-extend.css is provided for theming the footer_addon custom block type.

## Config

`config/install` provides the following configurations:

* Custom Block Type (footer_addon)
* Field (footer_addon)
* Field Storage (footer_addon)
* Entity Form Display (footer_addon)
* Entity View Display (footer_addon)
* Menu (footer2)

After installing the module, you create custom blocks of type footer_addon and place them on the "Footer Addon" region from `/admin/structure/block/list/umd_terp` path.

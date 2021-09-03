# UMD Lib Customization of UMD Terp Theme

## Footer

The footer template is over to add a new region footer_addon to allow placing custom content on the footer. Also, footer.css file is added for umd lib specific theming of the footer.

- Created `templates/_includes/global/umdlib-footer.html.twig`
- Configured `templates/core/page.html.twig` to use `umdlib-footer.html.twig` instead of `footer.html.twig`
- Created `css/footer.css`
- Configured `umd_terp.libraries.yml` to include `css/footer.css`.
- Added `footer_addon` region to `umd_terp.info.yml`.
- Added additional `umd_terp_footer_settings` configuration fields to `theme-settings.php`.

Note: The `templates/block/block--block-content-footer-addon.html.twig` needs to be copied from and kept in sync with `../../../modules/custom/footer_extend/templates/block--block-content-footer-addon.html.twig`. The `footer_extend` module will the primary tracking location for this template.

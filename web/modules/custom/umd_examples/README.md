# UMD Examples

This module demonstrates the following:

* Basic module structure
* Templating within a Drupal module
** umd_examples.module for hook_theme implementation
** templates/umd-example-template.html.twig for Twig template
** src/Controller/ExamplesController.php invokes template 
* Displaying content at a specific path
** umd_examples.routing.yml (umd_examples.sample_page) for path and config
** src/Controller/ExamplesController.php provides content
* Drupal entity query
** src/Controller/ExamplesController.php/entityQuery
* Solr query using Search API
** src/Controller/ExamplesController.php/solrQuery
* Settings / Administration form
** umd_examples.routing.yml (umd_examples.sample_settings_form) for path and config
** src/Form/ExampleSettingsForm.php provides form definition and processing
* Custom Views Field
** src/Plugin/views/field/DemoField.php
** umd_examples.views.inc

TODO
* Custom Views filter
* Event Listener
* Custom cron job

https://www.drupal.org/docs/develop/standards/coding-standards

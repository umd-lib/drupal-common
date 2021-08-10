# UMD Feature Example 

## Development Workflows

### Initial Packaging

When ready to deploy or distribute a new feature, you can clone this module and do the following:

* Rename all files to your feature name.

* Update your .info.yml with feature information.

* Update your .info.yml with dependency requirements.

	Note that easy_install is a recommended dependency during development, and it will allow a developer to easily nuke and rebuild a pre-existing install likely to test or deploy updated configurations.

* Export the new configurations under /admin/config/development/configuration/single/export and place these under config/install/

	Note that the UUID line must be removed for each configuration file. Otherwise, Drupal will assume the configuration is associated with a different site.

	Keep in mind that when exporting a content type, you must also export the fields and field storage. Other configurations may have similar requirements.

* Customize your .install file

### Processing Updates

During development, configurations under config/install can be updated and redeployed. Changes can also be added to a PR for testing and merging.

Use easy_install to purge a stale configuration as part of the uninstall process prior to installing an updated version of the module.

#### Purging Demo Content on Uninstall

Occasionally, you may want your module to purge all stale content associated with a feature.

(Untested) See https://stackoverflow.com/a/62230494 for a possible solution.

## Production Workflows

While the easy_install method for releasing production updates can work in some circumstances, it is generally better to modify a production site's configuration using update hooks. See:

https://www.drupal.org/docs/drupal-apis/update-api/updating-entities-and-fields-in-drupal-8

It is also required to update yml configurations with changes. The update hooks are only employed by sites with the feature pre-installed. New sites will still use the yml configuration.

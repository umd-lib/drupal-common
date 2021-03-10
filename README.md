# UMD Drupal Common

## Prerequisites 

Install the latest **Composer** (https://getcomposer.org/download/) if not already
installed.

(Recommended) Add the following to your **~/.profile**:

            alias composer="composer.phar"

Add the following to your /etc/hosts (customized if you wish):

            127.0.0.1		drupal.docker.localhost
            127.0.0.1		portainer.drupal.docker.localhost
            127.0.0.1		solr.drupal.docker.localhost

## Local Deploy (new site)

### Install

To deploy locally, clone the develop branch:

            > git clone https://github.com/umd-lib/drupal-common.git common-demo
            > cd common-demo
            > git checkout develop

Assuming this site will run the UMD Libraries theme, you will need to clone this
repo as well:

            > cd common-demo/web/themes
            > mkdir custom	# If not already created
            > cd custom
            > git clone git@github.com:umd-lib/umd-libraries-theme.git umd_libraries
            > cd umd_libraries
            > git checkout develop

Install the site using Composer. This will download all dependencies and prep
for local deployment.

            # in common-demo/
            > composer install

Clone the drupal-projects-env repository locally and copy the demo/env file to
your web root as .env:

            > cp drupal-projects/demo/env common-demo/.env

Customize this file for your environment. Specifically, look at the values for:

* PROJECT_NAME
* DB_DATA_DIR
* SOLR_DATA_DIR

All other values can stay the same.

To bring up the site, use docker-compose:

            > cd common-demo
            > docker-compose up -d

After a minute, the site should be available at:

* http://drupal.docker.localhost:8000 (or wherever you deployed).

For a new site, Drupal will launch the site setup wizard. Use whichever Postgres
username/password/database combos you had set up in your .env file. Note that
you will need to change host under  Advanced Operations in your database
configuration. Change this to simply *postgres*.

If all values are correct, Drupal will install and provide you will an site
configuration form.

## Basic Configuration

### Install UMD Terp Stack

Once the site is installed, open the *Extend* panel, which lists
plugins--enabled and disabled. 

Install the following:

* UMD Schoolwide Header
* UMD Terp (all)
* Twig Field Value
* Twig Tweak
* Image Optimize - Binaries
* CAS (no need to install CAS Attributes)
* Metatag
* Google Tag Manager
* SAMLAuth Attrib (to be used later)

Some are necessary for UMD Terp to behave correctly. Others are just nice to
have or intended for future-proofing.

(Note that in local, this installation could take a while. To prevent PHP
timeouts, consider enabling only five or so at a time as many also install
dependencies.)

UMD Terp also requires an *optimized* Image Style. For this, click
*Configuration* and first ensure you have an *Image Optimize* pipeline for Local
Binaries. Then, under *Image styles*, create a style called *optimized* using
the *Local Binaries* pipeline.

We can now enable the UMD Terp theme. Do this under *Appearance*. Note that it
will not be used as the administration theme. Visit the site homepage and if all
comes up without errors, you can now install the UMD Libraries theme.

### Configure UMD Libraries theme

If you haven't yet, install the UMD Libraries theme under *Appearance* as
default. Again, this should not be used as the administration theme (which
should be kept as Seven unless another admin theme is intentionally installed.)

Once installed, click *Settings* for UMD Libraries.

*Logo Image* and *Favicon* can be kept at defaults.

*UMD Terp Header* should be set to *Light header style* (as dark is not
currently supported).

Under *UMD Terp Social Media Accounts*, click *Hide Social Icons*.

*UMD Libraries Header Settings* has options if this is a digital branded site,
but otherwise, all other options can be left empty/unchecked for now.

#### Menu Configuration

Under *Structure*, select *Menus* and create a menu called *Global*.

At the very least, make sure this menu has the following entries:

* Privacy Policy : https://www.lib.umd.edu/about/privacy
* Web Accessibility : https://www.umd.edu/web-accessibility

Note that the *Main Navigation* menu manages the top menu. Heirarchical menu
options are supported and will appear as a drop-down.

#### Content Types

Under *Structure*, select *Content Types* and delete the *Article* and *Basic
Page* content types. We will be using only the UMD Terp content types. Depending
on the site requirements, we may be able to also remove the UMD Terp Person
content type.

### Developer Configuration

For development, it is good to copy the drupal-projects-env/services.yml to
common-demo/web/sites/default/. This will preconfigure your site for dev mode
and make theming much easier by injecting template information directly into the
site markup. Note that we don't want this file included in production (which is
a danger given that it isn't currently overwritten by k8s configuration.)

Under *Configuration* and *Performance*, make sure both Aggregate CSS/JavaScript
checkboxes are disabled.

Under *Extend*, you can enable the *Devel* module, which, among other features,
provides a *Clear Cache* link on the toolbar.  You're in Drupal now and will be
using *Clear Cache* with some frequency.

## Local Install (Existing Site)

As an example, we're using WHPool. The process should be the same for PACT and 
1856Project. Note that Staff Blog might require some special handling (TODO).

Clone the develop branch:

            > git clone https://github.com/umd-lib/drupal-common.git common-whpool
            > cd common-whpool
            > git checkout develop

Because WHPool runs the UMD Libraries theme, you will need to clone this repo:

            > cd common-whpool/web/themes
            > mkdir custom	# If not already created
            > cd custom
            > git clone git@github.com:umd-lib/umd-libraries-theme.git umd_libraries
            > cd umd_libraries
            > git checkout develop

Install the site using Composer. This will download all dependencies and prep
for local deployment.

            # in common-whpool/
            > composer install

Clone the drupal-projects-env repository locally and copy the whpool/env file to
your web root as .env:

            > cp drupal-projects/whpool/env common-whpool/.env

Customize this file for your environment. Specifically, look at the values for:

* DB_DATA_DIR
* SOLR_DATA_DIR

All other values can stay the same.

Copy the settings.php from drupal-projects-env to the proper location:

            > cp drupal-projects/whpool/settings.php common-whpool/web/sites/default/

To get WHPool data, dump the Kubernetes database:

            > cd common-whpool/
            > kubectl exec drupal-whpool-db-0 -- pg_dump -c -O -U drupaldb -d drupaldb > postgres-init/whpool.sql

To bring up the site, use docker-compose:

            > cd common-whpool
            > docker-compose up -d

After a minute, the site should be available at:

* http://drupal.docker.localhost:8000 (or wherever you deployed).

You can also copy any files you might need from the server into your local:

            > kubectl exec --stdin --tty drupal-whpool-0 -- /bin/bash
            > tar -czvf files.tgz web/sites/default/files/
            > exit
            > kubectl cp drupal-whpool-0:files.tgz common-whpool/

And then extract the archive into your local's web/sites/default/files/.

Flush cache after this so that Drupal can regenerate any thumbnails, etc.

One thing to note is that a production database is likely optimized for performance
and may need to have CSS/JS aggregation turned off and modules such as devel enabled.

## Solr Integration

To create a local Solr core with through docker-compose, do the following:

            > docker exec -ti [container]_solr sh
            > /opt/solr/bin/solr create_core -c drupal -d /opt/solr/server/solr/configsets/search_api_solr_8.x-3.9/conf/

## Database

# Dumping Local Database

           > docker exec [container]_postgres pg_dump -U drupaluser -O drupaldb > /tmp/dump.sql


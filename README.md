# UMD Drupal Common

## Prerequisites

Install the latest **Composer** (https://getcomposer.org/download/) if not already
installed.

(Recommended) Add the following to your **~/.profile**:

            alias composer="composer.phar"

## Local Deploy (new site)

### Install

Create a base directory to hold each of the directories for the project. This
example will use "drupal", but that is arbitrary:

            > mkdir drupal
            > cd drupal

To deploy locally, clone the main branch:

            > git clone https://github.com/umd-lib/drupal-common.git common-demo
            > cd common-demo
            > git checkout main

Install the site using Composer. This will download all dependencies and prep
for local deployment.

            # in common-demo/
            > composer install

Switch back to the base ("drupal") directory, and create empty directories for the
Postgres and Solr data (used in the next steps):

            > cd ..  # back to "drupal"
            > mkdir postgres_data
            > mkdir solr_data

Clone the drupal-projects-env repository locally and copy the demo/env file
into your web root (i.e., common-demo) as .env:

            > git clone git@github.com:umd-lib/drupal-projects-env.git
            > cp drupal-projects-env/demo/env common-demo/.env

Customize the common-demo/.env file for your environment. Specifically, look at
the values for:

* PROJECT_NAME
* DB_DATA_DIR - the fully-qualified path to the "postgres_data" directory
* SOLR_DATA_DIR - the fully-qualified path to the "solr_data" directory

All other values can stay the same.

To bring up the site, use docker-compose:

            > cd common-demo
            > docker-compose up -d

After a minute, the site should be available at:

* http://drupal.docker.localhost:8000 (or wherever you deployed).

For a new site, Drupal will launch the site setup wizard. Use the UMD Profile
to get the standard libraries configuration.Use whichever Postgres
username/password/database combos you had set up in your .env file. Note that
you will need to change host under  Advanced Operations in your database
configuration. Change this to simply *postgres*.

If all values are correct, Drupal will install and provide you will an site
configuration form.

## Basic Configuration

The UMD Profile should prepare your site for basic functionality. If you
encounter errors, please report them.

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

Clone the main branch:

            > git clone https://github.com/umd-lib/drupal-common.git common-whpool
            > cd common-whpool
            > git checkout main

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

## Tools

### PHP CodeSniffer

PHP CodeSniffer (https://github.com/squizlabs/PHP_CodeSniffer) is a linter for
identifying coding standard violations.

The current configuration is defined in the "phpcs.xml.dist" file.

To check a particular directory or file, run the following commands:

            > vendor/bin/phpcs <DIRECTORY_OR_FILE>
            
where <DIRECTORY_OR_FILE> is the name of the directory or file. For example,
to check the code in the "web/modules/custom/" directory, run:

            > vendor/bin/phpcs web/modules/custom/

## VS Code Remote Containers

The VS Code "Remote Containers" functionality can be used to quickly stand up
a configured development environment, with development-specific extensions
automatically installed in the container.

For more information abot the "Remote Containers" functionality, see
(https://code.visualstudio.com/docs/remote/containers).

### Remote Containers Setup

1) Start the Drupal application using Docker Compose.

2) Open VS Code.

3) In the lower-left corner of the VS Code window, there is a small green
icon "><". Left-click the icon, and select "Remote Containers: Open Folder in Container..."

4) In the resulting dialog, select the directory where "drupal-common" was
checked out to. VS Code will reset and display the files in the "drupal-common"
directory. Container-specific extensions, such as "PHP Intellisense" and
"phpcs" will be automatically loaded. The "Terminal" window will default to
the "/var/www/html" in the PHP container.

The "PHP CodeSniffer" linter will be automatically enabled for ".php" files
(i.e., opening a ".php" file should automatically highlight any violations
in the file).

The "PHP CodeSniffer" can also be run from the VS Code Terminal. For example,
to check the "web/modules/custom/" directory:

            > phpcs web/modules/custom/
            
### Remote Containers - Enabling Debugging

The "PHP Debug" extension is added to the Remote Containers configuration by
default. However, because it seems to slow Drupal down significantly, the
"xdebug" tool is not enabled by default.

To enable the "xdebug" tools in the Docker container, do the following:

1) On the host machine, stop the running Docker containers. The simplest way
to do this is to go to the directory where "drupal-common" is checked out
and run:

            > make down
            
2) Prune the existing containers (this seems to be necessary, as otherwise the
PHP container does not appear to restart properly:

            > docker system prune -f
            
3) Edit the "docker-compose.yml" file, uncommenting the following lines:

            #      PHP_XDEBUG: 1
            #      PHP_XDEBUG_DEFAULT_ENABLE: 1
            #      PHP_XDEBUG_REMOTE_HOST: host.docker.internal
            #      PHP_XDEBUG_REMOTE_PORT: 9123
            #      PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
            
by changing them to:

                   PHP_XDEBUG: 1
                   PHP_XDEBUG_DEFAULT_ENABLE: 1
                   PHP_XDEBUG_REMOTE_HOST: host.docker.internal
                   PHP_XDEBUG_REMOTE_PORT: 9123
                   PHP_XDEBUG_REMOTE_CONNECT_BACK: 0

**Note:** A non-standard port of "9123" is used for "xdebug", because the
stardard port of "9000" is exposed by the Docker container, and cannot be
accessed via Remote Containers.

4) Restart the Docker containers:

            > make up

Once debugging is enabled in the Docker container, the VS Code debugger can
be used by doing the following:

1) If necessary, open VS Code (or if VS Code was already running, left-click the
"Reload Window" button). If you get a message about "Configuration files(s)
changed", simply left-click the "Rebuild" button.

2) Left-click the "Run and Debug" icon in the left-sidebar. At the top of 
the sidebar will be a "Listen for Xdebug" dropdown, with a green "Play"
button next to it. Left-click the green "Play" button. The status bar at the
bottom of the VS Code will turn orange.

3) Set breakpoints in the code of interest.

4) In the web browser, perform whatever steps are necessary to trigger the
code with the breakpoints. **Note:** If the breakpoint is not being triggered,
the Drupal cache may need to be cleared.

5) Once the breakpoint is hit, VS Code should display with the line containing
the breakpoint highlighted.

### Remote Containers - Disabling Debugging

Since "xdebug" causes decreased performance, it can be disabled when no longer
needed by doing the following:

1) On the host machine, stop the running Docker containers. The simplest way
to do this is to go to the directory where "drupal-common" is checked out
and run:

            > make down
            
2) Prune the existing containers (this seems to be necessary, as otherwise the
PHP container does not appear to restart properly:

            > docker system prune -f
            
3) Edit the "docker-compose.yml" file, commenting out the following lines:

                   PHP_XDEBUG: 1
                   PHP_XDEBUG_DEFAULT_ENABLE: 1
                   PHP_XDEBUG_REMOTE_HOST: host.docker.internal
                   PHP_XDEBUG_REMOTE_PORT: 9123
                   PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
            
by changing them to:

            #      PHP_XDEBUG: 1
            #      PHP_XDEBUG_DEFAULT_ENABLE: 1
            #      PHP_XDEBUG_REMOTE_HOST: host.docker.internal
            #      PHP_XDEBUG_REMOTE_PORT: 9123
            #      PHP_XDEBUG_REMOTE_CONNECT_BACK: 0

4) Restart the Docker containers:

            > make up

5) If necessary, open VS Code (or if VS Code was already running, left-click the
"Reload Window" button). If you get a message about "Configuration files(s)
changed", simply left-click the "Rebuild" button.

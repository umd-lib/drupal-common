# UMD Drupal Common

## Prerequisites

Install the latest **Composer** <https://getcomposer.org/download/> if not already
installed.

(Recommended) Add the following to your **~/.profile**:

```
alias composer="composer.phar"
```

Add the following to your /etc/hosts (customized if you wish):

```
127.0.0.1		www.docker.localhost
127.0.0.1		portainer.drupal.docker.localhost
127.0.0.1		solr.drupal.docker.localhost
```

## Local Deploy

### Install

1) Create a base directory to hold each of the directories for the project. This
example will use "drupal", but that is arbitrary:

```
> mkdir drupal
> cd drupal
```

2) To deploy locally, clone the main branch:

```
> git clone https://github.com/umd-lib/drupal-common.git common-www
> cd common-www
> git checkout feature/LIBWEB-5305   # (temporary development branch)
```

3) Install the site using Composer. This will download all dependencies and prep
for local deployment.

```
# in common-www/
> composer install
```

4) Switch back to the base ("drupal") directory, and create empty directories
for the Postgres and Solr data (used in the next steps):

```
> cd ..  # back to "drupal"
> mkdir postgres_data
> mkdir solr_data
```

5) Clone the drupal-projects-env repository locally and copy the demo/env file
into your web root (i.e., common-demo) as .env:

```
> git clone git@github.com:umd-lib/drupal-projects-env.git
> cp drupal-projects-env/www/env common-www/.env
> cp drupal-projects-env/www/settings.php common-www/web/sites/default/settings.php
```

6) Edit the "common-www/.env" file:

```
> vi common-www/.env
```

and customize the common-www/.env file for your environment. Specifically,
modify the values for:

* DB_DATA_DIR - the fully-qualified path to the "postgres_data" directory
* SOLR_DATA_DIR - the fully-qualified path to the "solr_data" directory

All other values can stay the same.

7) Get a recent database dump from production (or qa/test, as desired),
and copy to postgres-init:

```
> kubectl config get-contexts prod
> kubectl exec drupal-www-db-0 -- pg_dump -c --if-exists -O -U drupaldb -d drupaldb > common-www/postgres-init/wwwnew.sql
```

8) (Optional - Local development only) Copy drupal-projects-env/services.yml
to common-demo/web/sites/default/:

```
> cp drupal-projects-env/services.yml common-www/web/sites/default/
```

(See additional post-startup development steps in the
"Local Development Configuration" section below).

9) To bring up the site, use docker-compose:

```
> cd common-www
> docker-compose up -d
```

Note that it is very likely you will need to flush cache before the site will
properly appear. Wait a minute before performing this to ensure the stack is
fully started.

```
> make drush cr
```

Unless clearing cache produces errors, the site should be available at:

* <http://www.docker.localhost:18080>

If the site fails to come up, kill the stack with:

```
> docker-compose down
```

And restart without the -d in order to pump logging to standard out.

```
> docker-compose up
```

10) If your work depends on having all images in place, you will want to
generate a dump from production. These can be copied to

common-www/web/sites/default/files/

See instructions below.

Be sure to flush cache after the file copy is complete.

### Local Development Configuration

As mentioned in the optional step above for local development, copy the
drupal-projects-env/services.yml file to common-demo/web/sites/default/.
This will preconfigure your site for dev mode and make theming much easier by
injecting template information directly into the site markup. Note that we don't
want this file included in production (which is a danger given that it isn't
currently overwritten by k8s configuration.)

Under *Configuration | Performance*, make sure both Aggregate CSS/JavaScript
checkboxes are unchecked.

Under *Extend*, you can enable the *Devel* module, which, among other features,
provides a *Clear Cache* link on the toolbar.  You're in Drupal now and will be
using *Clear Cache* with some frequency.

## Additional Help

### Database Dump

To dump SQL data from the Kubernetes cluster:

```
# In "drupal"
> kubectl exec drupal-www-db-0 -- pg_dump -c --if-exists -O -U drupaldb -d drupaldb > common-www/postgres-init/wwwnew.sql
```

Dumping Local Database (generally not needed)

```
> docker exec wwwnew_postgres pg_dump -U drupaluser -O drupaldb > /tmp/dump.sql
```

### Files Copy

To copy files you might need from the server into your local:

```
# In "drupal"
> kubectl exec --stdin --tty drupal-www-0 -- /bin/bash
drupal-www-0> tar -czvf files.tgz web/sites/default/files/
drupal-www-0> exit
> kubectl cp drupal-www-0:files.tgz common-www/files.tgz
```

And then extract the archive into your local's web/sites/default/files/:

```
> cd common-www
> tar -xvzf files.tgz
```

Flush cache after this so that Drupal can regenerate any thumbnails, etc:

```
> make drush cr
> cd ..
```

One thing to note is that a production database is likely optimized for performance
and may need to have CSS/JS aggregation turned off and modules such as devel enabled.

### Solr Integration

To create a local Solr core with through docker-compose, do the following:

```
> docker exec -ti wwwnew_solr sh
> /opt/solr/bin/solr create_core -c drupal -d /opt/solr/server/solr/configsets/search_api_solr_8.x-3.9/conf/
```

### SAML Integration

For UMD users, see the "drupal-common SAML Setup" page in Confluence
(<https://confluence.umd.edu/display/ULDW/drupal-common+SAML+Setup>).

## Tools

### PHP CodeSniffer

PHP CodeSniffer <https://github.com/squizlabs/PHP_CodeSniffer> is a linter for
identifying coding standard violations.

The current configuration is defined in the "phpcs.xml.dist" file.

To check a particular directory or file, run the following commands:

```
> vendor/bin/phpcs <DIRECTORY_OR_FILE>
```

where <DIRECTORY_OR_FILE> is the name of the directory or file. For example,
to check the code in the "web/modules/custom/" directory, run:

```
> vendor/bin/phpcs web/modules/custom/
```

## VS Code Remote Containers

The VS Code "Remote Containers" functionality can be used to quickly stand up
a configured development environment, with development-specific extensions
automatically installed in the container.

For more information abot the "Remote Containers" functionality, see
<https://code.visualstudio.com/docs/remote/containers>.

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

```
> phpcs web/modules/custom/
```

### Remote Containers - Enabling Debugging

The "PHP Debug" extension is added to the Remote Containers configuration by
default. However, because it seems to slow Drupal down significantly, the
"xdebug" tool is not enabled by default.

To enable the "xdebug" tools in the Docker container, do the following:

1) On the host machine, stop the running Docker containers. The simplest way
to do this is to go to the directory where "drupal-common" is checked out
and run:

```
> make down
```

2) Prune the existing containers (this seems to be necessary, as otherwise the
PHP container does not appear to restart properly:

```
> docker system prune -f
```

3) Edit the "docker-compose.yml" file, uncommenting the following lines:

```
#      PHP_XDEBUG: 1
#      PHP_XDEBUG_DEFAULT_ENABLE: 1
#      PHP_XDEBUG_REMOTE_HOST: host.docker.internal
#      PHP_XDEBUG_REMOTE_PORT: 9123
#      PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
```

by changing them to:

```
PHP_XDEBUG: 1
PHP_XDEBUG_DEFAULT_ENABLE: 1
PHP_XDEBUG_REMOTE_HOST: host.docker.internal
PHP_XDEBUG_REMOTE_PORT: 9123
PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
```

**Note:** A non-standard port of "9123" is used for "xdebug", because the
stardard port of "9000" is exposed by the Docker container, and cannot be
accessed via Remote Containers.

4) Restart the Docker containers:

```
> make up
```

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

```
> make down
```

2) Prune the existing containers (this seems to be necessary, as otherwise the
PHP container does not appear to restart properly:

```
> docker system prune -f
```

3) Edit the "docker-compose.yml" file, commenting out the following lines:

```
PHP_XDEBUG: 1
PHP_XDEBUG_DEFAULT_ENABLE: 1
PHP_XDEBUG_REMOTE_HOST: host.docker.internal
PHP_XDEBUG_REMOTE_PORT: 9123
PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
```

by changing them to:

```
#      PHP_XDEBUG: 1
#      PHP_XDEBUG_DEFAULT_ENABLE: 1
#      PHP_XDEBUG_REMOTE_HOST: host.docker.internal
#      PHP_XDEBUG_REMOTE_PORT: 9123
#      PHP_XDEBUG_REMOTE_CONNECT_BACK: 0
```

4) Restart the Docker containers:

```
> make up
```

5) If necessary, open VS Code (or if VS Code was already running, left-click the
"Reload Window" button). If you get a message about "Configuration files(s)
changed", simply left-click the "Rebuild" button.

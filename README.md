# UMD Drupal Common

## Quick Deploy (new site)

* Install the latest **Composer** (https://getcomposer.org/download/) if not already installed.

* (Optional) Add the following to your **~/.profile**: *alias composer="composer.phar"*

Add the following to your /etc/hosts (customized if you wish):

            127.0.0.1       drupal.docker.localhost
            127.0.0.1       portainer.drupal.docker.localhost
            127.0.0.1       solr.drupal.docker.localhost

To deploy locally, clone the develop branch:

            > git clone https://github.com/umd-lib/drupal-common.git common-demo
            > cd common-demo
            > git checkout develop

Install the site using Composer. This will download all dependencies and prep for local deployment.

            # in common-demo
            > composer install

Clone the drupal-projects-env repository locally and copy the demo/env file to your web root as .env:

            > cp drupal-projects/demo/env common-demo/.env

Customize this file for your environment. Specifically, look at the values for:

* PROJECT_NAME
* DB_DATA_DIR
* SOLR_DATA_DIR

All other values can stay the same.

To bring up the site, use docker-compose:

            > cd common-demo
            > docker-compose up -d

After a minute, the site should be available at: http://drupal.docker.localhost:8000 (or wherever you deployed).

For a new site, Drupal will launch the site setup wizard. Use whichever Postgres username/password/database 
combos you had set up in your .env file. Note that you will need to change host under  Advanced Operations in your
database configuration. Change this to simply *postgres*.

Following this, the site should install.

# Solr Integration

To create a local Solr core with through docker-compose, do the following:

            > docker exec -ti -e COLUMNS=80 -e LINES=24 [container]_solr sh
            > /opt/solr/bin/solr create_core -c default -d /opt/solr/server/solr/configsets/search_api_solr_8.x-3.9/conf/

# Access Database

            > docker exec -ti -e COLUMNS=80 -e LINES=24 [container]_db sh

# Dumping Database

            > docker exec [container]_postgres pg_dump -U drupaluser -O drupaldb > /tmp/dump.sql

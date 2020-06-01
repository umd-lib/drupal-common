# UMD Drupal Sandbox

To deploy locally, clone into your htroot.

Meke sure Composer is installed, and if not, install using the instructions at:

https://getcomposer.org/download/

Then, from the htroot (e.g., htroot/demo/), run:

> composer install

Which will download all dependencies including Drupal core and contrib.

Use the following guide to create your database:

https://www.drupal.org/docs/8/install/step-3-create-a-database

If you're installing a new Drupal install, go through the install.php steps by accessing the Drupal site through your browser (e.g., localhost/demo/web).

If you are looking to clone the demo environment, request a database dump and import this into your database without going through the install.php process.

# Solr Integration

To create a local Solr core with through docker-compose, do the following:

docker exec -ti -e COLUMNS=80 -e LINES=24 staff-blog_solr sh

/opt/solr/bin/solr create_core -c default -d /opt/solr/server/solr/configsets/search_api_solr_8.x-3.9/conf/

# Access Database

docker exec -ti -e COLUMNS=80 -e LINES=24 staff-blog_db sh

# UMD Solr Defaults

Before enabling this module, you should make sure you have a Solr core available in your local stack:

```
> docker exec -ti [container]_solr sh
> /opt/solr/bin/solr create_core -c drupal -d /opt/solr/server/solr/configsets/search_api_solr_8.x-3.9/conf/
```

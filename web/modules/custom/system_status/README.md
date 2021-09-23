# System Status module

This module provides a cached system status json endpoint that pulls data from
external system status json service.

## Setup

Enable the "System Status" module in "Manage | Extend"

## Config

The configuration for the upstream System Status JSON API url will be available
in the "Drupal Configuration" page > "SYSTEM" section > "System Status"
(/admin/config/system/system_status), or by running the following "drush"
command:

```
> drush config:set --yes system_status.settings service_url '<URL>'
```

where \<URL> is the URL of the API endpoint. For example, if the URL is
"http://docker.for.mac.localhost:4567/status", the command would be:

```
> drush config:set --yes system_status.settings service_url 'http://docker.for.mac.localhost:4567/status'
```

## JavaScript

This module includes JavaScript for updating the "Systems Status" menu item in
the "utility navigation" menu provided by the "utility_nav" module.

## Testing

The system status endpoint can be tested in the local development environment
using the "system-status" Docker image
(see <https://github.com/umd-lib/system-status/>).

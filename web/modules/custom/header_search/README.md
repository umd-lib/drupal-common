# Header Search module

This module adds the custom header search block that allows launching a search
into one of multiple search destinations based on the target selected using a
drop-down.

## Setup

Enable the "Header Search" module in "Manage | Extend"

After the module is installed, the "Header Search" block can be placed in the
theme's header region.

## Theming

css/header-search.css is provided for theming the header_search custom block.

## Config

The configuration for the search target URLs will be available in the
"Drupal Configuration" page > "SEARCH AND METADATA" section > "Header Search"
(/admin/config/search/header_search).

The "Search Targets" use a YAML format to specify that names and URLs of the
search targets.

The YAML format is the following:

```
<SEARCH_TARGET_NAME>:
  url: '<URL>'
```

where:

* SEARCH_TARGET_NAME - the name of the option in the search dialog
    (i.e., "All", "UMD Catalog", "WorldCat")
* URL - the URL to use in performing the search. URLs are expected to end with

Sample configuration:

```
All:
    url: 'https://searchnew.lib.umd.edu/results?search&query='
'UMD Catalog':
    url: 'https://catalog.umd.edu/cgi-bin/direct?searchtype=F1_WRD&base=CP&search=&searchrequest='
WorldCat:
    url: 'https://umaryland.on.worldcat.org/search?databaseList=&umdlib=&queryString='
```

Provides three search option, "All", "UMD Catalog", and "WorldCat".

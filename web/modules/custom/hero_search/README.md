# Hero Search module

This module adds the configurable custom search block for use in home page.

## Setup

Enable the "Hero Search" module in "Manage | Extend"

After the module is installed, the "Hero Search" block can be placed in the home
page [Hero Block](../hero_block) region.

## Theming

css/hero-search.css is provided for theming the hero_search custom block.

## Config

The configuration for the search target URLs will be available in the
"Drupal Configuration" page > "SEARCH AND METADATA" section > "Hero Search"
(/admin/config/search/hero_search).

The "Search Targets" use a YAML format to specify that names and URLs of the
search targets, as well as an optional "alternate" search target.

The YAML format is the following:

```
<SEARCH_TARGET_NAME>:
  url: '<URL>'
  alternate: { url: '<ALTERNATE_URL>', text: 'ALTERNATE_LINK_TEXT', title: 'ALTERNATE_TOOLTIP_TEXT' }
  placeholder: '<PLACEHOLDER_TEXT>'
```

where:

* SEARCH_TARGET_NAME - the name of the option in the search dialog
    (i.e., "All", "UMD Catalog", "WorldCat")
* URL - the URL to use in performing the search. URLs are expected to end with
    a "=" to allow the query term to be appended.
* ALTERNATE_URL - the URL to use as an "alternate" search, such as a direct link
    to a vendor search page.
* ALTERNATE_LINK_TEXT - The text to display for the link
* ALTERNATE_TOOLTIP_TEXT - The tooltip to display when hovering over the link.
* PLACEHOLDER_TEXT - The placeholder text to display in the search textfield

Sample configuration:

```
All:
    url: 'https://searchnew.lib.umd.edu/results?search&query='
    placeholder: 'Find books, journals, articles, media & more'
'UMD Catalog':
    url: 'https://catalog.umd.edu/cgi-bin/direct?searchtype=F1_WRD&base=CP&search=&searchrequest='
    placeholder: 'Search for items held at UMD'
WorldCat:
    url: 'https://umaryland.on.worldcat.org/search?databaseList=&umdlib=&queryString='
    alternate: { url: 'https://umaryland.on.worldcat.org/advancedsearch', text: 'Advanced Search', title: tooltip }
    placeholder: 'Search books, articles, journals, and website'
```

Provides three search option, "All", "UMD Catalog", and "WorldCat".

When the "WorldCat" option is selected in the GUI, the "alternate" link will
also be displayed.

The "placeholder" value is optional -- if not provided, default placeholder text
will be used.

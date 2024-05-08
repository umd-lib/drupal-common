# Bento module

This module provides various blocks for bento searchers and utilities.

## Setup

Enable the "Bento" module in "Manage | Extend"

After the module is installed, place various Bento block into the bento regions.

## Theming

css/form_util.css is provided for theming the bento blocks.

templates/ provides a range of relevant templates.

Additional styles and templates are included with the umd_terp theme.

## Config

The configuration for most blocks is a combination of strings and YAML.

Use the following as references for each block type:

### Bento: Looking for More

Provides a single link providing access to the external search. If there is
a query parameter, the *Looking More URL* is used. If the query is missing
or empty, *Looking More Empty URL* is used.

- Looking More Heading: String
- Looking More Text: String
- Looking More URL: String with %placeholder% to indicate query term placement
- Looking More Empty Text: String to display if query is empty or missing
- Looking More Empty URL: URL to use if query is empty or missing

### Bento: Resource Types

Provides a series of links to search results for various resource types.
Use the %placeholder% to indicate where the query term should go for the url
field. For the url_empty field, do not indicate a placeholder.

- Block Header: String
- Resource Types: YAML (format example below)

```
<Resource Name 1>:
  url: '<URL>'
  url_empty: '<URL>'
<Resource Name 2>:
  url: '<URL>'
  url_empty: '<URL>'
```

### Bento: Search Categories

Intended to provide an array of categorized anchor links to jump to on-page results.
No url_empty is needed for this block as the content will not change depending on
query.

- Block Header: String
- Search Categories: YAML (format example below)

```
<Category 1>:
    - { name: <String>, url: '#<ID>' }
    - { name: <String>, url: '#<ID>' }
<Category 2>:
    - { name: "<String>", url: '#<ID>' }
    - { name: '<String>', url: '#<ID>' }
    - { name: '<String>', url: '#<ID>' }
```

### Bento: Other Resources

Intended as a search target listing other resources. The url field should include
a %placeholder% to indicate where the query should go. Include a url_empty field
to provide a link without a replaced placeholder.

- Block Header: String
- Other Resources: YAML (format example below)

```
'<Site Name>':
    url: '<URL>'
    url_empty: '<URL>'
    type: <String>
    description: '<String>'
'<Site Name>':
    url: '<URL>'
    url_empty: '<URL>'
    type: <String> 
    description: '<String>'
```

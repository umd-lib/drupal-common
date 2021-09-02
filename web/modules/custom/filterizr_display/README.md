# Filterizr Display 

## Overview

A Drupal Views Display plugin which utilizes the Filterizr library:

<https://yiotis.net/filterizr/#/>

To use this, select the Filterizr display type for your view and configure
by setting one of the two options. Note that OPTION 2 settings will override OPTION 1.

This module requires you to include a *Custom Text* field for wrapping
your results in the following HTML (including tokens for demo purposes):

```
<div class="filtr-item" data-category="{{ field_taxonomy_tid }}" data-sort="{{ title }}">
</div>
```

All other fields should be marked as hidden and referenced in this
*Custom Text* field as tokens.

In theory, any combination of fields and markup could be wrapped in this div.

This module also only filters on taxonomy term so be sure your content type 
includes a Vocabulary. Select that same vocabulary in the Filterizr display
type settings or reference the field in use.

TODO: Add a third option for filtering by arbitrary strings harvested from a field.

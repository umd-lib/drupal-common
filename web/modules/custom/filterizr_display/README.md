# Filterizr Display 

## Overview

A Drupal Views Display plugin which utilizes the Filterizr library:

<https://yiotis.net/filterizr/#/>

To use this, select the Filterizr display type for your view and configure
by setting one of the two options. Note that OPTION 2 settings will override OPTION 1.

This module requires you to include a *Custom Text* field for wrapping
your results in the following HTML:

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

It's worth noting that Drupal doesn't automatically store a taxonomy term's parent
with the node.

For example, if (in DST > SSDR) only SSDR is selected, only SSDR will be stored.
You could conceviably use the *Parent Term* Views field, but this has not been tested.

If you need the term's complete hierarchy attached to a node, consider using
*Client-side hierarchical select* in the Content Type's *Form Display* configuration
and ticking *Save Lineage* or just encouraging users to include the complete hierarchy.

TODO: Add a third option for filtering by arbitrary strings harvested from a field.


3) Install the site using Composer. This will download all dependencies and prep

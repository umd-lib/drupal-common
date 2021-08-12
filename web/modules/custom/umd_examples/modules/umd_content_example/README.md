# Default Content Example

This module could be used for generating and distributing demo content or pre-packaged content for production.

The .info file has been annotated with setup information.

Generate your content by adding content arrays to the .info file. E.g., 

```
default_content:
    node:
      - c9a89616-7057-4971-8337-555e425ed782
      - b6d6d9fd-4f28-4918-b100-ffcfb15c9374
    file:
      - 59674274-f1f5-4d6a-be00-fecedfde6534
      - 0fab901d-36ba-4bfd-9b00-d6617ffc2f1f
    media:
      - ee63912a-6276-4081-93af-63ca66285594
      - bcb3c719-e266-45c1-8b90-8f630f86dcc7
    menu_link_content:
      - 9fbb684c-156d-49d6-b24b-755501b434e6
      - 19f38567-4051-4682-bf00-a4f19de48a01
    block_content:
      - af171e09-fcb2-4d93-a94d-77dc61aab213
      - a608987c-1b74-442b-b900-a54f40cda661
```

And then, from Drupal root, perform the export with:

```
> drush dcem umd_content_example
```

If running in the drupal-common stack, use:

```
> make drush dcem umd_content_example
```

An easy means for identifying UUIDs would be to create a View and expose the UUID field.

You can also identify the UUID using the Devel module and checking the *Devel* tab in the entity to be exported.

More information about the module here:

https://www.drupal.org/project/default_content

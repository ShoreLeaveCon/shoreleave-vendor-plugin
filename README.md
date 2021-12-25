# Shore Leave Vendor List

This WordPress plugin provides two functions to the Shore Leave web site

- A "vendor" custom post type.
- A "vendor-list" shortcode to display the list of vendors on a page.

## Vendor Custom Post Type

This appears on the administrative menu as "Vendors".

Each vendor entry consists of the fields:
- Name: The vendor's name. This is a required field.
- Website: A link to the vendor's website.

Individual vendors do not have unique pages on the web site.

## vendor-list shortcode

This may be placed on any page by inserting a shortcode block in the page content and specifying `vendor-list' as the code to use.

Alternatively, you may insert the text `[vendor-list]` into the page content using the Code-editor view.

## Future

The next version will expand the vendor post type to include table assignments, and an option on the short-code for whether to include those assignments.
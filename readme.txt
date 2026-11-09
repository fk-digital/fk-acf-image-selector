=== FK ACF Image Selector ===
Contributors: futurekings
Tags: acf, advanced custom fields, image select, field type, swatches
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An ACF field type for picking one of a set of images, shown as thumbnails or round swatches. Returns the value you gave the chosen image.

== Description ==

FK ACF Image Selector adds an **Image Selector** field type to Advanced Custom Fields.

Add a set of images to the field, giving each one a name and a value. Editors see the images and click the one they want. The field returns the chosen image's value, which you can use directly as a CSS class name.

A typical use is a colour scheme picker for a banner. Upload a preview of each colour combination, give each a value like `navy-gold`, and style `.banner--navy-gold` in your theme.

**Features**

* Add choices from the media library, each with a name and a value. Drag to reorder.
* Values are generated from names, and can only contain letters, numbers, hyphens and underscores, so they're safe to use as class names.
* Two display styles: thumbnails at their own shape, or round swatches like the block editor's colour picker.
* Return the value, the name, or both as an array.
* Optional "None" choice.
* Works in ACF blocks, repeaters, flexible content and options pages, with required-field validation and conditional logic.

**Requirements**

Advanced Custom Fields or ACF Pro, version 6.0 or later.

Built by [FutureKings](https://futurekings.co.uk).

== Installation ==

1. Make sure Advanced Custom Fields or ACF Pro 6.0+ is active.
2. Upload the `fk-acf-image-selector` folder to `/wp-content/plugins/`, or upload the zip from Plugins › Add New › Upload Plugin.
3. Activate the plugin.
4. Edit a field group, add a field and choose **Image Selector** as the field type.

== Frequently Asked Questions ==

= What does get_field() return? =

The chosen image's value, as a string. It returns an empty string if nothing has been chosen. You can switch the field's Return Value to the name, or to an array of value, name and attachment ID.

= Can I show the choices as circles? =

Yes. In the field's Presentation settings, set Display Style to Circles.

= Does it work with ACF local JSON? =

Yes, but the choices store media library attachment IDs. If you sync a field group to another site, you'll need to pick the images again there unless both sites share the same media library.

= Does it store any data? =

No. It creates no tables or options. Field settings and values are stored by ACF as usual.

== Changelog ==

= 0.0.1 =
* Initial release.

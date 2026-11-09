# FK ACF Image Selector

[![Coding standards](https://github.com/fk-digital/fk-acf-image-selector/actions/workflows/coding-standards.yml/badge.svg?branch=main)](https://github.com/fk-digital/fk-acf-image-selector/actions/workflows/coding-standards.yml)

An **Image Selector** field type for [Advanced Custom Fields](https://www.advancedcustomfields.com/).

You add a set of images to the field, giving each one a **name** and a **value**. Editors see the images as thumbnails or round swatches and click the one they want. The field returns the value of the chosen image.

It was built for choosing a colour combination for a banner. Upload a preview of each combination and give each one a value like `navy-gold`. In your template, use the value as a class name and style each class in your CSS.

Built and maintained by [FutureKings](https://futurekings.co.uk).

## Features

- **Choices with images.** Add images from the media library. Give each a name (shown to editors) and a value (returned to your templates). Drag rows to reorder them.
- **Values ready to use as class names.** Typing a name fills in a matching value, which you can change. Values may only contain letters, numbers, hyphens and underscores.
- **Two display styles**, set per field under **Presentation › Display Style**:
  - **Thumbnails** show each image at its own shape, with its name underneath.
  - **Circles** show round swatches, like the colour picker in the block editor. The name appears as a tooltip.
- **Return value.** Return the **Value** (the default), the **Name**, or **Both** as an array.
- **Allow None.** Optionally add a "None" choice so editors can clear their selection.
- **Works wherever ACF fields do.** That includes post and term screens, options pages, repeaters, flexible content and ACF blocks. Required-field validation and conditional logic both work.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- [Advanced Custom Fields](https://wordpress.org/plugins/advanced-custom-fields/) or ACF Pro, version 6.0 or later

If ACF is missing or too old, the plugin shows an admin notice and does nothing else.

## Installation

### From a release zip

1. Download the latest `fk-acf-image-selector.zip` from the [Releases](../../releases) page.
2. Go to **Plugins › Add New › Upload Plugin** and upload the zip.
3. Activate it.

### From source

```bash
cd wp-content/plugins
git clone https://github.com/fk-digital/fk-acf-image-selector.git fk-acf-image-selector
wp plugin activate fk-acf-image-selector
```

## Usage

### Setting up the field

1. Edit a field group and add a field. Choose **Image Selector** (under **Choice**) as the field type.
2. Under **Choices**, click **Add Images** and pick one or more images. Each image becomes a choice. Its name defaults to the image title, and its value is generated from the name.
3. Change any names and values you need to. Drag the handle to reorder choices, click an image to swap it, or click **×** to remove a choice.
4. Optionally:
   - **General › Default Value**: the value selected before an editor has chosen anything.
   - **General › Return Value**: Value, Name or Both (array).
   - **Validation › Allow None**: adds a "None" choice.
   - **Presentation › Display Style**: Thumbnails or Circles.
   - **Presentation › Preview Size**: the image size used for the previews. The default is Medium. Use an uncropped size if you want thumbnails to keep their shape.

### In your templates

With the default **Value** return format, `get_field()` returns the chosen value as a string, or `''` if nothing has been chosen:

```php
<?php $scheme = get_field( 'banner_colours' ); ?>
<section class="banner<?php echo $scheme ? ' banner--' . esc_attr( $scheme ) : ''; ?>">
	…
</section>
```

```css
.banner--navy-gold { background: #14213d; color: #fca311; }
.banner--white-red { background: #fff;    color: #c1121f; }
```

With **Both (Array)**, it returns:

```php
array(
	'value' => 'navy-gold',
	'label' => 'Navy & Gold',
	'image' => 123, // Attachment ID.
)
```

or `null` if nothing has been chosen. With **Name**, it returns the name string.

### Registering the field in PHP

```php
acf_add_local_field_group( array(
	'key'      => 'group_banner',
	'title'    => 'Banner',
	'fields'   => array(
		array(
			'key'           => 'field_banner_colours',
			'name'          => 'banner_colours',
			'label'         => 'Colours',
			'type'          => 'fk_image_selector',
			'presentation'  => 'circle',         // Or 'thumbnail'.
			'preview_size'  => 'thumbnail',
			'return_format' => 'value',          // Or 'label' / 'array'.
			'allow_null'    => 0,
			'default_value' => 'navy-gold',
			'image_choices' => array(
				array( 'image' => 123, 'label' => 'Navy & Gold', 'value' => 'navy-gold' ),
				array( 'image' => 124, 'label' => 'White & Red', 'value' => 'white-red' ),
			),
		),
	),
	'location' => array( /* … */ ),
) );
```

## Notes

- **Images are media library attachments.** Choices store attachment IDs. If you sync field groups between sites with ACF local JSON, the IDs won't match on the other site, so you'll need to re-pick the images there or use the same media library.
- **Only configured values are saved.** If you remove a choice, posts that used it keep the old value until they're next saved. `get_field()` still returns that value, so remove its CSS when you're sure it's unused.
- **No data of its own.** The plugin creates no tables or options. Everything lives in ACF's field settings and post meta.

## Development

The plugin has no build step. PHP code lives in `includes/`, scripts in `assets/js/` and styles in `assets/css/`.

| File | Purpose |
| --- | --- |
| `includes/class-fk-acf-image-selector.php` | Checks ACF is available and registers the field type. |
| `includes/class-fk-acf-image-selector-field.php` | The field type: settings, rendering, saving and formatting values. |
| `assets/js/field-settings.js` | The choices builder in the field group editor. |
| `assets/js/field.js` | Registers the field with ACF's JS API, for `field.val()` and conditional logic. |
| `assets/css/field.css` | Thumbnail and circle styles, and the choices builder. |

Coding standards are checked with [WPCS](https://github.com/WordPress/WordPress-Coding-Standards):

```bash
composer install
composer lint        # check
composer lint:fix    # auto-fix what can be fixed
```

The same check runs on GitHub Actions ([`.github/workflows/coding-standards.yml`](.github/workflows/coding-standards.yml)) for every pull request and every push to `main`. Any violations are shown inline on the pull request diff.

To regenerate the translation template:

```bash
wp i18n make-pot . languages/fk-acf-image-selector.pot
```

### Releasing

1. Bump the version in `fk-acf-image-selector.php` (both the header and `FK_ACF_IMAGE_SELECTOR_VERSION`) and the `Stable tag` in `readme.txt`.
2. Add an entry to `CHANGELOG.md` and the changelog in `readme.txt`.
3. Tag the release (for example `git tag v0.0.2`) and push the tag.
4. Build the distributable zip. Development files are excluded by `.gitattributes`:

   ```bash
   git archive --format=zip --prefix=fk-acf-image-selector/ -o fk-acf-image-selector.zip HEAD
   ```

5. Attach the zip to a GitHub release.

## License

[GPL-2.0-or-later](LICENSE). © FutureKings.

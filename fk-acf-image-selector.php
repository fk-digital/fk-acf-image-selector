<?php
/**
 * Plugin Name:       FK ACF Image Selector
 * Plugin URI:        https://futurekings.co.uk
 * Description:       Adds an "Image Selector" field type to Advanced Custom Fields. Editors pick from a set of images, shown as thumbnails or round swatches, and the field returns the value you gave that image.
 * Version:           0.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            FutureKings
 * Author URI:        https://futurekings.co.uk
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       fk-acf-image-selector
 * Domain Path:       /languages
 * Update URI:        https://futurekings.co.uk
 *
 * @package FK_ACF_Image_Selector
 */

/*
 * FK ACF Image Selector is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or (at your
 * option) any later version.
 *
 * FK ACF Image Selector is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FK_ACF_IMAGE_SELECTOR_VERSION', '0.0.1' );
define( 'FK_ACF_IMAGE_SELECTOR_FILE', __FILE__ );
define( 'FK_ACF_IMAGE_SELECTOR_DIR', plugin_dir_path( __FILE__ ) );
define( 'FK_ACF_IMAGE_SELECTOR_URL', plugin_dir_url( __FILE__ ) );

/**
 * Minimum ACF version. 6.0 introduced the tabbed field settings (General /
 * Validation / Presentation) that the field type's settings are split across.
 */
define( 'FK_ACF_IMAGE_SELECTOR_MIN_ACF', '6.0' );

require_once FK_ACF_IMAGE_SELECTOR_DIR . 'includes/class-fk-acf-image-selector.php';

/*
 * There's deliberately no "Requires Plugins" header: it only accepts exact
 * wordpress.org slugs, so it would block activation for anyone running ACF
 * Pro (advanced-custom-fields-pro). The dependency is checked at runtime
 * instead, once every plugin has loaded.
 */
add_action( 'plugins_loaded', array( 'FK_ACF_Image_Selector', 'instance' ) );

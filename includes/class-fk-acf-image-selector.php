<?php
/**
 * Main plugin class.
 *
 * @package FK_ACF_Image_Selector
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checks ACF is available and registers the Image Selector field type.
 */
final class FK_ACF_Image_Selector {

	/**
	 * Singleton instance.
	 *
	 * @var FK_ACF_Image_Selector|null
	 */
	private static $instance = null;

	/**
	 * Returns the shared instance, creating (and hooking) it on first call.
	 *
	 * @return FK_ACF_Image_Selector
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Registers all WordPress hooks.
	 */
	private function hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		if ( ! $this->acf_is_supported() ) {
			add_action( 'admin_notices', array( $this, 'render_missing_acf_notice' ) );
			return;
		}

		add_action( 'acf/include_field_types', array( $this, 'register_field_type' ) );
	}

	/**
	 * Loads translations from the plugin's /languages directory.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'fk-acf-image-selector', false, dirname( plugin_basename( FK_ACF_IMAGE_SELECTOR_FILE ) ) . '/languages' );
	}

	/**
	 * Whether a compatible version of ACF (free or Pro) is active.
	 *
	 * @return bool
	 */
	private function acf_is_supported() {
		if ( ! class_exists( 'ACF' ) || ! function_exists( 'acf_get_setting' ) ) {
			return false;
		}

		return version_compare( (string) acf_get_setting( 'version' ), FK_ACF_IMAGE_SELECTOR_MIN_ACF, '>=' );
	}

	/**
	 * Loads and registers the field type. The class extends acf_field, so it
	 * can only be loaded once ACF is ready for field types.
	 */
	public function register_field_type() {
		require_once FK_ACF_IMAGE_SELECTOR_DIR . 'includes/class-fk-acf-image-selector-field.php';

		acf_register_field_type( 'FK_ACF_Image_Selector_Field' );
	}

	/**
	 * Tells admins the plugin is doing nothing because ACF is missing or too old.
	 */
	public function render_missing_acf_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: minimum ACF version number. */
					__( 'FK ACF Image Selector needs Advanced Custom Fields (or ACF Pro) %s or later to be installed and active.', 'fk-acf-image-selector' ),
					FK_ACF_IMAGE_SELECTOR_MIN_ACF
				)
			)
		);
	}
}

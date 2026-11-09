<?php
/**
 * The Image Selector ACF field type.
 *
 * @package FK_ACF_Image_Selector
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lets editors pick one of a fixed set of images, each with a name and a
 * value. The field returns the chosen value - handy as a CSS class name.
 *
 * Choices are stored on the field as a list of
 * `array( 'image' => attachment ID, 'label' => string, 'value' => string )`.
 * Fields registered in PHP or local JSON can use the same shape.
 */
class FK_ACF_Image_Selector_Field extends acf_field {

	/**
	 * Script/style handle prefix.
	 */
	const HANDLE = 'fk-acf-image-selector';

	/**
	 * Sets up the field type's name, label and default settings.
	 */
	public function initialize() {
		$this->name        = 'fk_image_selector';
		$this->label       = __( 'Image Selector', 'fk-acf-image-selector' );
		$this->category    = 'choice';
		$this->description = __( 'Pick one option from a set of images, shown as thumbnails or round swatches. Returns the value given to the chosen image.', 'fk-acf-image-selector' );
		$this->defaults    = array(
			'image_choices' => array(),
			'default_value' => '',
			'return_format' => 'value',
			'allow_null'    => 0,
			'presentation'  => 'thumbnail',
			'preview_size'  => 'medium',
		);
	}

	/*
	 * ------------------------------------------------------------------
	 * Field settings (field group editor)
	 * ------------------------------------------------------------------
	 */

	/**
	 * Renders the General tab settings.
	 *
	 * The choices are edited with a small builder (assets/js/field-settings.js)
	 * that reads and writes JSON in a hidden textarea. ACF field settings are
	 * flat inputs, so the textarea carries the whole list and update_field()
	 * turns it back into an array on save.
	 *
	 * @param array $field The field being edited.
	 */
	public function render_field_settings( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Choices', 'fk-acf-image-selector' ),
				'instructions' => __( 'Add an image for each option. The name is shown to editors. The value is what the field returns, for example a CSS class name, so it can only contain letters, numbers, hyphens and underscores.', 'fk-acf-image-selector' ),
				'type'         => 'textarea',
				'name'         => 'image_choices',
				'rows'         => 3,
				'value'        => wp_json_encode( $this->choices_for_builder( $field['image_choices'] ) ),
			)
		);

		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Default Value', 'fk-acf-image-selector' ),
				'instructions' => __( 'The value selected when nothing has been chosen yet.', 'fk-acf-image-selector' ),
				'type'         => 'text',
				'name'         => 'default_value',
			)
		);

		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Return Value', 'fk-acf-image-selector' ),
				'instructions' => __( 'What the field returns in your templates.', 'fk-acf-image-selector' ),
				'type'         => 'radio',
				'name'         => 'return_format',
				'layout'       => 'horizontal',
				'choices'      => array(
					'value' => __( 'Value', 'fk-acf-image-selector' ),
					'label' => __( 'Name', 'fk-acf-image-selector' ),
					'array' => __( 'Both (Array)', 'fk-acf-image-selector' ),
				),
			)
		);
	}

	/**
	 * Renders the Validation tab settings.
	 *
	 * @param array $field The field being edited.
	 */
	public function render_field_validation_settings( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Allow None', 'fk-acf-image-selector' ),
				'instructions' => __( 'Adds a "None" option so the editor can clear their choice.', 'fk-acf-image-selector' ),
				'type'         => 'true_false',
				'name'         => 'allow_null',
				'ui'           => 1,
			)
		);
	}

	/**
	 * Renders the Presentation tab settings.
	 *
	 * @param array $field The field being edited.
	 */
	public function render_field_presentation_settings( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Display Style', 'fk-acf-image-selector' ),
				'instructions' => __( 'Thumbnails show each image at its own shape. Circles show round swatches, like the colour picker in the block editor.', 'fk-acf-image-selector' ),
				'type'         => 'radio',
				'name'         => 'presentation',
				'layout'       => 'horizontal',
				'choices'      => array(
					'thumbnail' => __( 'Thumbnails', 'fk-acf-image-selector' ),
					'circle'    => __( 'Circles', 'fk-acf-image-selector' ),
				),
			)
		);

		acf_render_field_setting(
			$field,
			array(
				'label'        => __( 'Preview Size', 'fk-acf-image-selector' ),
				'instructions' => __( 'The image size used for the previews. Pick one that isn\'t cropped if you want thumbnails to keep their shape.', 'fk-acf-image-selector' ),
				'type'         => 'select',
				'name'         => 'preview_size',
				'choices'      => acf_get_image_sizes(),
			)
		);
	}

	/**
	 * Cleans the choices before the field settings are saved.
	 *
	 * @param array $field The field settings about to be saved.
	 * @return array
	 */
	public function update_field( $field ) {
		$field['image_choices'] = $this->normalize_choices( isset( $field['image_choices'] ) ? $field['image_choices'] : array() );
		$field['default_value'] = sanitize_html_class( (string) $field['default_value'] );
		$field['presentation']  = 'circle' === $field['presentation'] ? 'circle' : 'thumbnail';

		if ( ! in_array( $field['return_format'], array( 'value', 'label', 'array' ), true ) ) {
			$field['return_format'] = 'value';
		}

		return $field;
	}

	/*
	 * ------------------------------------------------------------------
	 * Field input (edit screens)
	 * ------------------------------------------------------------------
	 */

	/**
	 * Renders the choices as a group of image radio buttons.
	 *
	 * The radios are real inputs, hidden visually, so saving, required-field
	 * validation and keyboard navigation all work the way they do for ACF's
	 * own Radio Button field.
	 *
	 * @param array $field The field being rendered.
	 */
	public function render_field( $field ) {
		$choices      = $this->get_choices( $field );
		$presentation = 'circle' === $field['presentation'] ? 'circle' : 'thumbnail';
		$size         = $field['preview_size'] ? $field['preview_size'] : 'medium';
		$current      = is_scalar( $field['value'] ) ? (string) $field['value'] : '';

		if ( empty( $choices ) ) {
			printf(
				'<p class="description">%s</p>',
				esc_html__( 'No images have been added to this field yet.', 'fk-acf-image-selector' )
			);
			return;
		}

		if ( $field['allow_null'] ) {
			array_unshift(
				$choices,
				array(
					'image' => 0,
					'label' => __( 'None', 'fk-acf-image-selector' ),
					'value' => '',
				)
			);
		}

		printf(
			'<div class="fk-ais fk-ais--%s">',
			esc_attr( $presentation )
		);

		// Posts an empty value when nothing is ticked, so the field can be validated and cleared.
		printf(
			'<input type="hidden" name="%s" value="" />',
			esc_attr( $field['name'] )
		);

		echo '<ul class="fk-ais__list">';

		foreach ( $choices as $i => $choice ) {
			$input_id = $field['id'] . '-' . $i;
			$is_none  = '' === $choice['value'];
			$image    = $choice['image'] ? wp_get_attachment_image(
				$choice['image'],
				$size,
				false,
				array(
					'class'   => 'fk-ais__img',
					'alt'     => '',
					'loading' => false,
				)
			) : '';

			$swatch_class = 'fk-ais__swatch';
			if ( $is_none ) {
				$swatch_class .= ' fk-ais__swatch--none';
			} elseif ( ! $image ) {
				$swatch_class .= ' fk-ais__swatch--missing';
			}

			printf(
				'<li class="fk-ais__item"><label class="fk-ais__choice" for="%1$s" title="%2$s">'
				. '<input type="radio" class="fk-ais__input" id="%1$s" name="%3$s" value="%4$s"%5$s />'
				. '<span class="%6$s" aria-hidden="true">%7$s</span>'
				. '<span class="fk-ais__label">%8$s</span>'
				. '</label></li>',
				esc_attr( $input_id ),
				esc_attr( $choice['label'] ),
				esc_attr( $field['name'] ),
				esc_attr( $choice['value'] ),
				checked( $current, $choice['value'], false ),
				esc_attr( $swatch_class ),
				$image, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Markup from wp_get_attachment_image().
				esc_html( $choice['label'] )
			);
		}

		echo '</ul></div>';
	}

	/**
	 * Enqueues the edit-screen assets.
	 */
	public function input_admin_enqueue_scripts() {
		wp_enqueue_style( self::HANDLE, FK_ACF_IMAGE_SELECTOR_URL . 'assets/css/field.css', array(), FK_ACF_IMAGE_SELECTOR_VERSION );
		wp_enqueue_script( self::HANDLE, FK_ACF_IMAGE_SELECTOR_URL . 'assets/js/field.js', array( 'acf-input' ), FK_ACF_IMAGE_SELECTOR_VERSION, true );
	}

	/**
	 * Enqueues the field group editor assets (the choices builder).
	 */
	public function field_group_admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style( self::HANDLE, FK_ACF_IMAGE_SELECTOR_URL . 'assets/css/field.css', array(), FK_ACF_IMAGE_SELECTOR_VERSION );
		wp_enqueue_script(
			self::HANDLE . '-settings',
			FK_ACF_IMAGE_SELECTOR_URL . 'assets/js/field-settings.js',
			array( 'jquery', 'jquery-ui-sortable', 'acf-field-group' ),
			FK_ACF_IMAGE_SELECTOR_VERSION,
			true
		);
		wp_localize_script(
			self::HANDLE . '-settings',
			'fkAcfImageSelector',
			array(
				'addImages'   => __( 'Add Images', 'fk-acf-image-selector' ),
				'chooseImage' => __( 'Choose Image', 'fk-acf-image-selector' ),
				'useImages'   => __( 'Use these images', 'fk-acf-image-selector' ),
				'useImage'    => __( 'Use this image', 'fk-acf-image-selector' ),
				'changeImage' => __( 'Change image', 'fk-acf-image-selector' ),
				'name'        => __( 'Name', 'fk-acf-image-selector' ),
				'value'       => __( 'Value', 'fk-acf-image-selector' ),
				'remove'      => __( 'Remove', 'fk-acf-image-selector' ),
				'reorder'     => __( 'Drag to reorder', 'fk-acf-image-selector' ),
				'empty'       => __( 'No images yet. Click "Add Images" to choose some from the media library.', 'fk-acf-image-selector' ),
			)
		);
	}

	/*
	 * ------------------------------------------------------------------
	 * Values
	 * ------------------------------------------------------------------
	 */

	/**
	 * Only saves values that match one of the field's choices.
	 *
	 * @param mixed      $value   The submitted value.
	 * @param int|string $post_id The post ID the value belongs to.
	 * @param array      $field   The field settings.
	 * @return string
	 */
	public function update_value( $value, $post_id, $field ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		return in_array( $value, wp_list_pluck( $this->get_choices( $field ), 'value' ), true ) ? $value : '';
	}

	/**
	 * Formats the value for get_field() / the_field().
	 *
	 * @param mixed      $value   The stored value.
	 * @param int|string $post_id The post ID the value belongs to.
	 * @param array      $field   The field settings.
	 * @return string|array|null The value or name string ('' when nothing is
	 *                           chosen), or for the array format
	 *                           { value, label, image } or null.
	 */
	public function format_value( $value, $post_id, $field ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		if ( 'value' === $field['return_format'] ) {
			return $value;
		}

		if ( '' === $value ) {
			return 'array' === $field['return_format'] ? null : '';
		}

		$choice = array(
			'value' => $value,
			'label' => $value,
			'image' => 0,
		);

		foreach ( $this->get_choices( $field ) as $candidate ) {
			if ( $candidate['value'] === $value ) {
				$choice = $candidate;
				break;
			}
		}

		return 'label' === $field['return_format'] ? $choice['label'] : $choice;
	}

	/*
	 * ------------------------------------------------------------------
	 * Helpers
	 * ------------------------------------------------------------------
	 */

	/**
	 * Returns the field's choices in their normalised form.
	 *
	 * @param array $field The field settings.
	 * @return array[]
	 */
	private function get_choices( $field ) {
		return $this->normalize_choices( isset( $field['image_choices'] ) ? $field['image_choices'] : array() );
	}

	/**
	 * Cleans up a list of choices, from either a JSON string (as posted by the
	 * builder) or an array (as stored, or registered in PHP / local JSON).
	 *
	 * Choices without a value are dropped, values are made safe to use as a
	 * CSS class, and only the first choice with any given value is kept.
	 *
	 * @param string|array $raw The choices.
	 * @return array[]
	 */
	private function normalize_choices( $raw ) {
		if ( is_string( $raw ) ) {
			$decoded = json_decode( $raw, true );
			$raw     = is_array( $decoded ) ? $decoded : array();
		}

		if ( ! is_array( $raw ) ) {
			return array();
		}

		$choices = array();
		$seen    = array();

		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			$value = sanitize_html_class( isset( $row['value'] ) ? (string) $row['value'] : '' );
			if ( '' === $value || isset( $seen[ $value ] ) ) {
				continue;
			}

			$label = isset( $row['label'] ) ? sanitize_text_field( (string) $row['label'] ) : '';

			$seen[ $value ] = true;
			$choices[]      = array(
				'image' => isset( $row['image'] ) ? absint( $row['image'] ) : 0,
				'label' => '' === $label ? $value : $label,
				'value' => $value,
			);
		}

		return $choices;
	}

	/**
	 * Adds a preview URL to each choice for the builder to display. The URL is
	 * dropped again on save.
	 *
	 * @param string|array $raw The stored choices.
	 * @return array[]
	 */
	private function choices_for_builder( $raw ) {
		return array_map(
			static function ( $choice ) {
				$choice['url'] = $choice['image'] ? (string) wp_get_attachment_image_url( $choice['image'], 'thumbnail' ) : '';
				return $choice;
			},
			$this->normalize_choices( $raw )
		);
	}
}

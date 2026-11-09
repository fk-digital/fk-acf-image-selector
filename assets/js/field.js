/**
 * Image Selector field - edit screens.
 *
 * The field is a group of native radio buttons, so selection, saving and
 * keyboard support need no JS. This registers the field type with ACF's JS
 * API so field.val() and conditional logic read the chosen value.
 */
( function ( $ ) {
	'use strict';

	if ( typeof acf === 'undefined' ) {
		return;
	}

	var Field = acf.Field.extend( {
		type: 'fk_image_selector',

		$control: function () {
			return this.$( '.fk-ais' );
		},

		$input: function () {
			return this.$( '.fk-ais__input:checked' );
		},

		getValue: function () {
			var $input = this.$input();
			return $input.length ? $input.val() : '';
		},

		setValue: function ( value ) {
			value = value === null || value === undefined ? '' : String( value );

			this.$( '.fk-ais__input' )
				.filter( function () {
					return this.value === value;
				} )
				.prop( 'checked', true )
				.trigger( 'change' );
		},
	} );

	acf.registerFieldType( Field );

	// Let conditional logic rules on other fields depend on this one.
	[ 'hasValue', 'hasNoValue', 'equalTo', 'notEqualTo', 'patternMatch', 'contains' ].forEach( function ( condition ) {
		acf.registerConditionForFieldType( condition, 'fk_image_selector' );
	} );
} )( jQuery );

/**
 * Image Selector field - choices builder in the field group editor.
 *
 * ACF renders the "Choices" setting as a textarea holding a JSON list of
 * { image, label, value, url } objects. This hides it and puts a sortable
 * list of image rows in its place, writing the JSON back on every change.
 */
( function ( $ ) {
	'use strict';

	var SETTING = '.acf-field-setting-image_choices';
	var l10n = window.fkAcfImageSelector || {};

	/**
	 * Turns a name into a value that's safe to use as a CSS class.
	 *
	 * @param {string} text
	 * @return {string}
	 */
	function slugify( text ) {
		return String( text )
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[̀-ͯ]/g, '' )
			.replace( /[^a-z0-9_-]+/g, '-' )
			.replace( /^-+|-+$/g, '' );
	}

	/**
	 * Strips characters that aren't allowed in a value (matches the server's
	 * sanitize_html_class()).
	 *
	 * @param {string} text
	 * @return {string}
	 */
	function cleanValue( text ) {
		return String( text ).replace( /[^A-Za-z0-9_-]/g, '' );
	}

	/**
	 * Picks a small preview URL from a media library attachment.
	 *
	 * @param {Object} attachment
	 * @return {string}
	 */
	function previewUrl( attachment ) {
		var sizes = attachment.sizes || {};
		var size = sizes.thumbnail || sizes.medium || sizes.full;
		return size ? size.url : attachment.url;
	}

	/**
	 * Opens the media library.
	 *
	 * @param {boolean}  multiple Allow picking more than one image.
	 * @param {Function} onSelect Called with an array of attachment objects.
	 */
	function openMedia( multiple, onSelect ) {
		var frame = wp.media( {
			title: multiple ? l10n.addImages : l10n.chooseImage,
			button: { text: multiple ? l10n.useImages : l10n.useImage },
			library: { type: 'image' },
			multiple: multiple ? 'add' : false,
		} );

		frame.on( 'select', function () {
			onSelect( frame.state().get( 'selection' ).toJSON() );
		} );

		frame.open();
	}

	/**
	 * Builds one choice row.
	 *
	 * @param {Object}  choice   { image, label, value, url }
	 * @param {boolean} existing Whether the value was already saved. Saved
	 *                           values are never rewritten from the name.
	 * @return {jQuery}
	 */
	function buildRow( choice, existing ) {
		var $row = $( '<li class="fk-ais-builder__row" />' ).attr( 'data-image', choice.image || 0 );

		$( '<span class="fk-ais-builder__handle dashicons dashicons-menu" />' )
			.attr( 'title', l10n.reorder )
			.appendTo( $row );

		var $image = $( '<button type="button" class="fk-ais-builder__image" />' )
			.attr( { title: l10n.changeImage, 'aria-label': l10n.changeImage } )
			.appendTo( $row );

		if ( choice.url ) {
			$( '<img alt="" />' ).attr( 'src', choice.url ).appendTo( $image );
		}

		$( '<label class="fk-ais-builder__field" />' )
			.append( $( '<span />' ).text( l10n.name ) )
			.append( $( '<input type="text" class="fk-ais-builder__label" />' ).val( choice.label || '' ) )
			.appendTo( $row );

		$( '<label class="fk-ais-builder__field" />' )
			.append( $( '<span />' ).text( l10n.value ) )
			.append(
				$( '<input type="text" class="fk-ais-builder__value code" />' )
					.val( choice.value || '' )
					.toggleClass( 'is-edited', !! existing )
			)
			.appendTo( $row );

		$( '<button type="button" class="fk-ais-builder__remove dashicons dashicons-no-alt" />' )
			.attr( { title: l10n.remove, 'aria-label': l10n.remove } )
			.appendTo( $row );

		return $row;
	}

	/**
	 * Writes the rows back to the textarea as JSON. Triggering "change" is what
	 * tells ACF this field's settings need saving.
	 *
	 * @param {jQuery} $setting The .acf-field-setting-image_choices wrapper.
	 */
	function sync( $setting ) {
		var choices = $setting
			.find( '.fk-ais-builder__row' )
			.map( function () {
				var $row = $( this );
				return {
					image: parseInt( $row.attr( 'data-image' ), 10 ) || 0,
					label: $row.find( '.fk-ais-builder__label' ).val(),
					value: $row.find( '.fk-ais-builder__value' ).val(),
					url: $row.find( '.fk-ais-builder__image img' ).attr( 'src' ) || '',
				};
			} )
			.get();

		$setting.find( '.fk-ais-builder' ).toggleClass( 'is-empty', ! choices.length );
		$setting.find( 'textarea' ).val( JSON.stringify( choices ) ).trigger( 'change' );
	}

	/**
	 * Replaces a settings textarea with the builder. Safe to call repeatedly.
	 *
	 * @param {jQuery} $setting The .acf-field-setting-image_choices wrapper.
	 */
	function init( $setting ) {
		if ( $setting.data( 'fkAis' ) ) {
			return;
		}
		$setting.data( 'fkAis', true );

		// A duplicated field object arrives with a copy of the builder that
		// has no sortable attached, so start again from the textarea.
		$setting.find( '.fk-ais-builder' ).remove();

		var $textarea = $setting.find( 'textarea' );
		var choices = [];

		try {
			choices = JSON.parse( $textarea.val() || '[]' );
		} catch ( e ) {
			choices = [];
		}

		var $list = $( '<ul class="fk-ais-builder__list" />' );
		$.each( Array.isArray( choices ) ? choices : [], function ( i, choice ) {
			$list.append( buildRow( choice, true ) );
		} );

		var $builder = $( '<div class="fk-ais-builder" />' )
			.toggleClass( 'is-empty', ! $list.children().length )
			.append( $list )
			.append( $( '<p class="fk-ais-builder__empty description" />' ).text( l10n.empty ) )
			.append( $( '<button type="button" class="button fk-ais-builder__add" />' ).text( l10n.addImages ) );

		$textarea.after( $builder );

		$list.sortable( {
			handle: '.fk-ais-builder__handle',
			axis: 'y',
			tolerance: 'pointer',
			update: function () {
				sync( $setting );
			},
		} );
	}

	function initAll() {
		$( SETTING ).each( function () {
			init( $( this ) );
		} );
	}

	// Add new choices from the media library.
	$( document ).on( 'click', '.fk-ais-builder__add', function ( e ) {
		e.preventDefault();
		var $setting = $( this ).closest( SETTING );

		openMedia( true, function ( attachments ) {
			var $list = $setting.find( '.fk-ais-builder__list' );

			attachments.forEach( function ( attachment ) {
				var label = attachment.title || attachment.filename || '';
				$list.append(
					buildRow(
						{ image: attachment.id, label: label, value: slugify( label ), url: previewUrl( attachment ) },
						false
					)
				);
			} );

			sync( $setting );
		} );
	} );

	// Swap the image on an existing choice.
	$( document ).on( 'click', '.fk-ais-builder__image', function ( e ) {
		e.preventDefault();
		var $row = $( this ).closest( '.fk-ais-builder__row' );
		var $button = $( this );

		openMedia( false, function ( attachments ) {
			if ( ! attachments.length ) {
				return;
			}
			$row.attr( 'data-image', attachments[ 0 ].id );
			$button.empty().append( $( '<img alt="" />' ).attr( 'src', previewUrl( attachments[ 0 ] ) ) );
			sync( $row.closest( SETTING ) );
		} );
	} );

	$( document ).on( 'click', '.fk-ais-builder__remove', function ( e ) {
		e.preventDefault();
		var $setting = $( this ).closest( SETTING );
		$( this ).closest( '.fk-ais-builder__row' ).remove();
		sync( $setting );
	} );

	// Typing a name fills in the value, until the value is edited by hand.
	$( document ).on( 'input', '.fk-ais-builder__label', function () {
		var $row = $( this ).closest( '.fk-ais-builder__row' );
		var $value = $row.find( '.fk-ais-builder__value' );

		if ( ! $value.hasClass( 'is-edited' ) ) {
			$value.val( slugify( this.value ) );
		}
		sync( $row.closest( SETTING ) );
	} );

	$( document ).on( 'input', '.fk-ais-builder__value', function () {
		var cleaned = cleanValue( this.value );
		if ( cleaned !== this.value ) {
			this.value = cleaned;
		}
		$( this ).toggleClass( 'is-edited', cleaned !== '' );
		sync( $( this ).closest( SETTING ) );
	} );

	// Enter in a builder input shouldn't submit the field group form.
	$( document ).on( 'keydown', '.fk-ais-builder input', function ( e ) {
		if ( e.key === 'Enter' ) {
			e.preventDefault();
		}
	} );

	// Initialise on load, and whenever ACF adds or re-renders field settings
	// (new field, duplicated field, field type changed).
	$( initAll );

	if ( typeof acf !== 'undefined' ) {
		acf.addAction( 'append', initAll );
		acf.addAction( 'change_field_type', initAll );
		acf.addAction( 'render_field_object', initAll );
	}

	// Fallback for settings loaded in ways that skip the actions above.
	if ( window.MutationObserver ) {
		var queued = false;
		new MutationObserver( function () {
			if ( queued ) {
				return;
			}
			queued = true;
			window.requestAnimationFrame( function () {
				queued = false;
				initAll();
			} );
		} ).observe( document.body, { childList: true, subtree: true } );
	}
} )( jQuery );

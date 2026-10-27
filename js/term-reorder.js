/* global inlineEditTax, ajaxurl, orderL10n */

var sortable_terms_table = jQuery( '.wp-list-table tbody' ),
	taxonomy             = jQuery( 'form input[name="taxonomy"]' ).val(),
	term_order_saving    = false;

var term_order_i18n = jQuery.extend( {
	moveUp:        'Move up',
	moveDown:      'Move down',
	moveUpLabel:   'Move up: %s',
	moveDownLabel: 'Move down: %s',
	moved:         '%1$s moved to position %2$d of %3$d.',
	saved:         'Order saved.',
	error:         'The new order could not be saved. Reload the page and try again.'
}, ( typeof orderL10n !== 'undefined' ) ? orderL10n : {} );

/**
 * Replace %s / %d / %1$s style placeholders.
 *
 * @returns {string}
 */
function term_order_format( str ) {
	var args  = Array.prototype.slice.call( arguments, 1 ),
		index = 0;

	return String( str ).replace( /%(\d+\$)?[sd]/g, function ( match, position ) {
		var value = position ? args[ parseInt( position, 10 ) - 1 ] : args[ index++ ];
		return typeof value === 'undefined' ? '' : value;
	} );
}

function term_order_speak( message, politeness ) {
	if ( window.wp && wp.a11y && typeof wp.a11y.speak === 'function' ) {
		wp.a11y.speak( message, politeness || 'polite' );
	}
}

function term_order_rows() {
	return sortable_terms_table.children( 'tr:not(.no-items):not(.inline-edit-row)' );
}

function term_order_row_name( $row ) {
	var name = jQuery.trim( $row.find( '.row-title' ).first().text() );
	return name || jQuery.trim( $row.find( 'td.column-name, td.name' ).first().text() );
}

function term_order_refresh_buttons() {
	var $rows = term_order_rows();

	$rows.each( function ( index ) {
		var $row = jQuery( this );
		$row.find( '.pp-series-term-move-up' ).prop( 'disabled', index === 0 );
		$row.find( '.pp-series-term-move-down' ).prop( 'disabled', index === $rows.length - 1 );
	} );
}

/**
 * Add Move up / Move down buttons to each term row (idempotent).
 */
function term_order_add_buttons() {
	var $rows = term_order_rows();

	if ( $rows.length < 2 ) {
		$rows.find( '.pp-series-term-move-buttons' ).remove();
		return;
	}

	$rows.each( function () {
		var $row = jQuery( this ),
			name, $cell, $wrap;

		if ( $row.find( '.pp-series-term-move-buttons' ).length ) {
			return;
		}

		name  = term_order_row_name( $row );
		$cell = $row.find( 'td.column-name, td.column-primary' ).first();
		if ( ! $cell.length ) {
			$cell = $row.children( 'td' ).first();
		}

		$wrap = jQuery( '<div class="pp-series-term-move-buttons"></div>' );
		jQuery( '<button type="button" class="button button-small pp-series-term-move-up"></button>' )
			.text( term_order_i18n.moveUp )
			.attr( 'aria-label', term_order_format( term_order_i18n.moveUpLabel, name ) )
			.appendTo( $wrap );
		jQuery( '<button type="button" class="button button-small pp-series-term-move-down"></button>' )
			.text( term_order_i18n.moveDown )
			.attr( 'aria-label', term_order_format( term_order_i18n.moveDownLabel, name ) )
			.appendTo( $wrap );

		// Place after row actions so the buttons stay visible.
		var $actions = $cell.children( '.row-actions' ).first();
		if ( $actions.length ) {
			$wrap.insertAfter( $actions );
		} else {
			$cell.append( $wrap );
		}
	} );

	term_order_refresh_buttons();
}

function term_order_show_error( message ) {
	var $notice = jQuery( '<div class="notice notice-error pp-series-term-order-notice"><p></p></div>' ),
		$target = jQuery( '#ajax-response' );

	jQuery( '.pp-series-term-order-notice' ).remove();
	$notice.find( 'p' ).text( message );

	if ( $target.length ) {
		$target.append( $notice );
	} else {
		$notice.insertBefore( sortable_terms_table.closest( 'table' ) );
	}

	term_order_speak( message, 'assertive' );
}

/**
 * Save the current row order. Shared by drag and the Move buttons.
 *
 * @param {jQuery} $item The row that moved.
 * @param {string} announcement Text announced before "Order saved."
 */
function term_order_save( $item, announcement ) {
	sortable_terms_table.sortable( 'disable' ).addClass( 'to-updating' );
	term_order_saving = true;

	$item.addClass( 'to-row-updating' );

	//get terms id
	var terms = [];
	term_order_rows().each( function () {
		var term_id = jQuery( this ).attr( 'id' );
		if ( term_id ) {
			terms.push( term_id.replace( /\D/g, '' ) );
		}
	} );

	// Go do the sorting stuff via ajax
	jQuery.post( ajaxurl, {
		action: 'pp_series_reordering_terms',
		terms: terms,
		nonce: term_order_i18n.nonce,
		tax:    taxonomy
	} )
		.done( function ( response ) {
			term_order_update_callback( response, announcement );
		} )
		.fail( function () {
			term_order_update_callback( null, announcement );
		} );
}

sortable_terms_table.sortable( {

	// Settings
	items:     '> tr:not(.no-items)',
	cursor:    'move',
	axis:      'y',
	cancel:    '.inline-edit-row, button, input, select, textarea',
	distance:  2,
	opacity:   0.9,
	tolerance: 'pointer',
	scroll:    true,

	/**
	 * Sort start
	 *
	 * @param {event} e
	 * @param {element} ui
	 * @returns {void}
	 */
	start: function ( e, ui ) {

		if ( typeof ( inlineEditTax ) !== 'undefined' ) {
			inlineEditTax.revert();
		}

		ui.placeholder.height( ui.item.height() );
		ui.item.parent().parent().addClass( 'dragging' );
	},

	/**
	 * Sort dragging
	 *
	 * @param {event} e
	 * @param {element} ui
	 * @returns {void}
	 */
	helper: function ( e, ui ) {

		ui.children().each( function() {
			jQuery( this ).width( jQuery( this ).width() );
		} );

		return ui;
	},

	/**
	 * Sort dragging stopped
	 *
	 * @param {event} e
	 * @param {element} ui
	 * @returns {void}
	 */
	stop: function ( e, ui ) {
		ui.item.children( '.row-actions' ).show();
		ui.item.parent().parent().removeClass( 'dragging' );
	},

	/**
	 * Update the data in the database based on UI changes
	 *
	 * @param {event} e
	 * @param {element} ui
	 * @returns {void}
	 */
	update: function ( e, ui ) {
		var $rows = term_order_rows();
		term_order_refresh_buttons();
		term_order_save(
			ui.item,
			term_order_format( term_order_i18n.moved, term_order_row_name( ui.item ), $rows.index( ui.item ) + 1, $rows.length )
		);
	}
} );

/**
 * Update the term order based on the ajax response
 *
 * @param {object|string|null} response
 * @param {string} announcement
 * @returns {void}
 */
function term_order_update_callback( response, announcement ) {

	setTimeout( function() {
		jQuery( '.to-row-updating' ).removeClass( 'to-row-updating' );
	}, 500 );

	sortable_terms_table.removeClass( 'to-updating' ).sortable( 'enable' );
	term_order_saving = false;

	if ( typeof response === 'string' ) {
		try {
			response = JSON.parse( response );
		} catch ( err ) {
			response = null;
		}
	}

	if ( response && response.status === 'success' ) {
		jQuery( '.pp-series-term-order-notice' ).remove();
		// The server renumbers the rows in page order; keep the Order column (read by Quick Edit) in step.
		var position = 0;
		term_order_rows().each( function () {
			if ( jQuery( this ).attr( 'id' ) ) {
				position++;
				jQuery( this ).find( '.pp-series-term-order' ).attr( 'data-order', position ).removeData( 'order' ).text( position );
			}
		} );
		term_order_speak( ( announcement ? announcement + ' ' : '' ) + term_order_i18n.saved );
	} else {
		term_order_show_error( ( response && response.message ) ? response.message + ' ' + term_order_i18n.error : term_order_i18n.error );
	}
}

jQuery( document ).on( 'click', '.pp-series-term-move-up, .pp-series-term-move-down', function ( event ) {
	event.preventDefault();

	if ( term_order_saving ) {
		return;
	}

	var $button = jQuery( this ),
		$row    = $button.closest( 'tr' ),
		up      = $button.hasClass( 'pp-series-term-move-up' ),
		$sibling, $rows, $target;

	if ( typeof ( inlineEditTax ) !== 'undefined' ) {
		inlineEditTax.revert();
	}

	$sibling = up ? $row.prevAll( 'tr:not(.no-items):not(.inline-edit-row)' ).first() : $row.nextAll( 'tr:not(.no-items):not(.inline-edit-row)' ).first();
	if ( ! $sibling.length ) {
		return;
	}

	if ( up ) {
		$row.insertBefore( $sibling );
	} else {
		$row.insertAfter( $sibling );
	}

	term_order_refresh_buttons();

	// Keep focus on the moved button; use its sibling if it is now disabled.
	$target = $button.prop( 'disabled' ) ? $row.find( up ? '.pp-series-term-move-down' : '.pp-series-term-move-up' ) : $button;
	$target.trigger( 'focus' );

	$rows = term_order_rows();
	term_order_save(
		$row,
		term_order_format( term_order_i18n.moved, term_order_row_name( $row ), $rows.index( $row ) + 1, $rows.length )
	);
} );

// Rows replaced by Quick Edit or added via Add New Term need buttons too.
jQuery( document ).ajaxComplete( function () {
	setTimeout( term_order_add_buttons, 0 );
} );

jQuery( term_order_add_buttons );

/* global inlineEditTax, ajaxurl */

var sortable_terms_table = jQuery( '.wp-list-table tbody' ),
	taxonomy             = jQuery( 'form input[name="taxonomy"]' ).val();
  
sortable_terms_table.sortable( {

	// Settings
	items:     '> tr:not(.no-items)',
	cursor:    'move',
	axis:      'y',
	cancel:    '.inline-edit-row',
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
		sortable_terms_table.sortable( 'disable' ).addClass( 'to-updating' );

		ui.item.addClass( 'to-row-updating' );

    //get terms id
    var terms = [];
    jQuery(".wp-list-table tbody > tr:not(.no-items)").each(function (e, i) {
      var term_id = jQuery(this).attr('id');
      terms.push(term_id.replace(/\D/g,''));
    });

		// Go do the sorting stuff via ajax
		jQuery.post( ajaxurl, {
			action: 'pp_series_reordering_terms',
			terms: terms,
			nonce: orderL10n.nonce,
			tax:    taxonomy
		}, term_order_update_callback );
	}
} );

/**
 * Update the term order based on the ajax response
 *
 * @param {type} response
 * @returns {void}
 */
function term_order_update_callback( response ) {

  setTimeout( function() {
    jQuery( '.to-row-updating' ).removeClass( 'to-row-updating' );
  }, 500 );

  sortable_terms_table.removeClass('to-updating').sortable('enable');
  
  return;
}

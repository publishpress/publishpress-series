jQuery( document ).ready( function( $ ) {
    /**
     * Read the stored order from the row's data attribute (falls back to the Order column text).
     */
    function getRowOrder( $row ) {
        var $value = $row.find( '.pp-series-term-order' ).first();

        if ( $value.length && typeof $value.data( 'order' ) !== 'undefined' ) {
            return String( $value.data( 'order' ) );
        }

        return $.trim( $row.find( 'td.column-order, td.order' ).first().text() );
    }

    // Delegated, so rows replaced after a Quick Edit save keep working.
    $( '#the-list' ).on( 'click', '.editinline', function() {
        var $row  = $( this ).closest( 'tr' ),
            tagId = ( $row.attr( 'id' ) || '' ).replace( /\D/g, '' ),
            order = getRowOrder( $row );

        // Template row (cloned by WordPress) and, once open, the live edit row.
        $( ':input[name="term_order"]', '#inline-edit' ).val( order );
        $( ':input[name="term_order_original"]', '#inline-edit' ).val( order );
        setTimeout( function() {
            $( ':input[name="term_order"]', '#edit-' + tagId ).val( order );
            $( ':input[name="term_order_original"]', '#edit-' + tagId ).val( order );
        }, 0 );
    } );
} );

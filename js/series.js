jQuery(document).ready( function($) {
	var myConfirm = function() { return '' !== $('#newseries').val(); };

	var l10n = $.extend( {
		emptyName: 'Enter a name for the new series.'
	}, window.ppSeriesMetabox || {} );

	/*$('#serieschecklist').wpList( { alt: '', response: 'series-ajax-response', confirm: myConfirm } ); /**/

	function clearNewSeriesError() {
		$('#newseries').removeAttr('aria-invalid aria-describedby');
	}

	function showNewSeriesError( message ) {
		$('#series-ajax-response').text( message );
		$('#newseries')
			.attr( 'aria-invalid', 'true' )
			.attr( 'aria-describedby', 'series-ajax-response' )
			.trigger( 'focus' );
	}

	function addSeries() {
		if ( ! myConfirm() ) {
			showNewSeriesError( l10n.emptyName );
			return;
		}

		$('input','#serieschecklist').removeProp('checked');

		var data = {
			action: 'add_series',
			newseries: $('#newseries').val(),
			addnonce: $('#add-series-nonce').text()
		}
		$.post(ajaxurl, data, function(response) {
			var resp = ( typeof response === 'string' ) ? $.parseJSON(response) : response;
			if ( !resp.error ) {
				clearNewSeriesError();
				$('#newseries').val('');
				$('ul#serieschecklist li:first').after(resp.html);
				$('#series-'+resp.id).animate({backgroundColor: "transparent"}, 3000);
				$('#add-series-nonce').text(resp.new_nonce);
				$('#series-ajax-response').text(resp.message || '');
				if ( resp.id ) {
					$('#series_part').attr('name', 'series_part['+resp.id+']');
				}
			} else {
				showNewSeriesError( resp.error );
			}
		});
	}

	$('#seriesadd').click( function() {
		addSeries();
	});

	// Enter in the new series field adds the series instead of submitting the post form.
	$('#newseries').on( 'keydown', function( event ) {
		if ( event.key === 'Enter' || event.keyCode === 13 ) {
			event.preventDefault();
			addSeries();
		}
	}).on( 'input', function() {
		if ( $(this).attr('aria-invalid') ) {
			clearNewSeriesError();
		}
	});

	$('#seriesadd').mouseup( function() {
		setTimeout( function() {
			var new_id = $('input:checked', '#serieschecklist').val();
			if ( new_id != null )
				$('#series_part').attr('name', 'series_part['+new_id+']')
		}, 1000);
	});

	// Delegated so series added via Add New also update the part field name.
	$('#serieschecklist').on( 'change', 'input[name="post_series"]', function() {
		var ser_id = $(this).attr('id').replace('in-series-', '');
		$('#series_part').attr('name', 'series_part['+ser_id+']')
  });

} );

jQuery(document).ready( function($) {
	var myConfirm = function() { return '' !== $('#newseries').val(); };

	/*$('#serieschecklist').wpList( { alt: '', response: 'series-ajax-response', confirm: myConfirm } ); /**/


	$('#seriesadd').click( function() {
		$('input','#serieschecklist').removeProp('checked');
		/* console.log($('#newseries').val()); /**/

		var data = {
			action: 'add_series',
			newseries: $('#newseries').val(),
			addnonce: $('#add-series-nonce').text()
		}
		$.post(ajaxurl, data, function(response) {
			console.log(response); /**/
			var resp = $.parseJSON(response);
			console.log(resp); /**/
			if ( !resp.error ) {
				$('#newseries').val('');
				$('ul#serieschecklist li:first').after(resp.html);
				$('#series-'+resp.id).animate({backgroundColor: "transparent"}, 3000);
				$('#add-series-nonce').text(resp.new_nonce);
			} else {
				$('#series-ajax-response').html(resp.error);
			}
		});
	});

	$('#seriesadd').mouseup( function() {
		setTimeout( function() {
			var new_id = $('input:checked', '#serieschecklist').val();
			if ( new_id != null )
				$('#series_part').attr('name', 'series_part['+new_id+']')
		}, 1000);
	});

	$('input[name|="post_series"]').click(function() {
		var ser_id = $(this).attr('id').replace('in-series-', '');
		$('#series_part').attr('name', 'series_part['+ser_id+']')
	});

} );

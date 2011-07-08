jQuery( function($) {
	var myConfirm = function() { return '' !== $('#newseries').val(); };
	
	$('#jaxseries').prepend('<span id="ajaxseries"><input type="text" name="newseries" id="newseries" size="16" autocomplete="off"/><input type="button" name="Button" class="add:serieschecklist:jaxseries" id="seriesadd" value="' + seriesL10n.add + '" /><input type="hidden"/><input type="hidden"/><span id="howto">' + seriesL10n.how + '</span></span><span id="series-ajax-response"></span>')
	
	$('#serieschecklist').wpList( { alt: '', response: 'series-ajax-response', confirm: myConfirm } );
	
	
	$('#seriesadd').click( function() {
		$('input','#serieschecklist').removeProp('checked');
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
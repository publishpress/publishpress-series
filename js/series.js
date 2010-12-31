jQuery( function($) {
	var myConfirm = function() { return '' !== $('#newseries').val(); };
	$('#jaxseries').prepend('<span id="ajaxseries"><input type="text" name="newseries" id="newseries" size="16" autocomplete="off"/><input type="button" name="Button" class="add:serieschecklist:jaxseries" id="seriesadd" value="' + seriesL10n.add + '" /><input type="hidden"/><input type="hidden"/><span id="howto">' + seriesL10n.how + '</span></span><span id="series-ajax-response"></span>')
	$('#serieschecklist').wpList( { alt: '', response: 'series-ajax-response', confirm: myConfirm } );
	
	$('#seriesadd').click(function() {
		$('input','#serieschecklist').removeAttr('checked');
	});
} );
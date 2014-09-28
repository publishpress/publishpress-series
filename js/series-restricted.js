jQuery(document).ready( function($) {
	$('input[name|="post_series"]').click(function() {
		var ser_id = $(this).attr('id').replace('in-series-', '');
		$('#series_part').attr('name', 'series_part['+ser_id+']')
	});
} );

jQuery(document).ready( function($) {
	$('#serieschecklist').on('change', 'input[name="post_series"]', function() {
		var ser_id = $(this).attr('id').replace('in-series-', '');
		$('#series_part').attr('name', 'series_part['+ser_id+']')
	});
} );

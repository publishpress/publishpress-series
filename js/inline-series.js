jQuery(document).ready(function($) {
	$("label.inline-edit-tags").remove(":contains('Series')");
	$('#the-list').on( 'click', 'a.editinline', function() {
		var id, type, editRowData, rowData, series_check, series_part, series_text;
		var r_id = inlineEditPost.getId(this);
		type = $('table.widefat').hasClass('page') ? 'page' : 'post';
		
		rowData = $('#inline_series_'+r_id);
		id = $('.series_post_id', rowData).text();
		$('#hidden_series_id', 'div.inline_edit_series_old').remove();
		$('div.inline_edit_series_old').attr('class', 'inline_edit_series_');
		$('div.inline_edit_series_').attr('class','inline_edit_series_'+id);
		editRowData = $('.inline_edit_series_'+id);
			
		if ( type == 'post' ) {
			series_check = $('.series_inline_edit', rowData).text();
			series_part =  $('.series_inline_part', rowData).text();
			series_text = $('.series_inline_name', rowData).text();
			
			if ( series_check === '' ) {
				series_check = -1;
				series_text = 'No Series';
			}
				
			$('select[name="post_series"] option[value="'+series_check+'"]', editRowData).remove();/* FOR SOME STRANGE REASON IT APPEARS THAT ONLY ATTR AND VAL AREN'T WORKING BUT REMOVE() WILL WORK? WIERD...*/
			$('.post_series_select', editRowData).append('<option value="'+series_check+'" selected="selected">'+series_text+'</option>');
			
			if ( series_check == -1 ) series_check = 0;
			
			$('.series_part', editRowData).attr('name', 'series_part['+series_check+']').val(series_part);
			
					
		}
		
		$('select.post_series_select', editRowData).bind('change',function() {
				new_id = $(this).val();
				$('.series_part').attr('name', 'series_part['+new_id+']').val('');
		});
		
		$('label.inline-edit-series', editRowData).before('<div id="hidden_series_id" class="hidden">'+series_check+'</div>');
		$(editRowData).attr('class', 'inline_edit_series_old');
	});
});
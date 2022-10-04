jQuery(document).ready(function($) {
	$('#the-list').on('click', '.editinline', function() {
		var id, type, editRowData, rowData, group_check, group_text;
		var r_id = inlineEditTax.getId(this);
		type = $('#the-list').attr('data-wp-lists').substr(5);
		rowData = $('#inline_group_'+r_id);
		id = $('series_group_id', rowData).text();
		$('#hidden_group_id', 'div.inline_edit_group_old').remove();
		$('div.inline_edit_group_old').attr('class', 'inline_edit_group_');
		$('div.inline_edit_group_').attr('class', 'inline_edit_group_'+id);
		editRowData = $('.inline_edit_group_'+id);
		/*editRow = $('#inline-edit').clone(true);*/
		
		if ( type == 'tag' ) {
			group_check = $('.group_inline_edit', rowData).text();
			group_text = $('.group_inline_name', rowData).text();
			
			if ( group_check === '' ) {
				group_check = -1;
			}
			
			$('.group_inline_edit', rowData).each(function() {
				if ( term_ids = $(this).text() )
				{
					taxname = 'category';
					
					$('ul.'+taxname+'-checklist :checkbox', editRowData).val(term_ids.split(','));
					
				}
			});
			
		}
		
		$('span.catshow').click(function() {
			$(this).hide().next().show().parent().next().addClass("cat-hover");
		});
		
		$('span.cathide').click(function() {
			$(this).hide().prev().show().parent().next().removeClass("cat-hover");
		});
		$(editRowData).attr('class', 'inline_edit_group_old');
	});
	
	var series_filter = $('#series_group_filter');
	
	$('.col-wrap', '#col-right').prepend(series_filter);
});
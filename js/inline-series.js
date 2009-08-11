
(function($) {
inlineEditPost.edit = function(id){
	var t = this, fields, editRow, rowData, cats, status, pageOpt, f, pageLevel, nextPage, pageLoop = true, nextLevel, tax, series;
		t.revert();

		if ( typeof(id) == 'object' )
			id = t.getId(id);

		fields = ['post_title', 'post_name', 'post_author', '_status', 'jj', 'mm', 'aa', 'hh', 'mn', 'ss', 'post_password'];
		if ( t.type == 'page' ) fields.push('post_parent', 'menu_order', 'page_template');
		if ( t.type == 'post' ) fields.push('tags_input');
		
		// add the new blank row
		editRow = $('#inline-edit').clone(true);
		$('td', editRow).attr('colspan', $('.widefat:first thead th:visible').length);

		if ( $(t.what+id).hasClass('alternate') )
			$(editRow).addClass('alternate');
		$(t.what+id).hide().after(editRow);

		// populate the data
		rowData = $('#inline_'+id); 
		rowData2 = $('#inline_series_'+id);
		for ( f = 0; f < fields.length; f++ ) {
			$(':input[name="'+fields[f]+'"]', editRow).val( $('.'+fields[f], rowData).text() );
		}

		if ( $('.comment_status', rowData).text() == 'open' )
			$('input[name="comment_status"]', editRow).attr("checked", "checked");
		if ( $('.ping_status', rowData).text() == 'open' )
			$('input[name="ping_status"]', editRow).attr("checked", "checked");
		if ( $('.sticky', rowData).text() == 'sticky' )
			$('input[name="sticky"]', editRow).attr("checked", "checked");

		// categories
		if ( cats = $('.post_category', rowData).text() )
			$('ul.cat-checklist :checkbox', editRow).val(cats.split(','));

		// handle the post status
		status = $('._status', rowData).text();
		if ( status != 'future' ) $('select[name="_status"] option[value="future"]', editRow).remove();
		if ( status == 'private' ) {
			$('input[name="keep_private"]', editRow).attr("checked", "checked");
			$('input.inline-edit-password-input').val('').attr('disabled', 'disabled');
		}

		// remove the current page and children from the parent dropdown
		pageOpt = $('select[name="post_parent"] option[value="'+id+'"]', editRow);
		if ( pageOpt.length > 0 ) {
			pageLevel = pageOpt[0].className.split('-')[1];
			nextPage = pageOpt;
			while ( pageLoop ) {
				nextPage = nextPage.next('option');
				if (nextPage.length == 0) break;
				nextLevel = nextPage[0].className.split('-')[1];
				if ( nextLevel <= pageLevel ) {
					pageLoop = false;
				} else {
					nextPage.remove();
					nextPage = pageOpt;
				}
			}
			pageOpt.remove();
		}

		$(editRow).attr('id', 'edit-'+id).addClass('inline-editor').show();
		$('.ptitle', editRow).focus();

		// enable autocomplete for tags
		if ( t.type == 'post' ) {
			tax = 'post_tag';
			$('tr.inline-editor textarea[name="tags_input"]').suggest( 'admin-ajax.php?action=ajax-tag-search&tax='+tax, { delay: 500, minchars: 2, multiple: true, multipleSep: ", " } );
		}
		
		if ( t.type == 'post' ) {
			var series_check = $('.series_inline_edit', rowData2).text();
			var series_part = $('.series_inline_part', rowData2).text();
			if ( series_check == '' ) {
				$('select[name="post_series"]',editRow).val(0); 
			} else {
				$('select[name="post_series"]',editRow).val(series_check); 
			}
			$('input[name="series_part"]',editRow).val(series_part);
		}
		
		return false;
	}
	
$(document).ready(function(){inlineEditPost.edit();});
})(jQuery);
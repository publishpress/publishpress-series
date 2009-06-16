(function($) {
inlineEditSeries = {
	init : function() {
		var t = this;
		
		t.type = $('table.widefat').hasClass('page') ? 'page' : 'post';
		t.what = '#'+t.type+'-';
		t.rows = $('tr.iedit');
		
		// add events
		t.addEvents(t.rows);
		
	},
		
	addEvents : function(r) {
		r.each(function() {
			var row = $(this);
			$('a.editinline', row).click(function() { inlineEditSeries.edit(this); return false; });
		});
	},
		
	edit : function(id) {
		var t = this;
		//t.inlineEditPost.revert();
				
		if ( typeof(id) == 'object' )
			id = t.getId(id);
			
		var fields = ['series_part'];
			
		var editRowSeries = $('#inline-edit').clone(true);
		$('td', editRowSeries).attr('colspan', $('.widefat:first thead th:visible').length);
		
		if ( $(t.what+id).hasClass('alternate') )
			$(editRowSeries).addClass('alternate');
		$(t.what+id).hide().after(editRowSeries);
		
		
		// populate the series data
		var rowDataSeries = $('#inline_series_'+id);
		
		//for ( var f = 0; f < fields.length; f++ ) {
		 $(':input[name="series_part"]', editRowSeries).val( $('.series_part', rowDataSeries).text() );
		//}
		
		//handle series
		var series;
		if ( series = $('.post_series', rowDataSeries).text() ) 
			$('ul.serieschecklist :checkbox', editRowSeries).val(series);
			//alert($('.post_series', rowDataSeries).text());		
			//LEFT OFF HERE
// - data is being obtained but not written...need to find  out why?	
//-because it's not pulling from the same area as the data the js pulls from wp core.  I think the td rows/etc. are wrong - need to pull from inline_series_+id NOT #inline_+id  (take a look at editRowSeries? (see line 130 on linline-seriesold.js - I think that's the line that needs to be changed...)			
	},
	
	getId : function(o) {
		var id = o.tagName == 'TR' ? o.id : $(o).parents('tr').attr('id');
		var parts = id.split('-');
		return parts[parts.length - 1];
		
	}
};

$(document).ready(function(){inlineEditSeries.init();});
})(jQuery);
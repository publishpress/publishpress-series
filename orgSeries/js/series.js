/* copied from cat.js */
addLoadEvent(function(){seriesList=new listMan('serieschecklist');seriesList.ajaxRespEl='jaxseries';seriesList.topAdder=1;seriesList.alt=0;seriesList.showLink=0;});
addLoadEvent(newSeriesAddIn);
function newSeriesAddIn() {
	var jaxseries = $('jaxseries');
	if ( !jaxseries )
		return false;
	Element.update(jaxseries,'<span id="ajaxseries"><input type="text" name="newseries" id="newseries" size="16" autocomplete="off" /><input type="button" name="Button" id="seriesadd" value="' + seriesL10n.add + '"/><input type="hidden"/><span id="serieshowto">' + seriesL10n.how + '</span></span>');
	$('newseries').onkeypress = function(e) { return killSubmit("seriesList.ajaxAdder('series','jaxseries');", e); };
	$('seriesadd').onclick = function() {seriesList.ajaxAdder('series','jaxseries'); };
}
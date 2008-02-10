addLoadEvent(function() {
	if (!theList.theList) return false;
	document.forms.addseries.submit.onclick = function(e) {return killSubmit('theList.ajaxAdder("series", "addseries");', e); };
	theList.addComplete = function(what,where,update,transport) {
		var name= getNodeVaule(transport.responseXML, 'name').unescapeHTML();
		var id = transport.responseXML.getElementsByTagName(what)[0].getAttribute('id');
	};
});
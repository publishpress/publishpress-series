addLoadEvent(function() {
	if (!theList.theList) return false;
	document.forms.addseries.submit.onclick = function(e) {return killSubmit('theList.ajaxAdder("serial", "addseries");', e); };
});
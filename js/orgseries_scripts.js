var image_url_collection = new Array();
var image_title_collection = new Array();
var image_url_collection_div_contents = '';

var nativeSplit = nativeSplit || String.prototype.split; // Cross-Browser Split

//Extracted from '/wp-includes/js/quicktags.js?ver=3958' - to use the media-upload.php with thickbox
function edInsertContent(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
		myField.focus();
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)
					  + myValue
					  + myField.value.substring(endPos, myField.value.length);
		myField.focus();
		myField.selectionStart = startPos + myValue.length;
		myField.selectionEnd = startPos + myValue.length;
	} else {
		myField.value += myValue;
		myField.focus();
	}
}
//Code extracted from '/wp-includes/js/quicktags.js?ver=3958' ends here.

function str_right(str, n){
    if (n <= 0)
       return "";
    else if (n > String(str).length)
       return str;
    else {
       var iLen = String(str).length;
       return String(str).substring(iLen, iLen - n);
    }
}

/*
	Cross-Browser Split 0.2.1
	By Steven Levithan <http://stevenlevithan.com>
	MIT license
*/
String.prototype.split = function (s /* separator */, limit) {
	// If separator is not a regex, use the native split method
	if (!(s instanceof RegExp))
		return nativeSplit.apply(this, arguments);
	/* Behavior for limit: If it's...
	 - Undefined: No limit
	 - NaN or zero: Return an empty array
	 - A positive number: Use limit after dropping any decimal
	 - A negative number: No limit
	 - Other: Type-convert, then use the above rules */
	if (limit === undefined || +limit < 0) {
		limit = false;
	} else {
		limit = Math.floor(+limit);
		if (!limit)
			return [];
	}
	var	flags = (s.global ? "g" : "") + (s.ignoreCase ? "i" : "") + (s.multiline ? "m" : ""),
		s2 = new RegExp("^" + s.source + "$", flags),
		output = [],
		lastLastIndex = 0,
		i = 0,
		match;
	if (!s.global)
		s = new RegExp(s.source, "g" + flags);
	while ((!limit || i++ <= limit) && (match = s.exec(this))) {
		var zeroLengthMatch = !match[0].length;
		// Fix IE's infinite-loop-resistant but incorrect lastIndex
		if (zeroLengthMatch && s.lastIndex > match.index)
			s.lastIndex = match.index; // The same as s.lastIndex--
		if (s.lastIndex > lastLastIndex) {
			// Fix browsers whose exec methods don't consistently return undefined for non-participating capturing groups
			if (match.length > 1) {
				match[0].replace(s2, function () {
					for (var j = 1; j < arguments.length - 2; j++) {
						if (arguments[j] === undefined)
							match[j] = undefined;
					}
				});
			}
			output = output.concat(this.slice(lastLastIndex, match.index), (match.index === this.length ? [] : match.slice(1)));
			lastLastIndex = s.lastIndex;
		}
		if (zeroLengthMatch)
			s.lastIndex++;
	}
	return (lastLastIndex === this.length) ?
		(s.test("") ? output : output.concat("")) :
		(limit      ? output : output.concat(this.slice(lastLastIndex)));
};

//Extracted from fpg_scripts.js (Flash Picture Gallery Plugin) and modified for use here.
function image_url_sync(){
	add_image_url = '';
	for (i=0;i<image_url_collection.length;i++){
		if (image_title_collection[i].length > 50) {cropped_image_title = '...' + str_right(image_title_collection[i], 50);} else {cropped_image_title = image_title_collection[i];}
		add_image_url = add_image_url + "<a href=\"" + image_url_collection[i] + "\" target=\"_blank\">" + cropped_image_title + "</a> [<a href=\"#\" onclick=\"image_url_remove("+i+");return false;\">Remove</a>]<br />";
	}
	if (add_image_url == '') add_image_url = 'No images selected';	
	jQuery("#series_image_url_display").html(add_image_url);
}

function image_url_add(){
	image_url = edCanvas.value.match(/img src=\"(.*?)\"/g)[0].split(/img src=\"(.*?)\"/g)[1];
	image_url = image_url.replace(/-[0-9][0-9][0-9]x[0-9][0-9][0-9]\./i,'.');
	image_title = edCanvas.value.match(/title=\"(.*?)\"/g)[0].split(/title=\"(.*?)\"/g)[1];
	image_url_collection.push(image_url);
	image_title_collection.push(image_title);	
	edCanvas.value = '';
	image_url_sync();
}

function image_url_remove(id) {
	image_url_collection.splice(id,1);
	image_title_collection.splice(id,1);
	image_url_sync();
}
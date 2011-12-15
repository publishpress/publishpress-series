jQuery(document).ready(function($) {
	var add_image_url, image_url_collection;
	add_image_url = image_url_collection;
		
	if (add_image_url == '') add_image_url = "No images selected";
	
	$('#upload_image_button').click(function() {
		var formfield = $('#series_icon_loc').attr('name');
		tb_show('', 'media-upload.php?type=image&amp;TB_iframe=1');
		var imageFrame = $('#TB_iframeContent');
		imageFrame.load(function() {
			imageFrame.contents().find("input[value*='Insert']").val('Use as Series Image');
			imageFrame.contents().find(".wp-post-thumbnail").text('');
			imageFrame.contents().find("#media-items").hover(function() {
			$("input[value*='Insert']", this).val('Use as Series Image');
			$(".wp-post-thumbnail", this).text('');
			},
			function () {
				$("input[value*='Insert']", this).val('Use as Series Image');
				$(".wp-post-thumbnail", this).text('');
			});
			
		});
		return false;
	});
	
		
	window.send_to_editor = function(html) {
		  var imgurl = $('img',html).attr('src');
		 $('#series_icon_loc').val(imgurl);
		 $('#series_icon_loc_display').val(imgurl);
		 tb_remove();
		 var view_image_url = "Selected Image:<br /> <img src=\"" + imgurl + "\" width=\"100px\" />";
		 $('#selected-icon').html(view_image_url)
	}

	$('#submit').click(function(){
		var series_icon_loc_display = $('#series_icon_loc_display');

		if ( series_icon_loc_display.val() == '' ) {
				$('#series_icon_loc').val('');
		}
		$(series_icon_loc_display).val('');
		$('#selected-icon').empty();
	});
});

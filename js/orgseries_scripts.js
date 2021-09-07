(function ($, window, document) {
  'use strict'

	jQuery(document).ready(function($) {
	var add_image_url, image_url_collection;
	add_image_url = image_url_collection;

	if (add_image_url == '') add_image_url = "No images selected";

	$('#upload_image_button2').click(function() {
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
		console.log(html);
			var imgurl = $('img',html).attr('src');
			//account for breakage that got introduced in some WP version. This allows for back compat.
			if ( typeof imgurl === 'undefined' ) {
                imgurl = $(html).attr('src');
            }
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

	// -------------------------------------------------------------
	//   Custom media upload
	// -------------------------------------------------------------
$(document).on('click', '#upload_image_button', function (e) {
	e.preventDefault();
	var uploader = $(this);
	var upload_input = '#series_icon_loc';
	var upload_display = '#series_icon_loc_display';
	var multiple = 0;
	var prev_attachment = 0;//
	var custom_feedback_image_frame;
	if(custom_feedback_image_frame){
		custom_feedback_image_frame.open();
	}
	// Define custom_feedback_image_frame as wp.media object
	custom_feedback_image_frame = wp.media({
					title: 'Select Media',
					multiple : multiple > 0 ? true : false,
					library : {
						 type : 'image',
					 }
				});

				custom_feedback_image_frame.on('close',function() {
				 // On close, get selections and save to the hidden input
				 // plus other AJAX stuff to refresh the image preview
				 if(multiple > 0){

					var selection = custom_feedback_image_frame.state().get('selection');
					selection.map( function( attachment ) {
						attachment = attachment.toJSON();
						$(upload_input).after("<input type='text'  name='student_note[]' class='form-control student_note' value='"+attachment.url+"/>");
					});

				}else{
				 var attachment = custom_feedback_image_frame.state().get('selection').first().toJSON();
					 $(upload_input).val(attachment.url);
					 $(upload_display).val(attachment.url);
		 			var view_image_url = "Selected Image:<br /> <img src=\"" + attachment.url + "\" width=\"100px\" />";
		 			$('#selected-icon').html(view_image_url)
				 }

				});

			 custom_feedback_image_frame.on('open',function() {
				 //$('#menu-item-upload').trigger('click');
				 /*if(prev_attachment > 0){
				 // On open, get the id from the hidden input
				 // and select the appropiate images in the media manager
				 var selection =  custom_feedback_image_frame.state().get('selection');
				 var id = prev_attachment;
				 var attachment = wp.media.attachment(id);
				 attachment.fetch();
				 selection.add( attachment ? [ attachment ] : [] );
				 }*/
			 });

			 custom_feedback_image_frame.open();
});

});

})(jQuery, window, document);

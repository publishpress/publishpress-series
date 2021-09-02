(function($) {
  'use strict'

  /**
   * All of the code for your admin-facing JavaScript source
   * should reside in this file.
   *
   * Note: It has been assumed you will write jQuery code here, so the
   * $ function reference has been prepared for usage within the scope
   * of this function.
   *
   * This enables you to define handlers, for when the DOM is ready:
   *
   * $(function() {
   *
   * });
   *
   * When the window is loaded:
   *
   * $( window ).load(function() {
   *
   * });
   *
   * ...and/or other possibilities.
   *
   * Ideally, it is not considered best practise to attach more than a
   * single DOM-ready or window-load handler for a particular page.
   * Although scripts in the WordPress core, Plugins and Themes may be
   * practising this, we should strive to set a better example in our own work.
   */

  $(document).ready(function() {

    // -------------------------------------------------------------
    //  Move footer credit
    // -------------------------------------------------------------
    if ($('.ppseries-footer-credit').length > 0 && $('#wpbody-content').length > 0) {
      $('#wpbody-content').append('<div class="pressshack-admin-wrapper ppseries-footer-credit">' + $(".ppseries-footer-credit").html() + '</div>');
      $('.ppseries-footer-credit.temporary').remove();
    }


    // -------------------------------------------------------------
    //   Settings tab click
    // -------------------------------------------------------------
    $(document).on('click', '.ppseries-settings-tab .nav-tab', function (e) {
      e.preventDefault();
      var current_content = $(this).attr('href');
      $('.ppseries-settings-tab .nav-tab').removeClass('nav-tab-active');
      $('.ppseries-settings-tab-content').addClass('ppseries-hide-content');
      $('.has-sidebar-content.ppseries-settings-body-content').removeAttr('id');

      $(this).addClass('nav-tab-active');
      $(current_content+'-series-content').removeClass('ppseries-hide-content');

      if($(current_content+'-series-tab').hasClass('series-tab-content')){
        $('.has-sidebar-content.ppseries-settings-body-content').attr('id', 'post-body-content');
        $(current_content.replace("#", ".")+'-series-sidebar').removeClass('ppseries-hide-content');
      }

      if (typeof(localStorage) != 'undefined' && localStorage != null) {
          localStorage.setItem("pp_series_activetab", current_content);
      }

    });

    // -------------------------------------------------------------
    //   Display active group
    // -------------------------------------------------------------
    var pp_series_activetab = '';
    if (typeof(localStorage) != 'undefined' && localStorage != null) {
        pp_series_activetab = localStorage.getItem("pp_series_activetab");
    }
    if (pp_series_activetab !== '' && $(pp_series_activetab+'-series-tab').length) {
        $(pp_series_activetab+'-series-tab').trigger('click');
    } else {
        $('#series_automation_settings-series-tab').trigger('click');
    }


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


  })

})(jQuery)

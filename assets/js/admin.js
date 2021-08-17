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


  })

})(jQuery)

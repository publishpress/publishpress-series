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
    //   Series Post Details selection enhancement
    // -------------------------------------------------------------
    function toggleSeriesPostDetailsLegacyFields() {
      var selectedMeta = $('#series_post_details_selection').val();
      var legacyMetaFields = $('.series-meta-legacy-field');

      if (selectedMeta && selectedMeta !== '') {
        legacyMetaFields.fadeOut(300);
      } else {
        legacyMetaFields.fadeIn(300);
      }
    }

    function toggleSeriesPostDetailsTemplateFields() {
      var selectedValue = $('#series_post_details_selection').val();
      if (selectedValue && selectedValue !== '') {
        // Hide template fields when a Series Post Details is selected
        $('#series_meta_template_row').hide();
        $('#series_meta_excerpt_template_row').hide();
        $('#limit_series_meta_to_single_row').hide();
        $('#series_metabox_position_row').hide();
      } else {
        // Show template fields when "Custom Template" is selected
        $('#series_meta_template_row').show();
        $('#series_meta_excerpt_template_row').show();
        $('#limit_series_meta_to_single_row').show();
        $('#series_metabox_position_row').show();
      }
    }

    if ($('#series_post_details_selection').length) {
      toggleSeriesPostDetailsLegacyFields();
      toggleSeriesPostDetailsTemplateFields();

      $('#series_post_details_selection').on('change', function() {
        toggleSeriesPostDetailsLegacyFields();
        toggleSeriesPostDetailsTemplateFields();
      });
    }


    // -------------------------------------------------------------
    //   Settings tab click
    // -------------------------------------------------------------
    $(document).on('click', '.ppseries-settings-tab .nav-tab', function (e) {
      e.preventDefault();
      var current_content = $(this).attr('href');
      $('.ppseries-settings-tab .nav-tab').removeClass('nav-tab-active');
      $('.ppseries-settings-tab-content').addClass('ppseries-hide-content');
      $('input[name="update_orgseries"]').show();

      if( current_content === '#series_license_settings'){
        $('input[name="update_orgseries"]').hide();
      }

      $(this).addClass('nav-tab-active');
      $(current_content+'-series-content').removeClass('ppseries-hide-content');

      if ($(current_content + '-series-tab').hasClass('series-tab-content'))
      {
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
    //   Prevent ENTER on input from submitting
    // -------------------------------------------------------------
    $(document).on("keydown", "#ppseries_licence_key_input", function (event) {
      return event.keyCode !== 13;
    });

    // -------------------------------------------------------------
    //   Post List Box selection enhancement
    // -------------------------------------------------------------
    function toggleTemplateField() {
      var selectedValue = $('#series_post_list_box_selection').val();
      var legacyTemplateSettings = $('#series_post_list_template, #series_post_list_post_linked_post, #series_post_list_unpublished_post, #series_post_list_currentpost');

      if (selectedValue && selectedValue !== '') {
        legacyTemplateSettings.fadeOut(300);
      } else {
        legacyTemplateSettings.fadeIn(300);
      }
    }

    // Initial state for Post List Box selection
    if ($('#series_post_list_box_selection').length) {
      toggleTemplateField();

      // On change
      $('#series_post_list_box_selection').on('change', function() {
        toggleTemplateField();
      });
    }

    // -------------------------------------------------------------
    //   Post Navigation selection enhancement
    // -------------------------------------------------------------
    function togglePostNavigationCustomFields() {
      var selectedValue = $('#series_post_navigation_selection').val();
      var legacyNavRows = $('#series_post_nav_template_row, #series_navigation_box_position_row, #series_nextpost_nav_custom_text_row, #series_prevpost_nav_custom_text_row, #series_firstpost_nav_custom_text_row');

      if (selectedValue && selectedValue !== '') {
        // A layout is selected - hide the custom template fields
        legacyNavRows.fadeOut(300);
      } else {
        // Custom Template selected - show the fields
        legacyNavRows.fadeIn(300);
      }
    }

    // Initial state for Post Navigation selection
    if ($('#series_post_navigation_selection').length) {
      togglePostNavigationCustomFields();

      // On change
      $('#series_post_navigation_selection').on('change', function() {
        togglePostNavigationCustomFields();
      });
    }

    // -------------------------------------------------------------
    //   Series migration
    // -------------------------------------------------------------
    /*$(document).on('click', '.ppseries-migrate-series', function (e) {
      e.preventDefault();
      var button = $(this);
      $(".ppseries-spinner").addClass("is-active");
      button.attr('disabled', true);
      $('.ppseries-migrate-series-response').remove();
      var data = {
          'action': 'ppseries_pro_migrate_series_by_ajax'
      };
      $.post(ajaxurl, data, function (response) {
        $(".ppseries-spinner").removeClass("is-active");
        button.attr('disabled', false);
        $('.ppseries-migrate-series').after('<div class="ppseries-migrate-series-response" style="margin-top:10px;">'+response.content+'</div>');
      });

  });*/


  })

})(jQuery)

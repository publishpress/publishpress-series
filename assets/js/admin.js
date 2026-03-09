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
    //   Token sidebar: only show when "Custom Template" is selected
    // -------------------------------------------------------------
    function updateTokenSidebar() {
      var $sidebar = $('#ppseries-token-sidebar');
      if (!$sidebar.length) return;

      // Find the currently visible tab's layout select
      var $activeSelect = $('.ppseries-settings-tab-content:not(.ppseries-hide-content) .ppseries-layout-select');

      if ($activeSelect.length) {
        // Custom Template = empty value
        var isCustom = $activeSelect.val() === '';
        if (isCustom) {
          $sidebar.show();
        } else {
          $sidebar.hide();
        }
      } else {
        // Legacy tab has no layout dropdown, so keep token sidebar visible.
        $sidebar.show();
      }
    }

    $(document).on('click', '.ppseries-settings-tab .nav-tab', function() {
      setTimeout(updateTokenSidebar, 50);
    });

    $(document).on('change', '.ppseries-layout-select', function() {
      updateTokenSidebar();
    });

    // Initial state
    setTimeout(updateTokenSidebar, 100);

    // -------------------------------------------------------------
    //   Real-time "Edit this box/layout" link for layout selects
    // -------------------------------------------------------------
    function updateEditLink($select) {
      var $wrap  = $select.siblings('.ppseries-edit-link-wrap');
      var $link  = $wrap.find('.ppseries-edit-link');
      var $opt   = $select.find('option:selected');
      var url    = $opt.data('edit-url');
      var label  = $select.data('edit-label') || 'Edit';

      if (url) {
        $link.attr('href', url).text(label + ' \u2192');
        $wrap.show();
      } else {
        $wrap.hide();
      }
    }

    function bindEditLinks() {
      $('.ppseries-layout-select').each(function() {
        var $sel = $(this);
        updateEditLink($sel);
        $sel.off('change.editlink').on('change.editlink', function() {
          updateEditLink($sel);
        });
      });
    }

    // -------------------------------------------------------------
    //   AJAX lazy-load layout select options
    // -------------------------------------------------------------
    var layoutSelectLoaded = {};

    function loadLayoutSelect($select, callback) {
      var postType = $select.data('post-type');
      if (!postType || layoutSelectLoaded[postType]) {
        if (callback) callback();
        return;
      }

      var settings = window.ppseriesSettings || {};
      var savedKey = $select.data('saved-key');
      var savedVal = settings.saved && savedKey ? parseInt(settings.saved[savedKey], 10) || 0 : 0;

      $.ajax({
        url: settings.ajaxUrl,
        data: {
          action: 'ppseries_get_layout_options',
          nonce: settings.nonce,
          post_type: postType
        },
        dataType: 'json',
        success: function(response) {
          if (!response.success) return;

          $select.empty();
          $select.append('<option value="">Custom Template</option>');

          $.each(response.data, function(i, item) {
            var $opt = $('<option></option>')
              .val(item.id)
              .text(item.title)
              .attr('data-edit-url', item.edit_url || '');

            if (item.id === savedVal) {
              $opt.prop('selected', true);
            }
            $select.append($opt);
          });

          layoutSelectLoaded[postType] = true;

          // Re-bind everything after options are loaded
          updateEditLink($select);
          $select.trigger('change');
          if (callback) callback();
        }
      });
    }

    // Load all layout selects immediately
    $('.ppseries-layout-select[data-post-type]').each(function() {
      var $sel = $(this);
      loadLayoutSelect($sel, function() {
        bindEditLinks();
        updateTokenSidebar();
      });
    });

    // -------------------------------------------------------------
    //   Checkbox → disable selection + dependent rows (all tabs)
    // -------------------------------------------------------------
    function bindToggleCheckbox(checkboxId, selectionId, dependentRows) {
      var $checkbox  = $('#' + checkboxId);
      var $selection = $('#' + selectionId);
      var $selectionRow = $selection.closest('tr');
      var $rows = $(dependentRows);

      function apply() {
        var enabled = $checkbox.is(':checked');
        $selection.prop('disabled', !enabled);
        var $fallbackUnpublishedRow = $('#series_post_list_unpublished_post_template').closest('tr');
        if (enabled) {
          $selectionRow.css('opacity', '');
        } else {
          $selectionRow.css('opacity', '0.5');
          $rows.hide();
          if ($fallbackUnpublishedRow.length) {
            $fallbackUnpublishedRow.hide();
          }
        }
      }

      if ($checkbox.length && $selection.length) {
        apply();
        $checkbox.on('change', function() {
          apply();
          // Re-run the selection toggle so template rows show/hide correctly
          $selection.trigger('change');
        });
      }
    }

    bindToggleCheckbox(
      'auto_tag_toggle',
      'series_post_list_box_selection',
      '#series_post_list_position_row, #series_post_list_template_row, #series_post_list_post_linked_post_row, #series_post_list_unpublished_post, #series_post_list_currentpost_row'
    );

    bindToggleCheckbox(
      'auto_tag_seriesmeta_toggle',
      'series_post_details_selection',
      '#series_metabox_position_row, #series_meta_template_row, #series_meta_excerpt_template_row, #limit_series_meta_to_single_row'
    );

    bindToggleCheckbox(
      'auto_tag_nav_toggle',
      'series_post_navigation_selection',
      '#series_post_nav_template_row, #series_navigation_box_position_row, #series_nextpost_nav_custom_text_row, #series_prevpost_nav_custom_text_row, #series_firstpost_nav_custom_text_row'
    );

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
      var templateRows = $('#series_meta_template_row, #series_meta_excerpt_template_row, #limit_series_meta_to_single_row, #series_metabox_position_row');
      if (selectedValue && selectedValue !== '') {
        templateRows.hide();
      } else {
        templateRows.show();
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
        $('#series_post_list_box_settings-series-tab').trigger('click');
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
    function getPostListLegacyTemplateRows() {
      var $rows = $('#series_post_list_position_row, #series_post_list_template_row, #series_post_list_post_linked_post_row, #series_post_list_unpublished_post, #series_post_list_currentpost_row');
      var $unpublishedRowFallback = $('#series_post_list_unpublished_post_template').closest('tr');
      if ($unpublishedRowFallback.length) {
        $rows = $rows.add($unpublishedRowFallback);
      }
      return $rows;
    }

    function toggleTemplateField() {
      var selectedValue = $('#series_post_list_box_selection').val();
      var legacyTemplateSettings = getPostListLegacyTemplateRows();

      if (selectedValue && selectedValue !== '') {
        legacyTemplateSettings.hide();
      } else {
        legacyTemplateSettings.show();
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
      var templateRows = $('#series_post_nav_template_row, #series_navigation_box_position_row, #series_nextpost_nav_custom_text_row, #series_prevpost_nav_custom_text_row, #series_firstpost_nav_custom_text_row');

      if (selectedValue && selectedValue !== '') {
        templateRows.hide();
      } else {
        templateRows.show();
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

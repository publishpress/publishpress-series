jQuery(document).ready(function($) {
	//Thickbox
    var l10n = $.extend({
        resetTitle: 'Are you sure you want to Reset?'
    }, window.ppSeriesOptions || {});
    var $resetTrigger = null;
    var focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

    // Keep Tab / Shift+Tab inside the ThickBox window while the reset dialog is open.
    function trapResetDialogFocus(event) {
        if (event.key !== 'Tab' && event.keyCode !== 9) {
            return;
        }
        var $focusable = $('#TB_window').find(focusableSelector).filter(':visible');
        if (!$focusable.length) {
            return;
        }
        var first = $focusable.get(0);
        var last = $focusable.get($focusable.length - 1);

        if (event.shiftKey && (document.activeElement === first || !$.contains($('#TB_window')[0], document.activeElement))) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !$.contains($('#TB_window')[0], document.activeElement))) {
            event.preventDefault();
            first.focus();
        }
    }

    $(document).on('click','input[name="option_reset"]', function(){
        $resetTrigger = $(this);
        tb_show(l10n.resetTitle,'TB_inline?height=155&amp;width=300&amp;inlineId=TBcontent');

        $(document).off('keydown.ppseriesReset').on('keydown.ppseriesReset', trapResetDialogFocus);

        // Focus the first dialog button (No) once ThickBox has moved the content in.
        setTimeout(function () {
            $('#TB_window #TBcancel').trigger('focus');
        }, 0);

        return false;
    });

    // ThickBox fires these when the window closes (No, X button or Escape).
    function restoreResetFocus() {
        $(document).off('keydown.ppseriesReset');
        if ($resetTrigger && $resetTrigger.length) {
            var $trigger = $resetTrigger;
            $resetTrigger = null;
            setTimeout(function () {
                $trigger.trigger('focus');
            }, 0);
        }
    }
    $('body').on('thickbox:removed', restoreResetFocus);
    $(document).on('tb_unload', '#TB_window', restoreResetFocus);

    $('#TBcancel').click(function(){
        tb_remove();
    });

    $('#TBsubmit').click(function(){
		$('input.reset_option', '#series_options' ).val('1');
        document.series_options.submit();
    });

    $('#custom_css').click(function(e) {
      var is_checked = $('input#custom_css').is(':checked');
  
      if (is_checked) {
        $("input[class='css_style']").attr('disabled', false);
      }
  
      if (!is_checked) {
        $("input[class='css_style']").attr('disabled', true);
      }
    });

    // Overview page tab settings
    $('#series_overview_page_layout').on('change', function(){
        // Show / Hide columns field
        if($(this).val() == 'grid'){
            $('.ppseries-settings-table').find('tr.pps-row-columns').show();
        } else {
            $('.ppseries-settings-table').find('tr.pps-row-columns').hide();
        }
        // Show / Hide layout description
        if($(this).val() != 'default'){
            $('#series_overview_page_layout_desc').show();
        } else {
            $('#series_overview_page_layout_desc').hide();
        }
	});

    // Show / Hide layout description on load
    if($('#series_overview_page_layout').val() == 'default'){
        $('#series_overview_page_layout_desc').hide();
    }
});

jQuery(document).ready(function($) {
	//Thickbox
    $(document).on('click','input[name="option_reset"]', function(){
        tb_show('Are you sure you want to Reset?','TB_inline?height=155&amp;width=300&amp;inlineId=TBcontent');
        return false;
    });

    $('input#TBcancel').click(function(){
        tb_remove();
    });

    $('input#TBsubmit').click(function(){
		$('input.reset_option', '#series_options' ).val('1');
        document.series_options.submit();
    });

    $('#auto_tag_toggle').click(function(e) {
      var is_checked = $('input#auto_tag_toggle').is(':checked');

      if (is_checked) {
        $(".series_post_list_limit_row").show();
      }
  
      if (!is_checked) {
        $(".series_post_list_limit_row").hide();
      }
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
jQuery(document).ready(function($) {
	//Thickbox

        $('input[name="option_reset"]').live('click',function(){
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
});
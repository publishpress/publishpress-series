<script lang='javascript'><!--
var seriesdropdown = document.getElementById("series");
    function onSeriesChange() {
		if ( seriesdropdown.options[seriesdropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/?taxonomy=series&amp;term="+seriesdropdown.options[seriesdropdown.selectedClass].value;
		}
    }
    seriesdropdown.onchange = onSeriesChange;
--></script>
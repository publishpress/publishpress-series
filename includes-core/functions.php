<?php
if (!function_exists('pp_series_core_activation')) {
    //activation functions/codes
    function pp_series_core_activation()
    {
		pp_series_upgrade_function();
    }
}
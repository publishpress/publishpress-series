<?php
if (!function_exists('pp_series_core_activation')) {
    //activation functions/codes
    function pp_series_core_activation()
    {
		pp_series_upgrade_function();
        update_option('pp_series_flush_rewrite_rules', 1);
        update_option('org_series_is_initialized', 0);
    }
}

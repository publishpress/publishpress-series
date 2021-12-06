<?php
if (!function_exists('pp_series_core_activation')) {
    //activation functions/codes
    function pp_series_core_activation()
    {

		$version = get_option('pp_series_version');
		if ( !$version || $version < '2.7.1' || $version == '2.7.1' ) {
            // Init roles
            if ( function_exists( 'get_role' ) ) {
                $role = get_role( 'administrator' );
                if ( null !== $role && ! $role->has_cap( 'manage_publishpress_series' ) ) {
                    $role->add_cap( 'manage_publishpress_series' );
                }
            }
            update_option('pp_series_version', ORG_SERIES_VERSION);
		}

    }
}
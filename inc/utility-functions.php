<?php

if(!function_exists('orgSeries_dir')){
	/**
	* Nifty function to get the name of the directory orgSeries is installed in.
	*/
	function orgSeries_dir(){
		if (stristr(SERIES_FILE_PATH, '/') ){
			$orgdir = explode('/plugins/', dirname(SERIES_FILE_PATH));
		} else{
			$orgdir = explode('\\plugins\\', dirname(SERIES_FILE_PATH));
		}
    return str_replace('\\' , '/', end($orgdir)); //takes care of MS slashes
	}
}

if(!function_exists('pps_os_version_requirement_notice')){
function pps_os_version_requirement_notice() {
?>
        <div class="notice notice-error">
            <p>
                <?php
                    printf(
                        esc_html__(
                            'Publishpress Series %1$srequires PHP 5.6%2$s or greater.  Your website does not meet the requirements so the plugin is not fully activated.',
                            'organize-series'
                        ),
                        '<strong>',
                        '</strong>'
                    );
                    echo '<br>';
                    printf(
                        esc_html__(
                            'Most web hosts provide an easy path to update the php version on your website.  We recommend updating to PHP 7 or greater. Before you update, you will want to make sure other plugins and your theme are compatible (see %1$sthis article for more info%2$s).',
                            'organize-series'
                        ),
                        '<a href="https://kb.yoast.com/kb/site-ready-php-7/">',
                        '</a>'
                    );
                ?>
            </p>
            <p>
                <?php
                    esc_html_e(
                        'To remove this notice you can either deactivate the plugin or upgrade the php version on your server.',
                        'organize-series'
                    )
                ?>
            </p>
        </div>
<?php
    }
	}

	if(!function_exists('pp_series_free_version_init')){
	function pp_series_free_version_init()
		{
    	if (is_admin() && !defined('SERIES_PRO_VERSION')) {
        	require_once(SERIES_PATH . '/includes-core/PPSeriesCoreAdmin.php');
        	new \PublishPress\Series\PPSeriesCoreAdmin();
    }
		}
	}

	if(!function_exists('ppseries_settings_template_tabbed_callback')){
		add_filter('ppseries_settings_series_templates_settings_tabbed', 'ppseries_settings_template_tabbed_callback');
		function ppseries_settings_template_tabbed_callback(){
			return true;
		}
	}

?>

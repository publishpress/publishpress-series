<?php
/**
 * Publishpress Series Options page loader.
 *
 * This file loads modular settings files from inc/settings/.
 * Each tab, validation, page rendering, and upgrade logic lives in its own file.
 *
 * @package Publishpress Series
 * @since 2.2
 */

// Load modular settings files
require_once dirname(__FILE__) . '/inc/settings/settings-validation.php';
require_once dirname(__FILE__) . '/inc/settings/settings-page.php';
require_once dirname(__FILE__) . '/inc/settings/settings-upgrade.php';
require_once dirname(__FILE__) . '/inc/settings/ajax-layout-options.php';

// Admin menu
add_action('admin_menu', 'orgseries_create_options');

// Settings link on plugins page
add_filter('plugin_action_links', 'inject_orgseries_settings_link', 10, 2 );

// Upgrade and notices
add_action('admin_init', 'publishpress_series_process_upgrade');
add_action('admin_init', 'publishpress_series_sync_menu_order');
add_action('admin_notices', 'publishpress_series_upgrade_require_changes');
add_action('after_plugin_row', 'publishpress_series_upgrade_require_row_notice', 10, 2);

// Enqueue tooltips assets
add_action('admin_enqueue_scripts', 'ppseries_enqueue_tooltips_assets');


/**
 * Add Settings link to plugins.
 */
 function inject_orgseries_settings_link($links, $file) {
	static $this_plugin;
	global $orgseries;
	if ( !$this_plugin ) $this_plugin = PPSERIES_BASE_NAME;

	if ( $file == $this_plugin ) {
		$settings_link = '<a href="'. esc_url(ppseries_series_settings_page()) .'">'.esc_html__("Settings", 'organize-series').'</a>';
		 array_unshift($links, $settings_link);
	}
	return $links;
 }

//add orgSeries to the options submenu and register settings
function orgseries_create_options() {
	global $orgseries;

	$page = add_menu_page(
		__('PublishPress Series Options', 'organize-series'),
		__('Series', 'organize-series'),
		'manage_publishpress_series',
		'orgseries_options_page',
		'orgseries_option_page',
		'dashicons-book-alt',
		68
	);
    add_submenu_page(
        'orgseries_options_page',
        __('Settings', 'organize-series'),
        __('Settings', 'organize-series'),
        'manage_publishpress_series',
        'orgseries_options_page',
        'orgseries_option_page'
    );

    do_action('publishpress_series_admin_menu_page');

	add_action('admin_init', 'orgseries_options_init');
	add_action('admin_print_scripts-' . $page, 'orgseries_options_scripts');

}

// enqueue tooltips assets
function ppseries_enqueue_tooltips_assets() {

    if (isset($_GET['page']) && $_GET['page'] === 'orgseries_options_page') {

        wp_enqueue_style(
            'pp-tooltips-library', 
            plugin_dir_url(__FILE__) . 'assets/css/tooltip.min.css',
            array(),
            ORG_SERIES_VERSION
        );
        

        wp_enqueue_script(
            'pp-tooltips-library',
            plugin_dir_url(__FILE__) . 'assets/js/tooltip.min.js',
            array(),
            ORG_SERIES_VERSION,
            true
        );
    }
}


function orgseries_options_scripts() {
	wp_enqueue_script( 'orgseries_options' );
}

//register orgseries options
function orgseries_options_init() {
	$orgseries_options = 'orgseries_options';
	$org_opt = 'org_series_options';
	register_setting($orgseries_options, $org_opt, 'orgseries_validate');

	// Load tab files — each registers its own section + fields
	require_once dirname(__FILE__) . '/inc/settings/tab-post-list-box.php';
	require_once dirname(__FILE__) . '/inc/settings/tab-post-details.php';
	require_once dirname(__FILE__) . '/inc/settings/tab-post-navigation.php';
	require_once dirname(__FILE__) . '/inc/settings/tab-taxonomy.php';
	require_once dirname(__FILE__) . '/inc/settings/tab-metabox.php';
	require_once dirname(__FILE__) . '/inc/settings/tab-legacy.php';

	if ( ! pp_series_is_pro_active() ) {
		require_once dirname(__FILE__) . '/inc/settings/tab-post-types.php';
		require_once dirname(__FILE__) . '/inc/settings/tab-pro-features.php';
	}

	require_once dirname(__FILE__) . '/inc/settings/tab-advanced.php';

	// Hook for Pro to add additional settings sections
	do_action('publishpress_series_register_settings_sections');

  add_filter( 'ppseries_admin_settings_tabs', 'ppseries_filter_admin_settings_tabs');
}


function ppseries_filter_admin_settings_tabs($settings_tabs){

    if (!pp_series_is_pro_active()) {
        $settings_tabs['series_cpt_settings'] = esc_html__('Post Types', 'organize-series');
    }

    $settings_tabs['series_taxonomy_base_settings'] = esc_html__('Taxonomy', 'organize-series');
    $settings_tabs['series_metabox_settings'] = esc_html__('Metabox', 'organize-series');
    $settings_tabs['series_legacy_settings'] = esc_html__('Legacy', 'organize-series');

    if (!pp_series_is_pro_active()) {
        $settings_tabs['series_addon_settings'] = esc_html__('Pro Features', 'organize-series');
    }

    $settings_tabs['series_uninstall_settings'] = esc_html__('Advanced', 'organize-series');
    
    return $settings_tabs;
}
?>

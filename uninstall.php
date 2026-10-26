<?php
/**
 * Uninstall PublishPress Series.
 *
 * WordPress runs this file without loading the plugin, so it must not call
 * any plugin functions. The "Delete all PublishPress Series data" setting is
 * read directly: a network-wide site option on multisite (Super Admin-only),
 * or the per-site org_series_options value on single-site installs.
 */

defined('ABSPATH') || exit;

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$ppseries_delete_site_data = function () {
    global $wpdb;

    $taxonomy = get_option('pp_series_taxonomy_slug');
    $taxonomy = (is_string($taxonomy) && '' !== trim($taxonomy)) ? $taxonomy : 'series';

    // The plugin isn't loaded, so register the taxonomy just long enough to delete its terms.
    if (! taxonomy_exists($taxonomy)) {
        register_taxonomy($taxonomy, 'post', array('public' => false, 'rewrite' => false));
    }

    $series_ids = get_terms(
        array(
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'fields'     => 'ids',
        )
    );

    if (! is_wp_error($series_ids)) {
        foreach ($series_ids as $series) {
            wp_delete_term((int) $series, $taxonomy);
        }
    }

    $meta_key = '%' . $wpdb->esc_like('_series_part') . '%';
    $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $meta_key));
    $table_name = $wpdb->prefix . 'orgseriesicons';
    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Plugin-owned table name uses the WordPress database prefix and a static suffix.
    $wpdb->query('DROP TABLE IF EXISTS ' . $table_name);
    delete_option('org_series_options');
    delete_option('org_series_is_initialized');
    delete_option('org_series_version');
    delete_option('org_series_oldversion');
    delete_option('orgSeries_latest_series_widget');
    delete_option('orgSeries_widget');
    delete_option('series_icon_path');
    delete_option('series_icon_url');
    delete_option('series_icon_filetypes');

    // Upgrade flags, so a later reinstall recreates the icons table.
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM $wpdb->options WHERE option_name LIKE %s",
            $wpdb->esc_like('pp_series_') . '%' . $wpdb->esc_like('_upgraded')
        )
    );
    wp_cache_delete('alloptions', 'options');
};

if (is_multisite()) {
    if (1 !== (int) get_site_option('pp_series_kill_on_delete', 0)) {
        return;
    }

    $ppseries_site_ids = get_sites(
        array(
            'fields'     => 'ids',
            'number'     => 0,
            'network_id' => get_current_network_id(),
        )
    );

    foreach ($ppseries_site_ids as $ppseries_site_id) {
        switch_to_blog((int) $ppseries_site_id);
        $ppseries_delete_site_data();
        restore_current_blog();
    }

    delete_site_option('pp_series_kill_on_delete');
} else {
    $ppseries_settings = get_option('org_series_options', array());

    if (! is_array($ppseries_settings) || empty($ppseries_settings['kill_on_delete']) || 1 !== (int) $ppseries_settings['kill_on_delete']) {
        return;
    }

    $ppseries_delete_site_data();
}

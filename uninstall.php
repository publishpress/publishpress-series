<?php
global $wpdb, $orgseries;
$settings = $orgseries->settings;
$delete_series = $settings['kill_on_delete'];

if ($delete_series == 1) {
    $taxonomy = ppseries_get_series_slug();
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
}

<?php

global $wpdb, $orgseries;
$settings = $orgseries->settings;
    $delete_series = $settings['kill_on_delete'];

if ($delete_series == 1) {
    $query = $wpdb->prepare("SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = %s", ppseries_get_series_slug());
    $series_ids = $wpdb->get_results($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Prepared immediately above.
    foreach ($series_ids as $series) {
        $series = (int) $series->term_id;

        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $series));
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d", $series));
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->terms WHERE term_id = %d", $series));
    }

        $meta_key = '%' . $wpdb->esc_like('_series_part') . '%';
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key LIKE %s", $meta_key));
        $table_name = $wpdb->prefix . "orgseriesicons";
        $drop_query = "DROP TABLE IF EXISTS " . esc_sql($table_name);
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- DDL table identifier is the static plugin table with the current site prefix.
        $wpdb->query($drop_query);
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

<?php
global $wpdb, $orgseries;
$settings = $orgseries->settings;
$delete_grouping = $settings['kill_grouping_on_delete'];

if ( $delete_grouping == 1 ) {
	$type = 'series_grouping';
	$query = "SELECT ID from $wpdb->posts WHERE post_type = '$type'";
	$series_grouping_posts = $wpdb->get_results($query);
	foreach ( $series_grouping_posts as $series ) {	
		wp_delete_post($series->ID,true);
	}
	delete_option('orgseries_grouping_import_completed'); //so if reactivated later it will work!
	delete_option('orgseries_grouping_version'); //remove ALL traces!
}
?>
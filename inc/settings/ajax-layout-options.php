<?php
/**
 * AJAX handler: returns layout options for a given post type.
 *
 * Used by the settings page to lazy-load dropdown options
 * instead of running get_posts() on every page load.
 *
 * @package Publishpress Series
 */

add_action( 'wp_ajax_ppseries_get_layout_options', 'ppseries_ajax_get_layout_options' );

function ppseries_ajax_get_layout_options() {
	check_ajax_referer( 'ppseries_settings_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_publishpress_series' ) ) {
		wp_send_json_error( 'Permission denied', 403 );
	}

	$post_type = isset( $_GET['post_type'] ) ? sanitize_key( $_GET['post_type'] ) : '';

	$allowed = [
		'pps_post_list_box',
		'pps_post_details',
		'pps_post_navigation',
	];

	if ( ! in_array( $post_type, $allowed, true ) ) {
		wp_send_json_error( 'Invalid post type', 400 );
	}

	$posts = get_posts( [
		'post_type'   => $post_type,
		'post_status' => 'publish',
		'numberposts' => -1,
		'orderby'     => 'title',
		'order'       => 'ASC',
	] );

	$options = [];
	foreach ( $posts as $post ) {
		$edit_url = get_edit_post_link( $post->ID, 'raw' );
		$options[] = [
			'id'       => $post->ID,
			'title'    => $post->post_title,
			'edit_url' => $edit_url ? $edit_url : '',
		];
	}

	wp_send_json_success( $options );
}

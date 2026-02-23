<?php
/**
 * Settings validation and sanitization.
 *
 * This file handles all form input validation for the PublishPress Series
 * settings page. It is called automatically by the WordPress Settings API
 * via register_setting().
 *
 * @package Publishpress Series
 */

/**
 * Sanitize a checkbox (returns 1 or 0).
 */
function ppseries_sanitize_checkbox( $input, $key ) {
	return ( isset( $input[ $key ] ) && $input[ $key ] == 1 ) ? 1 : 0;
}

/**
 * Sanitize a text/template field (trim + stripslashes).
 *
 * Falls back to the existing saved value when the key is absent from $input,
 * which prevents data loss when the field is on a hidden tab.
 */
function ppseries_sanitize_text( $input, $key, $existing = [] ) {
	if ( isset( $input[ $key ] ) ) {
		return trim( stripslashes( (string) $input[ $key ] ) );
	}
	return isset( $existing[ $key ] ) ? $existing[ $key ] : '';
}

/**
 * Sanitize a slug (strip leading/trailing slashes).
 */
function ppseries_sanitize_slug( $input, $key ) {
	return isset( $input[ $key ] ) ? preg_replace( '/(^\/)|(\/$)/', '', $input[ $key ] ) : '';
}

/**
 * Sanitize an integer field.
 */
function ppseries_sanitize_int( $input, $key ) {
	return isset( $input[ $key ] ) ? (int) $input[ $key ] : 0;
}

/**
 * Register a temporary taxonomy for term migration.
 */
function ppseries_register_temporary_taxonomy() {
	$object_type  = apply_filters( 'orgseries_posttype_support', [ 'post' ] );
	$capabilities = [
		'manage_terms' => 'manage_publishpress_series',
		'edit_terms'   => 'manage_publishpress_series',
		'delete_terms' => 'manage_publishpress_series',
		'assign_terms' => 'manage_publishpress_series',
	];
	$labels = [
		'name'          => _x( 'Series', 'taxonomy general name', 'organize-series' ),
		'singular_name' => _x( 'Series', 'taxonomy singular name', 'organize-series' ),
		'search_items'  => __( 'Search Series', 'organize-series' ),
		'popular_items' => __( 'Popular Series', 'organize-series' ),
		'all_items'     => __( 'All Series', 'organize-series' ),
		'edit_item'     => __( 'Edit Series', 'organize-series' ),
		'update_item'   => __( 'Update Series', 'organize-series' ),
		'add_new_item'  => __( 'Add New Series', 'organize-series' ),
		'new_item_name' => __( 'New Series Name', 'organize-series' ),
		'menu_name'     => __( 'Manage Series', 'organize-series' ),
		'not_found'     => __( 'No series found', 'organize-series' ),
	];
	register_taxonomy( 'series', $object_type, [
		'update_count_callback' => '_os_update_post_term_count',
		'labels'                => $labels,
		'rewrite'               => [ 'slug' => 'series', 'with_front' => true ],
		'show_ui'               => true,
		'capabilities'          => $capabilities,
		'query_var'             => 'series',
	] );
}

/**
 * Migrate taxonomy terms from old slug to new slug.
 *
 * @return string Admin notice HTML.
 */
function ppseries_maybe_migrate_taxonomy_terms() {
	if ( ! isset( $_POST['migrate_series_terms'] ) || (int) $_POST['migrate_series_terms'] !== 1 ) {
		return ppseries_updated_notice();
	}

	global $wpdb;

	ppseries_register_temporary_taxonomy();

	$old_slug = get_option( 'pp_series_taxonomy_slug', 'series' );
	$raw_new  = isset( $_POST['org_series_options']['series_taxonomy_slug'] )
		? trim( $_POST['org_series_options']['series_taxonomy_slug'] )
		: '';
	$new_slug = ! empty( $raw_new ) ? sanitize_text_field( $raw_new ) : 'series';

	if ( $old_slug === $new_slug ) {
		return ppseries_updated_notice();
	}

	$terms = get_terms( [ 'hide_empty' => false, 'taxonomy' => $old_slug ] );
	$count = 0;

	foreach ( $terms as $term ) {
		$count++;
		$wpdb->update(
			$wpdb->prefix . 'term_taxonomy',
			[ 'taxonomy'         => $new_slug ],
			[ 'term_taxonomy_id' => $term->term_id ],
			[ '%s' ],
			[ '%d' ]
		);
	}

	return sprintf(
		'<div class="updated"><p>%s</p></div>',
		sprintf(
			esc_html__( '%1$s series migrated from "%2$s" to "%3$s" taxonomy. Settings updated.', 'organize-series' ),
			$count,
			$old_slug,
			$new_slug
		)
	);
}

/**
 * Standard "settings updated" notice.
 *
 * @return string Admin notice HTML.
 */
function ppseries_updated_notice() {
	return '<div class="updated"><p>' . esc_html__( 'PublishPress Series Plugin Options have been updated', 'organize-series' ) . '</p></div>';
}

/**
 * Main validation callback (called by register_setting).
 *
 * @param array $input Raw form input.
 * @return array Sanitized options.
 */
function orgseries_validate( $input ) {
	global $orgseries, $wp_rewrite;

	// --- Security checks ---------------------------------------------------
	check_admin_referer( 'publishpress_series_settings_nonce_action', 'publishpress_series_settings_nonce_field' );

	if ( ! current_user_can( 'manage_publishpress_series' ) ) {
		wp_die( esc_html__( 'Permission denied', 'organize-series' ) );
	}

	// --- Reset all options --------------------------------------------------
	if ( isset( $input['reset_option'] ) && $input['reset_option'] == 1 ) {
		if ( $orgseries->add_settings( true ) ) {
			$notice = '<div class="updated"><p>' . esc_html__( 'PublishPress Series Plugin Options have been RESET', 'organize-series' ) . '</p></div>';
			update_option( 'orgseries_update_message', $notice );
			return $orgseries->settings;
		}
	}

	// --- Taxonomy migration (if requested) ----------------------------------
	$notice = ppseries_maybe_migrate_taxonomy_terms();

	// --- Existing saved values (used as fallback for hidden-tab fields) ------
	$existing = get_option( 'org_series_options', [] );

	$newinput = [];

	/* Feature toggles (Post List Box / Post Details / Navigation) */
	$newinput['auto_tag_toggle']            = ppseries_sanitize_checkbox( $input, 'auto_tag_toggle' );
	$newinput['auto_tag_nav_toggle']        = ppseries_sanitize_checkbox( $input, 'auto_tag_nav_toggle' );
	$newinput['auto_tag_seriesmeta_toggle'] = ppseries_sanitize_checkbox( $input, 'auto_tag_seriesmeta_toggle' );

	/* Layout selections (which editor layout is active for each feature) */
	$default_box_id = PPS_Post_List_Box_Utilities::get_default_post_list_box_id() ?: '';
	$newinput['series_post_list_box_selection'] = isset( $input['series_post_list_box_selection'] )
		? intval( $input['series_post_list_box_selection'] )
		: $default_box_id;

	$default_details_id = PPS_Series_Post_Details_Utilities::get_default_series_post_details_id() ?: '';
	$newinput['series_post_details_selection'] = isset( $input['series_post_details_selection'] )
		? intval( $input['series_post_details_selection'] )
		: $default_details_id;

	$default_nav_id = class_exists( 'PPS_Series_Post_Navigation_Utilities' )
		? PPS_Series_Post_Navigation_Utilities::get_default_post_navigation_id()
		: '';
	$newinput['series_post_navigation_selection'] = isset( $input['series_post_navigation_selection'] )
		? intval( $input['series_post_navigation_selection'] )
		: $default_nav_id;

	/* Position settings (where each feature renders relative to content) */
	$newinput['series_post_list_position']      = ppseries_sanitize_text( $input, 'series_post_list_position', $existing );
	$newinput['series_metabox_position']         = ppseries_sanitize_text( $input, 'series_metabox_position', $existing );
	$newinput['series_navigation_box_position']  = ppseries_sanitize_text( $input, 'series_navigation_box_position', $existing );

	/* Custom templates (HTML with tokens — fall back to saved values) */
	$newinput['series_post_list_template']             = ppseries_sanitize_text( $input, 'series_post_list_template', $existing );
	$newinput['series_post_list_post_template']        = ppseries_sanitize_text( $input, 'series_post_list_post_template', $existing );
	$newinput['series_post_list_currentpost_template'] = ppseries_sanitize_text( $input, 'series_post_list_currentpost_template', $existing );
	$newinput['series_meta_template']                  = ppseries_sanitize_text( $input, 'series_meta_template', $existing );
	$newinput['series_meta_excerpt_template']          = ppseries_sanitize_text( $input, 'series_meta_excerpt_template', $existing );
	$newinput['series_table_of_contents_box_template'] = ppseries_sanitize_text( $input, 'series_table_of_contents_box_template', $existing );
	$newinput['series_post_nav_template']              = ppseries_sanitize_text( $input, 'series_post_nav_template', $existing );
	$newinput['series_nextpost_nav_custom_text']       = ppseries_sanitize_text( $input, 'series_nextpost_nav_custom_text', $existing );
	$newinput['series_prevpost_nav_custom_text']       = ppseries_sanitize_text( $input, 'series_prevpost_nav_custom_text', $existing );
	$newinput['series_firstpost_nav_custom_text']      = ppseries_sanitize_text( $input, 'series_firstpost_nav_custom_text', $existing );
	$newinput['latest_series_before_template']         = ppseries_sanitize_text( $input, 'latest_series_before_template', $existing );
	$newinput['latest_series_inner_template']          = ppseries_sanitize_text( $input, 'latest_series_inner_template', $existing );
	$newinput['latest_series_after_template']          = ppseries_sanitize_text( $input, 'latest_series_after_template', $existing );

	/* Taxonomy settings */
	$raw_slug = isset( $input['series_taxonomy_slug'] ) ? trim( $input['series_taxonomy_slug'] ) : '';
	$newinput['series_taxonomy_slug'] = ! empty( $raw_slug ) ? $raw_slug : 'series';
	$newinput['series_custom_base']   = ppseries_sanitize_slug( $input, 'series_custom_base' );

	/* Metabox settings */
	$newinput['metabox_show_add_new']              = ppseries_sanitize_checkbox( $input, 'metabox_show_add_new' );
	$newinput['metabox_show_post_title_in_widget'] = ppseries_sanitize_checkbox( $input, 'metabox_show_post_title_in_widget' );
	$newinput['metabox_series_order']              = isset( $input['metabox_series_order'] )
		? trim( stripslashes( $input['metabox_series_order'] ), 1 )
		: 'default';

	/* Legacy: CSS & Icons */
	$newinput['custom_css']       = ppseries_sanitize_checkbox( $input, 'custom_css' );
	$newinput['series_css_tougle'] = isset( $input['series_css_tougle'] )
		? trim( stripslashes( $input['series_css_tougle'] ), 1 )
		: 'default';

	$newinput['series_icon_width_series_page']  = ppseries_sanitize_int( $input, 'series_icon_width_series_page' );
	$newinput['series_icon_width_post_page']    = ppseries_sanitize_int( $input, 'series_icon_width_post_page' );
	$newinput['series_icon_width_latest_series'] = ppseries_sanitize_int( $input, 'series_icon_width_latest_series' );

	/* Legacy: Series Overview / TOC */
	$newinput['series_overview_page_layout']  = ppseries_sanitize_text( $input, 'series_overview_page_layout', $existing );
	$newinput['series_overview_page_columns'] = ppseries_sanitize_int( $input, 'series_overview_page_columns' );
	$newinput['series_toc_url']               = ppseries_sanitize_slug( $input, 'series_toc_url' );
	$newinput['series_toc_title']             = ppseries_sanitize_text( $input, 'series_toc_title', $existing );
	$newinput['series_perp_toc']              = isset( $input['series_perp_toc'] )
		? trim( preg_replace( '/[^0-9]/', '', $input['series_perp_toc'] ) )
		: '';

	if ( empty( $newinput['series_toc_url'] ) ) {
		$newinput['series_toc_url'] = false;
	}

	$newinput['series_posts_orderby']      = ppseries_sanitize_text( $input, 'series_posts_orderby', $existing );
	$newinput['series_posts_order']        = ppseries_sanitize_text( $input, 'series_posts_order', $existing );
	$newinput['series_post_list_limit']    = ppseries_sanitize_text( $input, 'series_post_list_limit', $existing );
	$newinput['limit_series_meta_to_single'] = ppseries_sanitize_checkbox( $input, 'limit_series_meta_to_single' );

	/* Advanced */
	$newinput['kill_on_delete'] = ppseries_sanitize_checkbox( $input, 'kill_on_delete' );
	$newinput['orgseries_api'] = isset( $input['orgseries_api'] ) ? trim( $input['orgseries_api'] ) : '';

	/* Finalize */
	update_option( 'pp_series_taxonomy_slug', $newinput['series_taxonomy_slug'] );

	$newinput['last_modified'] = gmdate( 'D, d M Y H:i:s', time() );
	$return_input = apply_filters( 'orgseries_options', $newinput, $input );

	update_option( 'orgseries_update_message', $notice );
	$wp_rewrite->flush_rules();

	return $return_input;
}

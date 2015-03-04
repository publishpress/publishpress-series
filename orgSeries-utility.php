<?php
/**
 * This file contains all the utility functions called by other functions in orgSeries.  The following criteria determines code included in this file:
 * 1. Is it directly related to plugin setup/initialization/activation?  If so then the function goes in orgSeries.php not here.
 * 2. Is it a hook into the WordPress core?  Then it doesn't belong in here.
 * 3. Is it a "template tag" function?  Then it belongs in series-template-tags.php
 *
 * @package Organize Series WordPress Plugin
 * @since 2.2
*/

function _usort_series_by_part($a, $b) {
	if ($a['part'] > $b['part'] )
		return 1;
	elseif ( $a['part'] < $b['part'] )
		return -1;
	else
		return 0;
	}

function _usort_series_by_name($a, $b) {
	return strcmp( $a['ser_name'], $b['ser_name'] );
}

//This function is used to create an array of posts in a series including the order the posts are in the series.  Then it will sort the array so it is keyed in the order the posts are in.  Will return the array.

function get_series_order($posts, $postid = 0, $series_id = 0, $skip = TRUE, $only_published = TRUE) {
	if (!isset($posts)) return false; //don't have the posts array so can't do anything.

	if ( !is_array( $posts ) )
		$posts = array($posts);

	$series_posts = array();
	$key = 0;

	$postids = '';
	$cycle = 0;

	foreach ( $posts as $newposts ) {
		if ( $cycle != 0 ) $postids .= ', ';
		if (array_key_exists('object_id', $posts)) {
			$postids .= $newposts['object_id'];
		} else {
			$postids .= $newposts;
		}
		$cycle++;
	}
	$posttypes = apply_filters('orgseries_posttype_support', array('post') );
	$args = array(
		'post_status' => 'any',
		'include' => $postids,
		'post_type' => $posttypes );
	$posts = get_posts($args);
	$meta_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);

	foreach ($posts as $spost) {
		if ( ( $spost->post_status == 'publish' || $spost->post_status == 'private' ) || !$only_published ) {

			if ($skip && $spost->ID == $postid) {
				continue;
			}

			$currentpart = get_post_meta($spost->ID, $meta_key, true);
			$series_posts[$key]['id'] = $spost->ID;
			$series_posts[$key]['part'] = $currentpart;
		$key++;
		}
	}

	if (count($series_posts) > 1)
		usort( $series_posts, '_usort_series_by_part' );

	return $series_posts;
}

/** Replaces tokens (set in orgSeries options) with the relevant values **/
/** NOTE: %postcontent% is NOT replaced with this function...it happens in the content filter function **/
function token_replace($replace, $referral = 'other', $id = 0, $ser_ID = 0) {
	global $post, $orgseries;
	$p_id = ( $id == 0 ) ? $post->ID : $id;
	$ser_id = ( $ser_ID == 0 ) ? $id : $ser_ID;

	//$p_id = (empty($post->ID) || $post->ID == '') ? $id : $post->ID;
	$settings = $orgseries->settings;
	$replace = apply_filters('pre_orgseries_token_replace', $replace, $referral, $id, $p_id, $ser_id);
	if ( 'post-list' == $referral  ) {
		$ser_width = $settings['series_icon_width_post_page'];
		 } elseif ( 'latest_series' == $referral ) {
		 $ser_width = $settings['series_icon_width_latest_series'];
		} else {
		 $ser_width = $settings['series_icon_width_series_page'];
		 }
	if ( 'series-toc' == $referral  || 'latest_series' == $referral ) {
		$replace = str_replace('%total_posts_in_series%', wp_postlist_count($ser_id), $replace);
	} else {
		$replace = str_replace('%total_posts_in_series%', wp_postlist_count($ser_id), $replace);
	}

	if( stristr($replace, '%series_icon%') )
	$replace = str_replace('%series_icon%', get_series_icon('fit_width=' .  $ser_width . '&link=0&series=' . $ser_id . '&display=0'), $replace);
	if( stristr($replace, '%series_icon_linked%') )
	$replace = str_replace('%series_icon_linked%', get_series_icon('fit_width= ' . $ser_width . '&series=' . $ser_id . '&display=0'), $replace);
	if( stristr($replace, '%series_title%') )
	$replace = str_replace('%series_title%', the_series_title($ser_id, FALSE), $replace);
	if( stristr($replace, '%series_title_linked%') )
	$replace = str_replace('%series_title_linked%', the_series_title($ser_id), $replace);
	if( stristr($replace, '%post_title_list%') )
	$replace = str_replace('%post_title_list%', get_series_posts($id, $referral), $replace);
	if( stristr($replace, '%post_title%') )
	$replace = str_replace('%post_title%', series_post_title($id, FALSE), $replace);
	if( stristr($replace, '%post_title_linked%') )
	$replace = str_replace('%post_title_linked%', series_post_title($id), $replace);
	if( stristr($replace, '%series_part%') )
	$replace = str_replace('%series_part%', wp_series_part($p_id, $ser_id), $replace);
	if( stristr($replace, '%series_description%') )
	$replace = str_replace('%series_description%', series_description($ser_id), $replace);
	if( stristr($replace, '%next_post%') )
	$replace = str_replace('%next_post%', wp_series_nav($id), $replace);
	if( stristr($replace, '%previous_post%') )
	$replace = str_replace('%previous_post%', wp_series_nav($id, FALSE), $replace);
	if( stristr($replace, '%next_post_custom%') )
	$replace = str_replace('%next_post_custom%', wp_series_nav($id, TRUE, TRUE), $replace);
	if( stristr($replace, '%previous_post_custom%') )
	$replace = str_replace('%previous_post_custom%', wp_series_nav($id, FALSE, TRUE), $replace);

	$replace = apply_filters('post_orgseries_token_replace', $replace, $referral, $id, $p_id, $ser_id);
	return $replace;
}

//permalinks//
function get_series_permastruct() {
	global $wp_rewrite, $orgseries;
	$settings = $orgseries->settings;
	$custom_base = $settings['series_custom_base'];

	if (empty($wp_rewrite->permalink_structure)) {
		$series_structure = '';
		return false;
	}

	$series_token = '%' . SERIES_QUERYVAR . '%';

	if ( $custom_base == '' )
		$series_structure = trailingslashit( $wp_rewrite->front . SERIES_URL . "/$series_token");

	else
		$series_structure = trailingslashit( $wp_rewrite->root . $custom_base . "/$series_token");

	return $series_structure;
}

/**
*  Directly taken from WordPress -> walk_category_dropdown_tree().
* See notes for wp_dropdown_series() for details on why this is necessary rather than using wp_dropdown_category...
*/
function walk_series_dropdown_tree() {
	$args = func_get_args();
	// the user's options are the third parameter
	if ( empty($args[2]['walker']) || !is_a($args[2]['walker'], 'Walker') )
		$walker = new Walker_SeriesDropdown;
	else
		$walker = $args[2]['walker'];

	return call_user_func_array(array( &$walker, 'walk' ), $args );
}

/**
* Directly taken from WordPress -> Walker_CategoryDropdown. See notes for wp_dropdown_series() for details on why this is necessary.
*/
class Walker_SeriesDropdown extends Walker {
	var $tree_type = 'series';
	var $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

	function start_el(&$output, $series, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$pad = str_repeat('&nbsp;', $depth * 3);

		$series_name = apply_filters('list_series', $series->name, $series);
		if ( $args['context'] == 'quick-edit' ) {
			$output .= "\t<option class=\"level-$depth\" value=\"".$series->term_id."\"";
			if ( $series->term_id === $args['selected'] )
				$output .= ' selected="selected"';
		} else {
			$output .= "\t<option class=\"level-$depth\" value=\"".$series->slug."\"";
			if ( $series->slug === $args['selected'] )
				$output .= ' selected="selected"';
		}
		$output .= '>';
		$output .= $pad.$series_name;
		if ( $args['show_count'] )
			$output .= '&nbsp;&nbsp;('. $series->count .')';
		if ( $args['show_last_update'] ) {
			$format = 'Y-m-d';
			$output .= '&nbsp;&nbsp;' . gmdate($format, $series->last_update_timestamp);
		}
		$output .= "</option>\n";
	}
}

function os_strarr_to_intarr($array) {
	if ( empty($array) ) return;
	array_walk($array, 'os_this_to_int');
	return $array;
}

function os_this_to_int(&$val, $key) {
		$val = (int) $val;
}



/**
 * Will update term count based on object types of series.
 *
 * Based off of _update_post_term_count but with the modification of including private post status for updating the count.
 *
 * @access private
 * @since 2.4.7
 * @uses $wpdb
 *
 * @param array $terms List of Term taxonomy IDs
 * @param object $taxonomy Current taxonomy object of terms
 */
function _os_update_post_term_count( $terms, $taxonomy ) {
	global $wpdb;

	$object_types = (array) $taxonomy->object_type;

	foreach ( $object_types as &$object_type )
		list( $object_type ) = explode( ':', $object_type );

	$object_types = array_unique( $object_types );

	if ( false !== ( $check_attachments = array_search( 'attachment', $object_types ) ) ) {
		unset( $object_types[ $check_attachments ] );
		$check_attachments = true;
	}

	if ( $object_types )
		$object_types = esc_sql( array_filter( $object_types, 'post_type_exists' ) );

	foreach ( (array) $terms as $term ) {
		$count = 0;

		// Attachments can be 'inherit' status, we need to base count off the parent's status if so
		if ( $check_attachments )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts p1 WHERE p1.ID = $wpdb->term_relationships.object_id AND ( post_status IN ( 'publish', 'private' ) OR ( post_status = 'inherit' AND post_parent > 0 AND ( SELECT post_status FROM $wpdb->posts WHERE ID = p1.post_parent ) = 'publish' ) ) AND post_type = 'attachment' AND term_taxonomy_id = %d", $term ) );

		if ( $object_types )
			$count += (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_status IN ( 'publish', 'private' ) AND post_type IN ('" . implode("', '", $object_types ) . "') AND term_taxonomy_id = %d", $term ) );

		/** This action is documented in wp-includes/taxonomy.php */
		do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );

		/** This action is documented in wp-includes/taxonomy.php */
		do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
	}
}
?>

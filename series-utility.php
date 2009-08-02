<?php
/**
 * This file contains all the utility functions called by other functions in orgSeries.  The following criteria determines code included in this file:
 * 1. Is it directly related to plugin setup/initialization/activation?  If so then the function goes in orgSeries.php not here.
 * 2. Is it a hook into the WordPress core?  Then it doesn't belong in here.
 * 3. Is it a "template tag" function?  Then it belongs in series-template-tags.php
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
*/

function series_includeTemplate() {
	if (is_series()) {
		$template = '';
		
		if ( file_exists(TEMPLATEPATH. "/" . SERIES_TEMPLATE) )
			$template = TEMPLATEPATH . "/" . SERIES_TEMPLATE;
		
		else
			$template = get_archive_template();
		
		if ($template) {
			load_template($template);
			exit;
		}
	}
	return;
}

function orgSeries_request($query_vars) {
	$query_vars['error'] = false;
	return $query_vars;
}

function series_init() {
	global $wp_rewrite;
	$settings = get_option('org_series_options');
	$series_toc_url = $settings['series_toc_url'];
	
	// make sure trailing slash is always added to REQUEST_URI  
	if ( stristr( $_SERVER['REQUEST_URI'], '/' ) ) {
		$toccheck = ltrim( $_SERVER['REQUEST_URI'], '/' );
		$toccheck = rtrim( $_SERVER['REQUEST_URI'], '/');
		$toccheck = $toccheck . '/';
	} else {
		$toccheck = ltrim( $_SERVER['REQUEST_URI'], '\\' );
		$toccheck = rtrim( $_SERVER['REQUEST_URI'], '\\' );
		$toccheck = $toccheck . '\\';
	}
		
	if ($series_toc_url && (strpos($toccheck, $series_toc_url) === 0) && (strlen($toccheck) == strlen($series_toc_url))) {
		//status_header( 200 ); 
		add_filter('request', 'orgSeries_request');
		add_action('template_redirect', 'orgSeries_toc_template');
	}
}

function orgSeries_toc_template() {
	global $wp_query;
	if (file_exists(TEMPLATEPATH . '/seriestoc.php')) {
		$template =  TEMPLATEPATH . '/seriestoc.php';
	} else {
		$template = ABSPATH . 'wp-content/plugins/' . SERIES_DIR .'/seriestoc.php';
	}
	
	function seriestoc_title( $title ) {
		$settings = get_option('org_series_options');
		$seriestoc_title = $settings['series_toc_title'];
		if ( $seriestoc_title == '' ) $seriestoc_title = __('Series Table of Contents');
		$title = $seriestoc_title . ' &laquo; ' . $title;
		return $title;
	}
	
	if ($template) {
		status_header( 200 ); //force correct header;
		$wp_query->is_series = true;
		$wp_query->is_404 = false;
		add_filter('wp_title', 'seriestoc_title');
		load_template($template);
		exit;
	}
	return;
}

function sort_series_page_join($join) {
	global $wpdb;
if (!is_series() || ( is_series() && is_feed() ) ) return $join;
	$join .= "LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id) ";
	return $join;
}

function sort_series_page_where($where) {
	global $wpdb;
	if (!is_series() || ( is_series() && is_feed() ) ) return $where;
	$part_key = SERIES_PART_KEY;
	$where .= " AND $wpdb->postmeta.meta_key = '$part_key' ";
	return $where;
}

function sort_series_page_orderby($ordering) {
	if (!is_series() || ( is_series() && is_feed() ) ) return $ordering;
	$settings = get_option('org_series_options');
	$orderby = $settings['series_posts_orderby'];
	if ( $orderby == 'meta_value' )
		$orderby = $orderby . '+ 0';
	$order = $settings['series_posts_order'];
	if (!isset($orderby)) $orderby = "post_date";
	if (!isset($order)) $order = "DESC";
	$ordering = " $orderby $order ";
	return $ordering;	
}

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

function get_series_order($posts, $postid = 0, $skip = TRUE) {
	if (!isset($posts)) return false; //don't have the posts array so can't do anything.
	
	if ( !is_array( $posts ) )
		$posts = array($posts);
		
	$series_posts = array();
	$key = 0;
	
	foreach ($posts as $spost) {
		if (array_key_exists('object_id', $posts)) {
			$spost_id = $spost['object_id'];
		} else {
			$spost_id = $spost;
		}
		
		/* 208 - fix by Matt Porter - to make sure unpublished posts are not made part of a series */
		$xpost = get_post($spost_id);
		if ($xpost->post_status == 'publish') {
			if ($skip && $spost_id == $postid) continue;
			$currentpart = get_post_meta($spost_id, SERIES_PART_KEY, true);
			$series_posts[$key]['id'] = $spost_id;
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
function token_replace($replace, $referral = 'other', $id = 0) {
	global $post;
	$p_id = $post->ID;
	$settings = get_option('org_series_options');
	if ( 'post-list' == $referral  ) {
		$ser_width = $settings['series_icon_width_post_page']; 
		 } elseif ( 'latest_series' == $referral ) {
		 $ser_width = $settings['series_icon_width_latest_series'];
		} else {
		 $ser_width = $settings['series_icon_width_series_page'];
		 }
	if ( 'series-toc' == $referral  || 'latest_series' == $referral ) {
		$replace = str_replace('%total_posts_in_series%', wp_postlist_count($id), $replace);
	} else {
		$replace = str_replace('%total_posts_in_series%', wp_postlist_count(), $replace);
	}
		
	if( stristr($replace, '%series_icon%') ) 
	$replace = str_replace('%series_icon%', get_series_icon('fit_width=' .  $ser_width . '&link=0&series=' . $id . '&display=0'), $replace);
	if( stristr($replace, '%series_icon_linked%') ) 
	$replace = str_replace('%series_icon_linked%', get_series_icon('fit_width= ' . $ser_width . '&series=' . $id . '&display=0'), $replace);
	if( stristr($replace, '%series_title%') ) 
	$replace = str_replace('%series_title%', the_series_title($id, FALSE), $replace);
	if( stristr($replace, '%series_title_linked%') ) 
	$replace = str_replace('%series_title_linked%', the_series_title($id), $replace);
	if( stristr($replace, '%post_title_list%') ) 
	$replace = str_replace('%post_title_list%', get_series_posts($id, $referral), $replace);
	if( stristr($replace, '%post_title%') ) 
	$replace = str_replace('%post_title%', series_post_title($id, FALSE), $replace);
	if( stristr($replace, '%post_title_linked%') ) 
	$replace = str_replace('%post_title_linked%', series_post_title($id), $replace);
	if( stristr($replace, '%series_part%') ) 
	$replace = str_replace('%series_part%', wp_series_part($p_id), $replace);
	if( stristr($replace, '%series_description%') ) 
	$replace = str_replace('%series_description%', series_description($id), $replace);
	if( stristr($replace, '%next_post%') ) 
	$replace = str_replace('%next_post%', wp_series_nav($id), $replace);
	if( stristr($replace, '%previous_post%') ) 
	$replace = str_replace('%previous_post%', wp_series_nav($id, FALSE), $replace);
	if( stristr($replace, '%next_post_custom%') ) 
	$replace = str_replace('%next_post_custom%', wp_series_nav($id, TRUE, TRUE), $replace);
	if( stristr($replace, '%previous_post_custom%') ) 
	$replace = str_replace('%previous_post_custom%', wp_series_nav($id, FALSE, TRUE), $replace);
	
	return $replace;
}

function series_rows( $series = 0 ) {
	if ( !$series )
		$series = get_series( 'hide_empty=0' );
	
	if ( $series ) {
		ob_start();
		foreach ( $series as $serial ) {
			echo "\t" . _series_row( $serial );
		}
		$output = ob_get_contents();
		ob_end_clean();
		
		$output = apply_filters('series_rows', $output);
		
		echo $output;
	} else {
		return false;
	}
}

function _series_row($series) {
	global $class, $wp_version;
	
	$series_icon = series_get_icons($series->term_id);
	$series_url = seriesicons_url();
	$icon = $series_url . '/' . $series_icon;
	
	if ( current_user_can( 'manage_series' ) ) {
		$edit = "<a href='edit.php?page=" .  SERIES_DIR . "/orgSeries-manage.php&amp;action=edit&amp;series_ID=$series->term_id' class='edit'>".__( 'Edit' )."</a></td>";
		
		if ( isset( $wp_version ) && $wp_version >= 2.5 )
			$edit .=  "<td><a href='" . wp_nonce_url("edit.php?page=" . SERIES_DIR . "/orgSeries-manage.php&action=delete&amp;series_ID=$series->term_id&noheader=1", 'delete-series_' . $series->term_id ) . "' class='delete'>".__( 'Delete' )."</a>";
		else
			$edit .= "<td><a href='" . wp_nonce_url("edit.php?page=" . SERIES_DIR . "/orgSeries-manage.php&amp;action=delete&amp;series_ID=$series->term_id", 'delete-series_' . $series->term_id ) . "' onclick=\"return deleteSomething('serial', $series->term_id, '" . js_escape(sprintf( __("You are about to delete the series '%s'. \nAll posts that were assigned to this series will be disassociated from the series.\n'OK' to delete, 'Cancel' to stop." ), $series->name  )) . "' );\" class='delete'>".__( 'Delete' )."</a>";
	} else
		$edit = '';
	
	if ( isset( $wp_version ) && $wp_version >= 2.5 )
		$class = " class='alternate'" == $class ? '' : " class='alternate'";
	else
		$class = ( ( defined( 'DOING_AJAX' ) && DOING_AJAX ) || " class='alternate'" == $class ) ? '' : " class='alternate'";
	$series->count = number_format_i18n( $series->count );
	$posts_count = ( $series->count > 0 ) ? "<a href='edit.php?series=$series->term_id'>$series->count</a>" : $series->count;  
	$output = "<tr id='serial-$series->term_id'$class>
		<th scope='row' style='text-align: center'>$series->term_id</th>
		<td>" . $series->name . "</td>
		<td>$series->description</td>
		<td align='center'>$posts_count</td>
		<td>";
		if (!$series_icon) {
			$output .= "No icon selected";
			} else {
			$output .= "<img src='" . $icon . "' title='" . $series_icon . "' width='50' alt='" . $icon . "' />";
			}
	$output .= "</td>
		<td>$edit</td>\n\t</tr>\n";
	
	return apply_filters('series_row', $output);
}

/**
 * All add_action() and add_filter() calls go here (that are not within functions/methods)
*/
add_filter('posts_join_paged','sort_series_page_join');
add_filter('posts_where', 'sort_series_page_where');
add_filter('posts_orderby','sort_series_page_orderby');

add_action( 'init', 'series_init', 0 );  
?>
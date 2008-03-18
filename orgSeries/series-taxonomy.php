<?php
/* This file is for all the Organize Series related Term Queries and "tags".  I just wanted to clean up the main plugin file a bit!
Please note:  I followed various WP core files for structuring code which made it easier than it could have been.  So I must give credit where credit is due!
 */
//these defines need to be moved to the orgSeries.php
 define('SERIES_QUERYVAR', 'series');  // get/post variable name for querying series from WP
 define('SERIES_URL', 'series'); //URL to use when querying series
 define('SERIES_TEMPLATE', 'series.php'); //template file to use for displaying series queries.
 define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
 define('SERIES_PART_KEY', 'series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to.
 define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.
 
/*utitlity functions -- perhaps add to their own file for better organization? */

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

/**
 * get_the_series() - calls up all the series info from the taxonomy tables.
*/	
function get_the_series( $id = false ) { 
	global $post, $term_cache, $blog_id;
	
	$id = (int) $id;
	
	if ( !$id )
		$id = (int) $post->ID;
	
	$series = get_object_term_cache($id, 'series');
	
	if (false === $series )
		$series = wp_get_object_terms($id, 'series');
		
	$series = apply_filters('get_the_series', $series); //adds a new filter for users to hook into

	if ( !empty($series) )
		usort($series, '_usort_terms_by_name');
	
	return $series;
}

// Get the ID of a series from its name
function get_series_ID($series_name='default') {
	$series = get_term_by('name', $cat_name, 'series');
	if ($series)
		return $series->term_id;
	return 0;
}
	
//This function is used to create an array of posts in a series including the order the posts are in the series.  Then it will sort the array so it is keyed in the order the posts are in.  Will return the array.
function get_series_order ($posts, $postid = 0, $skip = TRUE) {
	if (!isset($posts)) return false; //don't have the posts object so can't do anything.
	
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
		
		if ($skip && $spost_id == $postid) continue;
		$currentpart = get_post_meta($spost_id, SERIES_PART_KEY, true);
		$series_posts[$key]['id'] = $spost_id;
		$series_posts[$key]['part'] = $currentpart;
		$key++;
		}
	
	if (count($series_posts) > 1)
		usort( $series_posts, '_usort_series_by_part' );
		
	return $series_posts;
}
 
 /* functions referenced by other files */
function &get_series($args = '') {
	global $wpdb;
	
	$key = md5( serialize($args) );
	if ( $cache = wp_cache_get('get_series','series') )
		if ( isset( $cache[ $key ] ) )
			return apply_filters('get_series', $cache[$key],$args);
			
	$series = get_terms('series', $args);
	
	if ( empty($series) )
		return array();
		
	$cache[ $key ] = $series;
	wp_cache_set( 'get_series', $cache, 'series' );
	
	$series = apply_filters('get_series', $series, $args);
	return $series;
}
	
function &get_orgserial($orgserial, $output = OBJECT, $filter = 'raw') {
		return get_term($orgserial, 'series', $output, $filter);
}

//permalinks , rewrite rules etc.//
function get_series_permastruct() {
	global $wp_rewrite;
	
	if (empty($wp_rewrite->permalink_structure)) {
		$series_structure = '';
		return false;
	}
	
	$series_token = '%' . SERIES_QUERYVAR . '%';
	$series_structure = $wp_rewrite->front . SERIES_QUERYVAR . "/$series_token";
	return $series_structure;
}

function series_createRewriteRules($rules) {
	global $wp_rewrite;
	
	//$oldrules = $wp_rewrite->rules;
	$series_token = '%' . SERIES_QUERYVAR . '%';
	$wp_rewrite->add_rewrite_tag($series_token, '(.+)', SERIES_QUERYVAR . '=');
	
	//without trailing slash
	$series_structure = $wp_rewrite->root . SERIES_QUERYVAR . "/$series_token";
	$rewrite = $wp_rewrite->generate_rewrite_rules($series_structure);
	//return $series_structure;
	
	return ( $rewrite + $rules );
}

function series_init() {
	global $wp_rewrite;
	
	if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
		define('SERIES_REWRITEON', '1');  //pretty permalinks please!
	} else {
		define('SERIES_REWRITEON', '0');  //old school links
	}
	
	
	//generate rewrite rules for series queries 
	
	if (SERIES_REWRITEON && SERIES_REWRITERULES)
		add_filter('rewrite_rules_array', 'series_createRewriteRules');
		
	//setting up the series_toc_page redirect
	$settings = get_option('org_series_options');
	$series_toc_url = $settings['series_toc_url'];
	if ($series_toc_url && (strpos($_SERVER['REQUEST_URI'], $series_toc_url) === 0) && (strlen($_SERVER['REQUEST_URI']) == strlen($series_toc_url))) {
		status_header(200); 
		add_filter('request', 'orgSeries_request');
		add_action('template_redirect', 'orgSeries_toc_template');
	}
}

function orgSeries_request($query_vars) {
	$query_vars['error'] = false;
	return $query_vars;
}

function orgSeries_toc_template() {
	if (file_exists(TEMPLATEPATH . '/seriestoc.php')) {
		$template =  TEMPLATEPATH . '/seriestoc.php';
	} else {
		$template = ABSPATH . 'wp-content/plugins/orgSeries/seriestoc.php';
	}
	
	if ($template) {
		load_template($template);
		exit;
	}
	return;
}
add_action('init', 'series_init');

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


/*----------------------POST RELATED FUNCTIONS (i.e. query etc. see post.php)--------------------*/
//will have to add the following function for deleting the series relationship when a post is deleted.
function delete_series_post_relationship($postid) {
	wp_delete_object_term_relationships($postid, 'series');
}

//call up series post is associated with -- needed for the post-edit panel specificaly.
function wp_get_post_series( $post_id = 0, $args = array() ) {
	$post_id = (int) $post_id;
	
	$defaults = array('fields' => 'ids');
	$args = wp_parse_args( $args, $defaults);
	
	$series = wp_get_object_terms($post_id, 'series', $args);
	
	return $series;
}

//function to set the order that the post is in a series.
function set_series_order($postid = 0, $series_part = 0, $series_id) {
	
	if ( !isset($series_id) ) return false; // if post doesn't belong to a series yet.
	$post_ids_in_series = get_objects_in_term($series_id, 'series');
	$total_posts = count($post_ids_in_series);
	
	if (!isset($total_posts) || ($total_posts < $series_part) || $series_part ==  0 || $total_posts == 1) {
		if ($total_posts >=1) $series_part = $total_posts;
	} 
				
	$series_posts = array();
	$series_posts = get_series_order($post_ids_in_series, $postid);
		
	$ticker = 1;
	$count = count($series_posts);
	if ($count >= 1) {
		foreach ($series_posts as $sposts) {
			$currentpart = $sposts['part']; 
			$spostid = $sposts['id'];
			
			if (( $ticker >= 1) && ( $series_part > 2 ) &&  ( ($series_part - $currentpart)  >= 1) && $drop )  {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
			
			if ( (  $ticker == 1 ) && ( $currentpart == 2 ) && ($series_part != $currentpart) && ($count >= 2 ) && !$rise ) {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
			
			if ( ( $ticker == 1 ) && ( $series_part == $currentpart ) && ( $series_part == 2 ) && !$rise )  {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
				
			if ( ($series_part == $currentpart) && ( $series_part <= $count ) && ( $series_part > 1 ) && ($series_part != 2 ) && $drop ) 
				$newpart = ($currentpart - 1);
				
			if ( ( ($series_part == 1 ) && ($series_part >= $currentpart) ) ||  ( ( $series_part == $currentpart )  && !$drop && ($currentpart - $oldpart) < 2 ) || ( ( $series_part < $currentpart ) && ( $currentpart == $oldpart ) && !$drop ) ) {
				$newpart = ($currentpart + 1);
				$rise = TRUE;
			}
			 
			if ( ($series_part == $currentpart) && ($series_part > ( $count - 2 ) ) && ($series_part != 1) && !$drop && !$rise ) {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
				
			if ( ($series_part == $currentpart) && ($series_part > ( $count - 2 ) ) && ($ticker == $count ) && ($series_part != 1) && !$rise ) {
				$newpart = ($currentpart - 1);
				$drop = TRUE;		
			}
			
			if (!isset($newpart)) 
				$newpart = $currentpart;
				
			if ( isset($oldpart) && ($newpart - $oldpart) > 1 && !$drop && !$rise  && ($newpart != ($count + 1) ) ) {
					$newpart = ($currentpart - 1);
					$drop = TRUE;
					}			
			
			delete_post_meta($spostid, SERIES_PART_KEY); 
			add_post_meta($spostid, SERIES_PART_KEY, $newpart);
			$ticker++;
			$oldpart = $newpart;
			unset($newpart);
			
		}
	}
	delete_post_meta($postid, SERIES_PART_KEY);
	add_post_meta($postid, SERIES_PART_KEY, $series_part);
	return true;
}

function wp_reset_series_order_meta_cache ($post_id = 0, $series_id = 0, $reset = FALSE) {
		
	if ( 0 == $series_id ) return false; //post is not a part of a series so no need to waste cycles.
	
	$post_ids_in_series = get_objects_in_term($series_id, 'series');
	
	$addvalue = 1;
	
	$series_posts = get_series_order($post_ids_in_series, $post_id);
	
	if ($reset) {
		foreach ($post_ids_in_series as $spost) {
			delete_post_meta($spost['object_id'], SERIES_PART_KEY);
		}
		return true;
	}
	
	foreach ($series_posts as $spost) {
		$newpart = $addvalue;
		delete_post_meta($spost['id'], SERIES_PART_KEY);
		add_post_meta($spost['id'], SERIES_PART_KEY, $newpart);
		$addvalue++;
	}
	
	return true;
}

function add_series_wp_title( $title ) {
	$series = single_series_title('', false);
	
	if ( !empty($series) ) {
		if ( !is_feed() )
			$title = 'Series: ' . $series . ' &laquo; ' . $title;
		else
			$title = 'Posts from the series: ' . $series . '(UnfoldingNeurons.com)';
	}
	return $title;
}

add_filter('wp_title', 'add_series_wp_title');

//following is modified from wp_dropdown_categories()
function wp_dropdown_series($args = '') {
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '',
		'orderby' => 'ID', 'order' => 'ASC',
		'show_last_update' => 0, 'show_count' => 0,
		'hide_empty' => 1, 
		'exclude' => '', 'echo' => 1,
		'selected' => 0,
		'name' => 'series', 'class' => 'postform'
	);
	
	$defaults['selected'] = ( is_series() ) ? get_query_var('series') : 0;
	
	$r = wp_parse_args( $args, $defaults );
	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );
	
	$serieslist = get_series($r);
	
	$output = '';
	if ( ! empty($serieslist) ) {
		$output = "<select name='$name' id='$name' class='$class'>\n";
		
		if ( $show_option_all ) {
			$show_option_all = apply_filters('list_series', $show_option_all);
			$output .= "\t<option value='0'>$show_option_all</option>\n";
		}
		
		if ( $show_option_none) {
			$show_option_none = apply_filters('list_series', $show_option_none);
			$output .= "\t<option value='-1'>$show_option_none</option>\n";
		}
		foreach ($serieslist as $listseries) {		
			$output .= walk_series_dropdown_tree($listseries, $r);
		}
		$output .= "</select>\n";
	}
	
	if ( empty( $serieslist ) ) {
		$output = '<select name="no-series" id="no-series" class="postform">';
		$output .= "\n";
		$output .= '<option value="-1">No Series have been started</option>';
		$output .= "\n";
		$output .= "</select>\n";
	}
	
	$output = apply_filters('wp_dropdown_series', $output);
	
	if ( $echo )
		echo $output;
		
	return $output;
}

function walk_series_dropdown_tree($serieslist, $r) {
	$series_name = apply_filters('list_series', $serieslist->name, $serieslist);
	$output = '';
	$output .= "\t<option value=\"" . $serieslist->term_id . "\"";
	if ( $serieslist->term_id == $r['selected'] )
		$output .= ' selected="selected"';
	$output .= '>';
	$output .= $series_name;
	if ( $r['show_count'] )
		$output .= '&nbsp;&nbsp;(' . $serieslist->count .')';
	if ( $r['show_last_update'] ) {
		$format = 'Y-m-d';
		$output .= '&nbsp; &nbsp;' . gmdate($format, $serieslist->last_update_timestamp);
	}
	$output .= "</option>\n";
	
	return $output;
}

function wp_list_series($args = '') {
	$defaults = array(
	'show_option_all' => '', 'orderby' => 'name',
		'order' => 'ASC', 'show_last_update' => 0,
		'style' => 'list', 'show_count' => 0,
		'hide_empty' => 1, 'use_desc_for_title' => 1,
		'feed' => '', 'feed_image' => '', 'exclude' => '',
		'title_li' => __('Series'),
		'echo' => 1
	);
	
	$r = wp_parse_args( $args, $defaults );
	
	if ( isset( $r['show_date'] ) ) {
		$r['include_last_update_time'] = $r['show_date'];
	}
	
	extract( $r );
	
	$serieslist = get_series($r);
	
	$output = '';
	if ( $title_li && 'list' == $style )
		$output = '<li class="series">' . $r['title_li'] . '<ul>';
		
	if ( empty($serieslist) ) {
		if ( 'list' == $style )
			$output .= '<li>' .__("No series") . '</li>';
		else
			$output .= __("No Series");
	
	} else {
		global $wp_query;
		
		if (!empty($show_option_all) )
			if ('list' == $style )
				$output .= '<li><a href="' . get_bloginfo('url') . '".' . $show_option_all . '</a></li>';
			else
				$output .= '<a href="' . get_bloginfo('url') . '".' . $show_option_all . '</a>';
				
		if ( is_series() )
			$r['current_series'] = $wp_query->get_queried_object_id();
			
		foreach ( $serieslist as $listseries )
			$output .= walk_series_tree($listseries	, $r);
	}
	
	if ( $title_li && 'list' == $style )
		$output .= '</ul></li>';
		
	$output = apply_filters('wp_list_series', $output);
	
	if ( $echo )
		echo $output;
	else
		return $output;
}

function walk_series_tree( $series, $args) {
	if ( 'list' != $args['style'] )
		return $series;
	
	extract($args);
	
	$series_name = attribute_escape( $series->name );
	$series_name = apply_filters( 'list_series' , $series_name, $series );
	$link = '<a href="' . get_series_link( $series->term_id ) . '" ';
	$output = '';
	if ( $use_desc_for_title == 0 || empty($series->description) )
		$link .= 'title="' . sprintf(__( 'View all posts filed under %s' ), $series_name) . '"';
	else
		$link .= 'title="' . attribute_escape( apply_filters( 'series_description' , $series->description, $series )) . '"';
	$link .= '>';
	$link .= $series_name . '</a>';
	
	if ( (! empty($feed_image)) || (!empty($feed)) ) {
		$link .= ' ';
		
		if ( empty($feed_image) )
			$link .= '(';
		
		$link .= '<a href="' . get_series_rss_link( 0, $series->term_id, $series->slug ) . '"';
		
		if ( empty($feed) )
			$alt = ' alt="' . sprintf(__( 'Feed for all posts belonging to %s' ), $series_name ) . '"';
		else {
			$title = ' title="' . $feed . '"';
			$alt = ' alt="' . $feed . '"';
			$name = $feed;
			$link .= $title;
		}
		
		$link .= '>';
		
		if ( empty($feed_image) )
			$link .= $name;
		else
			$link .= "<img src='$feed_image'$alt$title" . ' />';
		$link .= '</a>';
		if ( empty($feed_image) )
			$link .= ')';
	}
	
	if ( isset($show_count) && $show_count )
		$link .= ' (' . intval($series->count) . ')';
		
	if ( isset($show_date) && $show_date ) {
		$link .= ' ' . gmdate('Y-m-d', $series->last_update_timestamp);
	}
	
	if ( 'list' == $args['style'] ) {
		$output .= "\t<li";
		$class = 'series-item series-item-'.$series->term_id;
		if ( $current_series && ($series->term_id == $current_series) )
			$class .= ' current-series';
		$output .= ' class="'.$class.'"';
		$output .= ">$link\n";
	} else {
		$output .= "\t$link<br />\n";
	}
	
	$output .= "</li>\n";
	return $output;
}
		
//wp_query stuff (see query.php) -- help for this came from examples gleaned in jeromes-keywords.php
function series_addQueryVar($wpvar_array) {
	$wpvar_array[] = SERIES_QUERYVAR;
	return($wpvar_array);
}

//for series queries
add_filter('query_vars', 'series_addQueryVar');
add_action('parse_query','series_parseQuery');


function series_parseQuery() {
	//if this is a series query, then reset other is_x flags and add template redirect;
	if (is_series()) {
		global $wp_query;
		$wp_query->is_single = false;
		$wp_query->is_page = false;
		$wp_query->is_archive = false;
		$wp_query->is_search = false;
		$wp_query->is_home = false;
		$wp_query->is_series = true;
		$wp_query->is_404 = false;
		
		add_action('template_redirect','series_includeTemplate');
	}	
	add_filter('posts_where', 'series_postsWhere');
	add_filter('posts_join', 'series_postsJoin');
}

function series_postsWhere($where) { 
	global $wpdb;
	$series_var = get_query_var(SERIES_QUERYVAR);
	$cat_var = get_query_var('cat');
	$token = "'" . SERIES_QUERYVAR . "'";
	//convert to series id if permalinks turned on.
	$serchk = is_term( $series_var, SERIES_QUERYVAR );
	if ( !empty($serchk) ) 
		$series_var = $serchk['term_id'];
	$whichseries = '';
	
	if ( !empty($series_var)  && empty($cat_var) ) {
		$whichseries .= " AND $wpdb->term_taxonomy.taxonomy = $token ";
		$whichseries .= " AND $wpdb->term_taxonomy.term_id = $series_var ";
	}
		
	//for category and series intersects
	If ( !empty( $series_var ) && !empty($cat_var) ) {
		$taxonomy = $token;
		$t_ids = array( $cat_var, $series_var );
		$tsql = "SELECT p.ID FROM $wpdb->posts p INNER JOIN $wpdb->term_relationships tr ON (p.ID = tr.object_id) INNER JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) INNER JOIN $wpdb->terms t ON (tt.term_id = t.term_id)";
		$tsql .= " WHERE tt.taxonomy = ($token OR 'category') AND t.term_id IN ('" . implode("', '", $t_ids) . "')";
		$tsql .= " GROUP BY p.ID HAVING count(p.ID) = " . count($t_ids);
		
		$post_ids = $wpdb->get_col($tsql);
		
		if ( count($post_ids) )
			$whichseries .= " AND $wpdb->posts.ID IN (" . implode(', ', $post_ids) . ") ";
		else 
			$whichseries = " AND 0 = 1";
	}
			
	$where .= $whichseries;
	return $where;
}

function series_postsJoin($join) {
	global $wpdb;
	$series_var = get_query_var(SERIES_QUERYVAR);
	$cat_var = get_query_var('cat');
	if ( !empty($series_var) && empty( $cat_var ) )  {
		$join = " INNER JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) INNER JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";
	}
		
	return $join;	
}

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

function wp_set_post_series( $post_ID = 0, $series_id = 0) {
	$post_ID = (int) $post_ID;
	if ( $series_id == 0 ) 
		$post_series = (int) $_POST['post_series'];
	else
		$post_series = (int) $series_id;
	
	if ( isset($_POST) )
		$series_part = (int) $_POST['series_part'];
	else
		$series_part = 0;
		
	$old_series = wp_get_post_series($post_ID);
	$match = in_array($post_series, $old_series);
	
	if ( !$match ) wp_reset_series_order_meta_cache($post_ID, $old_series);
	
	if ( $post_series == '' ||0 == $post_series )
		return wp_delete_post_series_relationship($post_ID);
	
	$success = wp_set_object_terms($post_ID, $post_series, 'series');
	if ( $success ) return set_series_order($post_ID, $series_part, $post_series);
	else return FALSE;
}

function wp_delete_post_series_relationship( $id ) {
	global $wpdb, $wp_rewrite;
	$postid = (int) $id;
	$series = get_the_series( $postid );
	//echo "ID = $id.....postid = $postid";
	if (!empty($series) ) {
		$seriesid = $series[0]->term_id;
		delete_post_meta($postid, SERIES_PART_KEY);
		wp_delete_object_term_relationships($postid, array('series'));
		return wp_reset_series_order_meta_cache($postid, $seriesid);
	}
	return false;
}

//add_action('edit_post','wp_set_post_series');
//add_action('publish_post','wp_set_post_series');
add_action('save_post','wp_set_post_series');
add_action('delete_post','wp_delete_post_series_relationship');

### taxonomy checks for series ####
function series_exists($series_name) {
	$id = is_term($series_name, 'series');
	if ( is_array($id) )
		$id = $id['term_id'];
	return $id;
}

function get_series_to_edit ( $id ) {
	$series = get_orgserial( $id, OBJECT, 'edit' );
	return $series;
}

function wp_create_single_series($series_name) {
	if ($id = series_exists($series_name) )
		return $id;
	
	return wp_insert_series( array('series_name' => $series_name) );
}

function wp_create_series($series, $post_id = '') { // this function could be used in a versions prior to 2.0 import as well.
	$series_ids = '';
	if ($id = series_exists($series) ) 
		$series_ids = $id;
	elseif ($id = wp_create_single_series($series) )
			$series_ids = $id;
	
	else $id = $_POST['post_series'];
	
	if ($post_id)
		wp_set_post_series($post_id, $series_ids);
	
	return $series_ids;
}

// note following function WILL NOT delete the actual image file from the server.  I don't think it's needed at this point.
function wp_delete_series($series_ID) {
	global $wpdb;
	$series_ID = (int) $series_ID;
		
	seriesicons_delete($series_ID);
	wp_reset_series_order_meta_cache('',$series_ID,TRUE);
	
	return wp_delete_term($series_ID, 'series');
}

function wp_insert_series($serarr, $file = FALSE) {
	global $wpdb;
	
	extract($serarr, EXTR_SKIP);
	
	if ( trim( $series_name ) == '' )
		return 0;
	
	$series_ID = (int) $series_ID;
	
	// Are we updating or creating?
	
	if ( !empty ($series_ID) )
		$update = true;
	else
		$update = false;
		
	$name = $series_name;
	$description = $series_description;
	$slug = $series_nicename;
	$action = $action;
	$overrides = array('action' => $action);
	if (!($file) || $file=='') unset($file);
	
	if (isset($file)) {
		$iconfile = wp_handle_upload( $file, $overrides );
	
		//if ($message = $iconfile['error']) return FALSE; //TODO - remove the RETURN FALSE check and instead return an array for wp_insert_series containing $message, and $series_id.  This would require going back over all the files to update any calls to wp_insert_series so that returned variable is used correctly.
		$iconname = $iconfile['url'];
		
		//take the $iconname which contains the full url of the series
		$iconname = explode('/', $iconname);
		$icon = $iconname[count($iconname) - 1];
	} else {
		$icon = '';
	}
	
	$args = compact('name','slug','description');
	
	if ( $update ) {
		$series_icon = seriesicons_write($series_ID, $icon);
		$ser_ID = wp_update_term($series_ID, 'series', $args);
	} else {
		$ser_ID = wp_insert_term($series_name,'series',$args);
		$series_icon = seriesicons_write($ser_ID['term_id'], $icon);
	}
	if ( is_wp_error($ser_ID) )
		return 0;
	
	return $ser_ID['term_id'];
}

function wp_update_series($serarr, $file = FALSE) {
	global $wpdb;
	
	$series_ID = (int) $serarr['series_ID'];
	
	// First, get all of the original fields
	$series = get_orgserial($series_ID, ARRAY_A);
	
	// Escape stuff pulled from DB.
	$series = add_magic_quotes($series);
	
	//Merge old and new fields with fields overwriting old ones.
	$serarr = array_merge($series, $serarr);
	return wp_insert_series($serarr, $file);
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
	$icon = $series_url . "/" . $series_icon;
	
	if ( current_user_can( 'manage_series' ) ) {
		$edit = "<a href='edit.php?page=orgSeries/orgSeries-manage.php&amp;action=edit&amp;series_ID=$series->term_id' class='edit'>".__( 'Edit' )."</a></td>";
		
		if ( isset( $wp_version ) && $wp_version >= 2.5 )
			$edit .=  "<td><a href='" . wp_nonce_url("edit.php?page=orgSeries/orgSeries-manage.php&action=delete&amp;series_ID=$series->term_id&noheader=1", 'delete-series_' . $series->term_id ) . "' class='delete'>".__( 'Delete' )."</a>";
		else
			$edit .= "<td><a href='" . wp_nonce_url("edit.php?page=orgSeries/orgSeries-manage.php&amp;action=delete&amp;series_ID=$series->term_id", 'delete-series_' . $series->term_id ) . "' onclick=\"return deleteSomething('serial', $series->term_id, '" . js_escape(sprintf( __("You are about to delete the series '%s'. \nAll posts that were assigned to this series will be disassociated from the series.\n'OK' to delete, 'Cancel' to stop." ), $series->name  )) . "' );\" class='delete'>".__( 'Delete' )."</a>";
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
?>
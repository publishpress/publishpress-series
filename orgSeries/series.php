<?php
/* This file is for all the Organize Series related Term Queries and "tags".  I just wanted to clean up the main plugin file a bit!
Please note:  I followed various WP core files for structuring code which made it easier than it could have been.  So I must give credit where credit is due!
 */
//these defines need to be moved to the orgSeries.php
 define('SERIES_QUERYVAR', 'series');  // get/post variable name for querying series from WP
 define('SERIES_URL', 'series'); //URL to use when querying series
 define('SERIES_TEMPLATE', 'series-template.php'); //template file to use for displaying series queries.
 define('SERIES_SEARCHURL','search'); //local search URL (from mod_rewrite_rules)
 define('SERIES_PART_KEY', 'series_part'); //the default key for the Custom Field that distinguishes what part a post is in the series it belongs to.
 define('SERIES_REWRITERULES','1'); //flag to determine if plugin can change WP rewrite rules.
 
/*utitlity functions -- perhaps add to their own file for better organization? */

//This function will sort multi-dimensional arrays (courtesy of alishahnovin@hotmail.com via comment on "sort" info page on the php.net site)
function _usort_series_by_part($a, $b) {
	if ($a['part'] > $b['part'] )
		return 1;
	elseif ( $a['part'] < $b['part'] )
		return -1;
	else
		return 0;
	}
	
function msort($array, $id="id") {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        return $temp_array;
    }
	
//This function is used to create an array of posts in a series including the order the posts are in the series.  Then it will sort the array so it is keyed in the order the posts are in.  Will return the array.
function get_series_order ($posts, $postid = 0, $skip = TRUE) {
	if (!isset($posts)) return false; //don't have the posts object so can't do anything.
	$series_posts = array();
	$key = 0;
	
	foreach ($posts as $spost) {
			$spost_id = $spost['object_id'];
		if ($skip && $spost_id == $postid) continue;
			$currentpart = get_post_meta($spost_id, SERIES_PART_KEY, true);
			$series_posts[$key]['id'] = $spost_id;
			$series_posts[$key]['part'] = $currentpart;
			$key++;
		}
	
	usort( $series_posts, '_usort_series_by_part' );
	//msort($series_posts, "part");
	
	return $series_posts;
}
 
 /* functions referenced by other files */
function &get_series($args = '') {
	global $wpdb, $category_links;
	
	$key = md5( serialize($args) );
	if ( $cache = wp_cache_get('get_series','category') )
		if ( isset( $cache[ $key ] ) )
			return apply_filters('get_series', $cache[$key],$args);
			
	$series = get_terms('series', $args);
	
	if ( empty($series) )
		return array();
		
	$cache[ $key ] = $series;
	wp_cache_set( 'get_series', $cache, 'category' );
	
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
	$series_structure = $wp_rewrite->root . '/' . SERIES_QUERYVAR . "/$series_token";
	return $series_structure;
}

function series_createRewriteRules($rewrite) {
	global $wp_rewrite;
	
	$series_token = '%' . SERIES_QUERYVAR . '%';
	$wp_rewrite->add_rewrite_tag($series_token, '(.+)', SERIES_QUERYVAR . '=');
	
	$series_structure = $wp_rewrite->root . SERIES_QUERYVAR . "/$series_token";
	$series_rewrite = $wp_rewrite->generate_rewrite_rules($series_structure);
	
	return ( $rewrite + $series_rewrite );
}

function series_init() {
	global $wp_rewrite;
	
	/*not necessary to change but can be set to 0 if you want to force permalinks off */
	if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
		define('SERIES_REWRITEON', '1');  //pretty permalinks please!
		define('SERIES_LINKBASE', $wp_rewrite->root);  //set to "index.php/" if using that style
	} else {
		define('SERIES_REWRITEON', '0');  //old school links
		define('SERIES_LINKBASE', '');  //don't need this
	}
	
	//generate rewrite rules for series queries 
	
	if (SERIES_REWRITEON && SERIES_REWRITERULES)
		add_filter('search_rewrite_rules', 'series_createRewriteRules');
}
add_action('init', 'series_init');


/* ---------- SERIES TEMPLATE TAGS (maybe add to a series-template.php file?) --------------------------*/
//url constructor/function template tags.
function get_series_link( $series_id ) {
	$series_token = '%' . SERIES_QUERYVAR . '%';
	$serieslink = get_series_permastruct();
	
	$series = &get_term($series_id, 'series');
	if (is_wp_error( $series ) )
		return $series;
	$slug = $series->slug;
	$id = $series->term_id;
	
	if ( empty($serieslink) ) {
		$file = get_option('home') . '/';
		$serieslink = $file . '?series=' . $id;
	} else {
		$serieslink = str_replace($series_token, $slug, $serieslink);
		$serieslink = get_settings('home') . user_trailingslashit($serieslink, 'category');
	}
	return $serieslink;
	//return apply_filters('series_link', $serieslink, $series_id); 
}
	
function get_the_series( $id = false ) { 
	global $post, $tem_cache, $blog_id;
	
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

function get_the_series_by_ID( $series_ID ) {
	$series_ID = (int) $series_ID;
	$series = &get_orgserial($series_ID);
	if ( is_wp_error( $series ) )
		return $series;
	return $series->name;
}

function get_the_series_list( $before = '', $sep = '', $after = '') { //This prepares a display lists of all series associated with a particular post and can choose the surrounding tags. Probably should modify this so the surrounding tags can be set in options? This particular function only returns the values.  the_series() will echo the values by default (and calls this function).
	$series = get_the_series();

	if ( empty( $series ) )
		return false;
	
	$series_list = $before;
	foreach ( $series as $orgSerial ) {
		$link = get_series_link($orgSerial->term_id);
		if  ( is_wp_error( $link ) )
			return $link;
		$series_links[] = '<a href="' . $link . '" rel="series">' . $orgSerial->name . '</a>'; 
	}
	
	$series_links = join( $sep, $series_links);
	$series_links = apply_filters ( 'the_series', $series_links );
	$series_list .= $tag_links;
	
	$series_list .= $after;
	
	return $series_list;
}

function the_series( $before = 'Series: ', $sep = ', ', $after = '') { //This function will echo the results from get_the_series_list with the modification of what shows up surrounding each series
	$return = get_the_series_list($before, $sep, $after);
	if ( is_wp_error( $return ) )
		return false;
	else
		echo $return;
}

function in_series( $series_term ) { //check if the current post is in the given series
	global $post, $blog_id;
	
	$series = get_object_term_cache($post->ID, 'series');
	if ( false === $series )
		$series = wp_get_object_terms($post->ID, 'series');
	if ( array_key_exists($series_term, $series))
		return true;
	else
		return false;
}

function the_series_title($series_id=0, $linked=TRUE, $display=TRUE) {
	if( 0==$series_id )
		return;
	
	$series_id = intval( $series_id );
	
	if ( !empty($series_id) ) {
		$my_series = &get_term($series_id, 'series', OBJECT, 'display');
		if ( is_wp_error( $my_series ) )
			return false;
		$prefix = '';
		$suffix = '';
		$my_series_name = $my_series->name;
		if ( !empty($my_series_name) ) {
			if ( $linked ) {
				$series_link = get_series_link($series_id);
				$prefix = '<a href="' . $series_link . '" title="series-' . $series_id . '">';
				$suffix = '</a>';
			}
			
			$result = $prefix . $my_series_name . $suffix;
			if ( $display ) 
				return $result;
			else
				return $my_series_name;
		}
	}
}

function series_description($series_id = 0) {
	global $series;
	if ( !$series_id )
		$series_id = $series;
		
	return get_term_field('description', $series_id, 'series');
}

/** Replaces tokens (set in orgSeries options) with the relevant values **/
/** NOTE: %postcontent% is NOT replaced with this function...it happens in the content filter function **/
function token_replace($replace, $referral = 'other', $seriesid = 0) {
	global $post;
	$id = $post->ID;
	$settings = get_option('org_series_options');
	if ('post-list' == $referral) {
		$ser_width = $settings['series_icon_width_post_page']; 
		 } else {
		 $ser_width = $settings['series_icon_width_series_page'];
		 }
	$replace = str_replace('%series_icon%', get_series_icon('fit_width=$ser_width, link=0, series=$seriesid'), $replace);
	$replace = str_replace('%series_icon_linked%', get_series_icon('fit_width=$ser_width, series=$seriesid'), $replace);
	$replace = str_replace('%series_title%', the_series_title($seriesid, FALSE), $replace);
	$replace = str_replace('%series_title_linked%', the_series_title($seriesid), $replace);
	$replace = str_replace('%post_title_list%', get_series_posts($seriesid), $replace);
	$replace = str_replace('%series_part%', wp_series_part($id), $replace);
	$replace = str_replace('%total_posts_in_series%', wp_postlist_count(), $replace);
	$replace = str_replace('%series_description%', series_description($series_id), $replace);
	return $replace;
	}

	//TODO - modify all the related series display functions in orgSeries.php using the token_replace function and the new codes.
	
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

//TODO ? have to figure out how to get this added to the wp_get_single_post call (post.php - line 577)
function wp_get_single_post_series($postid = 0, $mode = OBJECT) {
	global $wpdb;
	$postid = (int) $postid;
	
	$post = get_post($postid, $mode);
	
	//set series
	if($mode == OBJECT) {
		$post->series = wp_get_post_series($postid, array('fields' => 'names'));
	
	}
	else {
		$post['series'] = wp_get_post_series($postid, array('fields' => 'names'));
	}
	
	return $post;
}

//function to set the order that the post is in a series.
function set_series_order($postid = 0, $series_part = 0, $series_id) {
//TODO Bug - when setting the post order this method isn't rearranging the other posts in the series as expected (it looks like nothing is happening.  CURRENT STATUS:  I think it has something to do with the fact that I could get a conflict with two posts having the samenumbers?
	
	if ( !isset($series_id) ) return false; // if post doesn't belong to a series yet.
	$post_ids_in_series = get_objects_in_term($series_id, 'series');
	$total_posts = count($post_ids_in_series);
	
	if (!isset($total_posts) || ($total_posts < $series_part) || $series_part ==  0 || $total_posts == 1) {
		if ($total_posts >=1) $series_part = $total_posts;
	} 
				
	$series_posts = array();
	$key = 0;
	
	$series_posts = get_series_order($post_ids_in_series, $postid);
		
	$ticker = 1;
	$count = count($series_posts);
	if ($count >= 1) {
		foreach ($series_posts as $sposts) {
			$currentpart = $sposts['part']; 
			$spostid = $sposts['id'];
			
			//when there is no part 1 and all the parts below the $series_part have to drop 1
			if (( $ticker >= 1) && ( $series_part > 2 ) &&  ( ($series_part - $currentpart)  >= 1) && $drop )  {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
			
			if ( (  $ticker == 1 ) && ( $currentpart == 2 ) && ($series_part != $currentpart) && ($count > 2 ) && !$rise ) {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
			
			if ( ( $ticker == 1 ) && ( $series_part == $currentpart ) && ( $series_part == 2 ) && !$rise )  {
				$newpart = ($currentpart - 1);
				$drop = TRUE;
			}
				
			// for when the series part is something greater than what it was before and the parts underneath drop in value (because of no part 1.  the current_part equal to series part must drop too.
			if ( ($series_part == $currentpart) && ( $series_part < ( $count ) ) && ( $series_part > 1 ) && ($series_part != 2 ) && $drop ) 
				$newpart = ($currentpart - 1);
				
			
			//for when the starting series is part 1 and the new series is made part 1.  We want the starting series to be part 2 now and the rest to follow in order.
			if ( ( ($series_part == 1 ) && ($series_part >= $currentpart) ) ||  ( ( $series_part == $currentpart )  && !$drop ) || ( ( $series_part < $currentpart ) && ( $currentpart == $oldpart ) && !$drop ) ) {
				$newpart = ($currentpart + 1);
				$rise = TRUE;
			}
			 
			//for when the new $series_part is the same as the last exisitng part in a series we want the last existing part to drop 1.
			if ( ($series_part == $currentpart) && ($series_part > ( $count - 2 ) ) && ($series_part != 1) && !$drop && !$rise )
				$newpart = ($currentpart - 1);
				
			if ( ($series_part == $currentpart) && ($series_part > ( $count - 2 ) ) && ($ticker == $count ) && ($series_part != 1) && !$rise )
				$newpart = ($currentpart - 1);
						
			// for when there is no change in the currentpart (no other conditions are met);
						
			if (!isset($newpart)) {
				$newpart = $currentpart;
				if ( isset($oldpart) && ($newpart - $oldpart) > 1 && !$drop && !$rise && ($newpart != ($count + 1) ) ) {
					$newpart = ($currentpart - 1);
					$drop = TRUE;
					}
			}
			
			delete_post_meta($spostid, SERIES_PART_KEY); 
			add_post_meta($spostid, SERIES_PART_KEY, $newpart);
			$ticker++;
			$oldpart = $newpart;
			unset($newpart);
			//$bugcheck = 'BUGCHECK' . $spostid . 'and part' . $testvalue;
		}
	}
	delete_post_meta($postid, SERIES_PART_KEY);
	add_post_meta($postid, SERIES_PART_KEY, $series_part);
	return true;
}

function wp_reset_series_order_meta_cache ($post_id = 0, $series_id = 0) {
	if ( 0 == $series_id ) return false; //post is not a part of a series so no need to waste cycles.
	
	$post_ids_in_series = get_objects_in_term($series_id, 'series');
	
	$addvalue = 1;
	
	$series_posts = get_series_order($post_ids_in_series, $post_id);
	
	foreach ($series_posts as $spost) {
		$newpart = $addvalue;
		delete_post_meta($spost['id'], SERIES_PART_KEY);
		add_post_meta($spost['id'], SERIES_PART_KEY, $newpart);
		$addvalue++;
	}
	
	return true;
}

function add_series_wp_title( $title ) {
	global $wpdb, $wp_locale, $wp_query;
	$series = get_query_var(SERIES_QUERYVAR);
	$title = '';
	
	if ( !empty($series) ) {
		$series = get_term($series,'series', OBJECT, 'display');
		if ( is_wp_error($series) )
			return $series;
		if ( ! empty($series->name) )
			$title = apply_filters('single_series_title', $series->name);
		}
	return $title;
}

add_filter('wp_title', 'add_series_wp_title');

function single_series_title($prefix = '', $display = true) {
	if( !is_series() )
		return;
	$series_id = intval( get_query_var(SERIES_QUERYVAR) );
	
	if ( !empty($series_id) ) {
		$my_series = &get_term($series_id, 'series', OBJECT, 'display');
		if ( is_wp_error( $my_series ) )
			return false;
		$my_series_name = apply_filters('single_tag_title', $my_series->name);
		if ( !empty($my_series_name) ) {
			if ( $display )
				echo $prefix, $my_series_name;
			else
				return $my_series_name;
		}
	}
}

//wp_query stuff (see query.php) -- help for this came from examples gleaned in jeromes-keywords.php
function series_addQueryVar($wpvar_array) {
	$wpvar_array[] = SERIES_QUERYVAR;
	return($wpvar_array);
}
//for series queries
add_filter('query_vars', 'series_addQueryVar');
add_action('parse_query','series_parseQuery');


function series_parseQuery($query) {
	//if this is a series query, then reset other is_x flags and add query filters
	if (is_series()) {
		global $wp_query;
		$wp_query->is_single = false;
		$wp_query->is_page = false;
		$wp_query->is_archive = false;
		$wp_query->is_search = false;
		$wp_query->is_home = false;
		
		add_filter('posts_where', 'series_postsWhere');
		add_filter('posts_join', 'series_postsJoin');
		add_action('template_redirect','series_includeTemplate');
	}	
}

function is_series() { 
	global $wp_version;
	$series = ( isset($wp_version) && ($wp_version >= 2.0) ) ? get_query_var(SERIES_QUERYVAR) : $GLOBALS[SERIES_QUERYVAR];
	$series = get_query_var(SERIES_QUERYVAR);
	if (!is_null($series) && ($series != ''))
		return true;
	else
		return false;
}

function series_postsWhere($where) { 
	global $wpdb;
	$series_var = get_query_var(SERIES_QUERYVAR);
	$whichseries = '';
	
	if ( !empty($series_var) ) {
		$whichseries .= " AND $wpdb->term_taxonomy.taxonomy = 'series' ";
		$whichseries .= " AND $wpdb->term_taxonomy.term_id = $series_var ";
		$reqser = is_term( $series_var, 'series' );
		if ( !empty($reqser) )
			$q['ser_id'] = $reqser['term_id'];
	}
		
	$where .= "AND $wpdb->term_taxonomy.taxonomy = 'series' ";
	$where .= $whichseries;
	return ($where);
}

function series_postsJoin($join) {
	global $wpdb;
	$series_var = get_query_var(SERIES_QUERYVAR);
	if ( !empty($series_var) )  {
		$join = " LEFT JOIN $wpdb->term_relationships ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id) LEFT JOIN $wpdb->term_taxonomy ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id) ";
	}
		
	return ($join);	
}

function series_includeTemplate() {
	if (is_series()) {
		$template = '';
		
		if ( file_exists(TEMPLATEPATH. "/" . SERIES_TEMPLATE) )
			$template = TEMPLATEPATH . "/" . SERIES_TEMPLATE;
		else if ( file_exists(TEMPLATEPATE . "/series.php") )
			$template = TEMPLATEPATH . "/series.php";
		else
			$template = get_category_template();
		
		if ($template) {
			load_template($template);
			exit;
		}
	}
	return;
}

//TODO: NEED TO ADD TEMPLATE FOR SERIES TOC//

function wp_set_post_series( $post_ID = 0) {
	global $wpdb;
	$post_ID = (int) $post_ID;
	$post_series = (int) $_POST['post_series'];
	$series_part = (int) $_POST['series_part'];
	$old_series = wp_get_post_series($post_ID);
	$match = in_array($post_series, $old_series);
	
	if ( !$match ) wp_reset_series_order_meta_cache($post_ID, $old_series);
	
	if ( $post_series == '' ||0 == $post_series )
		return wp_delete_post_series_relationship($post_ID);
	
	$success = wp_set_object_terms($post_ID, $post_series, 'series');
	if ( $success ) return set_series_order($post_ID, $series_part, $post_series);
	else return FALSE;
}

function wp_delete_post_series_relationship( $id = 0 ) {
	//TODO  will have to consider caching...
	global $wpdb, $wp_rewrite;
	$postid = (int) $id;
	$series = get_the_series($postid);
	$seriesid = $series->term_id;
	$success = wp_delete_object_term_relationships($postid, array('series'));
	if ( $success ) return wp_reset_series_order_meta_cache($postid, $seriesid);
	else return FALSE;
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
	$series = get_series( $id, OBJECT, 'series' );
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
	else
		if ($id = wp_create_single_series($series) )
			$series_ids = $id;
	
	if ($post_id)
		wp_set_post_series($post_id, $series_ids);
	
	return $series_ids;
}

function wp_delete_series($series_ID) {
	global $wpdb;
	$default = '';
	$series_ID = (int) $series_ID;
	return wp_delete_term($series_ID, 'series', "default=$default");
}

function wp_insert_series($serarr) {
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
	
	$args = compact('name','slug','description');
	
	if ( $update )
		$series_ID = wp_update_term($series_ID, 'series', $args);
	else
		$series_ID = wp_insert_term($series_name,'series',$args);
	
	if ( is_wp_error($series_ID) )
		return 0;
	
	return $series_ID['term_id'];
}

function wp_update_series($serarr) {
	global $wpdb;
	
	$series_ID = (int) $serarr['series_ID'];
	
	// First, get all of the original fields
	$series = get_series($series_ID, ARRAY_A);
	
	// Escape stuff pulled from DB.
	$series = add_magic_quotes($series);
	
	//Merge old and new fields with fields overwriting old ones.
	$serarr = array_merge($series, $serarr);
	
	return wp_insert_series($serarr);
}
	
?>
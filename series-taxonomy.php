<?php
/* This file is for all the Organize Series related Term Queries and "tags".  I just wanted to clean up the main plugin file a bit!
Please note:  I followed various WP core files for structuring code which made it easier than it could have been.  So I must give credit where credit is due!
 */

/**
 * get_the_series() - calls up all the series info from the taxonomy tables.
*/	
function get_the_series( $id = false ) { 
	global $post, $term_cache, $blog_id;
	
	$id = (int) $id;
	
	if ( !$id )
		$id = (int) $post->ID;
	
	if ( empty($id) )
		return false;
	
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
	$series = get_term_by('name', $series_name, 'series');
	if ($series)
		return $series->term_id;
	return 0;
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
	$series_posts = array(); 
 	$series_posts = get_series_order($post_ids_in_series, $postid, true, false); 
 	$total_posts = count($series_posts) + 1;
	//var_dump($series_posts);	
	if (!isset($total_posts) || ($total_posts < $series_part) || $series_part ==  0 || $total_posts == 1) {
		if ($total_posts >=1) $series_part = $total_posts;
	} 
		
	$ticker = 1;
	$count = $total_posts;
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
	
	$series_posts = get_series_order($post_ids_in_series, $post_id, true, false);
	
	if ($reset) {
		foreach ($post_ids_in_series as $spost) {
		if (array_key_exists('object_id', $post_ids_in_series)) {
			$spost_id = $spost['object_id'];
			} else {
			$spost_id = $spost;
			}
			delete_post_meta($spost_id, SERIES_PART_KEY);
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
	$output .= "\t<option class=\"". $serieslist->slug . "\" value=\"" . $serieslist->term_id . "\"";
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
		'title_li' => __('Series'), 'number' => '',
		'echo' => 1
	);
	
	$r = wp_parse_args( $args, $defaults );
	
	if ( isset( $r['show_date'] ) ) {
		$r['show_last_update'] = $r['show_date'];
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
		
	if ( isset($show_last_update) && $show_last_update ) {
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
function wp_set_post_series_transition( $post ){
	remove_action('save_post', 'wp_set_post_series');
	$post_ID = $post->ID;
	$ser_id = wp_get_post_series($post_ID);
	$series_id = $ser_id[0];
	wp_set_post_series( $post_ID, $post, $series_id );
}
	
function wp_set_post_series( $post_ID = 0, $post, $series_id = 0) {
	//fix for the revisions feature in WP 2.6+
	if ($post->post_type == 'revision') {
		return;
	}
	
	if ( $post->post_status == 'draft' || $post->post_status == 'pending' || $post->post_status = 'future' )
		$update_count_forward = true;
		
	$post_ID = (int) $post_ID;
	$old_series = wp_get_post_series($post_ID);
	
	if ( $series_id == 0 ) { 
		if (isset($_POST['post_series'])) $post_series = (int) $_POST['post_series'];
		if (isset($_GET['post_series'])) $post_series = (int) $_GET['post_series'];
	 } else {
		$post_series = (int) $series_id;
	}
	$s_part = (int) wp_series_part($post_ID);
	
	if ( isset($_POST) || isset($_GET)) {
		if ( isset($_POST['series_part']) ) $series_part = (int) $_POST['series_part'];
		if ( isset($_GET['series_part']) ) $series_part = (int) $_GET['series_part'];
		
		if ( $update_count_forward )
			wp_update_term_count( $post_series, 'series', false);
			
		if ( (in_array($post_series, $old_series)) && $series_part == $s_part && $series_part != 0 ) return; //get out of here if there's no change in series part!!
	 
	 } else {
		if ( $s_part )
			$series_part = $s_part;
		else
			$series_part = 0;
	}	
	if ( $old_series != '' && ( $post_series == '' || 0 == $post_series  ) )
		return wp_delete_post_series_relationship($post_ID);
	
	if ( $old_series != '' && ($post_series == '' || 0 == $post_series) ) $post_series = (int) $old_series[0]; //this takes care of future posts being published.  Need to set the $post_series variable - but ONLY if the post was associated with a series.
	
	$match = in_array($post_series, $old_series);
	
	if ( !$match ) wp_reset_series_order_meta_cache($post_ID, $old_series);
	
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
	
	else $series_ids = $_POST['post_series'];
	
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
	$series_icon = $series_icon_loc;
	$action = $action;
	$overrides = array('action' => $action);
	
	if ( isset($series_icon) || $series_icon != '' ) {
		$build_path = seriesicons_url();
		$series_icon = str_replace($build_path, '', $series_icon);
	}
		
	$args = compact('name','slug','description');
	if ( $update ) {
		if ($delete_image) {
			seriesicons_delete($series_ID);
		} else {
			$series_icon = seriesicons_write($series_ID, $series_icon);
		}
		$ser_ID = wp_update_term($series_ID, 'series', $args);
	} else {
		$ser_ID = wp_insert_term($series_name,'series',$args);
		$series_icon = seriesicons_write($ser_ID['term_id'], $series_icon);
	}
	if ( is_wp_error($ser_ID) )
		return 0;
	
	return $ser_ID['term_id'];
}

function wp_update_series($serarr) {
	global $wpdb;
	
	$series_ID = (int) $serarr['series_ID'];
	
	// First, get all of the original fields
	$series = get_orgserial($series_ID, ARRAY_A);
	
	// Escape stuff pulled from DB.
	$series = add_magic_quotes($series);
	
	//Merge old and new fields with fields overwriting old ones.
	$serarr = array_merge($series, $serarr);
	return wp_insert_series($serarr);
}

function inline_edit_series($column_name, $type) {
	if ( $type == 'post' ) {
		?>
	<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
		<div class="inline_edit_group">
		<label>
			Series: <?php wp_dropdown_series('name=post_series&hide_empty=0&show_option_all="No Series"'); ?>
			Part: <input type="text" name="series_part" value="" />
		</label>
		</div>
	</div></fieldset>
		<?php
	}	
}

function inline_edit_series_js() {
	wp_enqueue_script('inline-edit-series');
}
/**
 * add_action() and add_filter() calls go here.
*/

global $pagenow;
//add_action for quick edit column 
add_action('quick_edit_custom_box', 'inline_edit_series',1,2);
add_action('admin_print_scripts-edit.php', 'inline_edit_series_js');

//hook into save post for adding/updating series information to posts
add_action('save_post','wp_set_post_series',10,3);

add_action('future_to_publish','wp_set_post_series_transition',1,1);
add_action('delete_post','wp_delete_post_series_relationship',1);
?>
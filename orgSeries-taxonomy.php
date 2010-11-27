<?php
/* This file is for all the Organize Series related Term Queries and "template tags".  

In most cases, the series functions listed here are just "wrappers" to save having to call the built-in functions for WordPress Custom Taxonomies.
 */

/**
 * get_the_series() - calls up all the series info from the taxonomy tables (for a particular post).
*/	
function get_the_series( $id = false ) { 
	global $post, $term_cache;
	
	$id = (int) $id;
	
	if ( !$id && ( !empty($post) || $post != '' || $post != null ) )
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
	$series = series_exists($series_name, 'series');
	if ( $series )
		return $series;
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


/*----------------------
 * POST RELATED FUNCTIONS (i.e. query etc. see post.php)
 --------------------*/

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

/**
 * get_series_in_order() - calls up all the series info from the taxonomy tables in an order specified by the caller.
 * @since 2.1.7
 *
 * @uses $wpdb->get_results() - to query the WP database with the custom query for getting the series in order from the database.
 *
 * @param string $orderby - Specify the field to order Series by (post_date, post_modified, series_name, series_slug, id, or term_id)
 * @param string $order - Specify the order direction (ASC or DESC)
 * @param string $postTypes - Specify the post types to include with each surrounded by quotation marks, 
 *								if single:  "'post'"  
 *								if multiple use comma to separate: "'post','page'"
 * @param bool $hide_empty - If True, only Series with published posts will be included (no empty series will be included)
 * 
 * @return array $series - an array of all series rows as pulled from database (each series listed once)
*/	

function get_series_ordered( $args = '' ) {
	global $wpdb;
	$defaults = array('orderby' => 'term_id', 'order' => 'DESC', 'postTypes' => '"post"', 'hide_empty' => TRUE);
	$args = wp_parse_args( $args, $defaults);
	extract($args, EXTR_SKIP);
	
	$orderby = strtolower($orderby);
	if ( 'post_date' == $orderby ) {
		if ( 'ASC' == $order ) 
			$_orderby = 'min(tp.post_date)';		
		else
			$_orderby = 'max(tp.post_date)';		
	}
	else if ( 'post_modified' == $orderby ) {
		if ( 'ASC' == $order ) 
			$_orderby = 'min(tp.post_modified)';		
		else
			$_orderby = 'max(tp.post_modified)';		
	}
	else if ( 'name' == $orderby ) 
		$_orderby = 't.name';
	else if ( 'slug' == $orderby )
		$_orderby = 't.slug';
	elseif ( empty($orderby) || 'id' == $orderby || 'term_id' == $orderby )
		$_orderby = 't.term_id';
	elseif ( 'count' == $orderby )
		$_orderby = 'tt.count';
		
	$having = '';
	
	if ( $hide_empty ) {
		$having = 'HAVING count(tp.id) > 0 ';
	}
		
	$query = "SELECT t.term_id, t.name, t.slug FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id LEFT OUTER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT OUTER JOIN $wpdb->posts AS tp ON tp.ID = tr.object_id and tp.post_status = 'publish' and tp.post_type in ($postTypes) WHERE tt.taxonomy = 'series' GROUP BY t.term_id, t.name, t.slug $having ORDER BY $_orderby $order";
	$series = $wpdb->get_results($query); 
	return $series;
}

/**
* Display or retrieve the HTML dropdown list of series.
*
* This is directly taken from wp_dropdown_categories in WordPress.  I am unable to just create a wrapper because wp_dropdown_categories, although custom taxonomy aware, it will only use the term_id as the value for each option (as of WP3.0) and they query_var WordPress expects for non-heirarchal taxonomies is the slug not the term_id.  Hence the requirement to make sure the values are the slug for the series.
*
* All arguments descriptions can be obtained from wp_dropdown_categories
*
*/
function wp_dropdown_series( $args ) {
	$defaults = array(
		'show_option_all' => '', 'show_option_none' => '',
		'orderby' => 'id', 'order' => 'ASC',
		'show_last_update' => 0, 'show_count' => 0,
		'hide_empty' => 1, 'child_of' => 0,
		'exclude' => '', 'echo' => 1,
		'selected' => 0, 'hierarchical' => 0,
		'name' => 'series', 'id' => '',
		'class' => 'postform', 'depth' => 0,
		'tab_index' => 0, 'taxonomy' => 'series',
		'hide_if_empty' => false, 'context' => 'normal'
	);
	
	$series_id = get_query_var(SERIES_QUERYVAR);
		
	if ( is_numeric($series_id) && $args['context'] == 'normal' )
		$series_id = get_term_field('slug', $series_id, 'series');
				
	$defaults['selected'] = ( ! empty($series_id) || $series_id != NULL ) ? $series_id : 0;
	
	$r = wp_parse_args( $args, $defaults );
	
	if ( !isset( $r['pad_counts'] ) && $r['show_count'] && $r['hierarchical'] ) {
		$r['pad_counts'] = true;
	}

	$r['include_last_update_time'] = $r['show_last_update'];
	extract( $r );

	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 )
		$tab_index_attribute = " tabindex=\"$tab_index\"";

	$series = get_terms( $taxonomy, $r );
	$name = esc_attr( $name );
	$class = esc_attr( $class );
	$id = $id ? esc_attr( $id ) : $name;

	if ( ! $r['hide_if_empty'] || ! empty($series) )
		$output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
	else
		$output = '';

	if ( empty($series) && ! $r['hide_if_empty'] && !empty($show_option_none) ) {
		$show_option_none = apply_filters( 'list_series', $show_option_none );
		$output .= "\t<option value='-1' selected='selected'>$show_option_none</option>\n";
	}

	if ( ! empty( $series ) ) {

		if ( $show_option_all ) {
			$show_option_all = apply_filters( 'list_series', $show_option_all );
			$selected = ( '0' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value='0'$selected>$show_option_all</option>\n";
		}

		if ( $show_option_none ) {
			$show_option_none = apply_filters( 'list_series', $show_option_none );
			if ( $r['selected'] == 0 ) $r['selected'] = '-1';
			$selected = ( '-1' === strval($r['selected']) ) ? " selected='selected'" : '';
			$output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
		}

		if ( $hierarchical )
			$depth = $r['depth'];  // Walk the full depth.
		else
			$depth = -1; // Flat.

		$output .= walk_series_dropdown_tree( $series, $depth, $r );
	}
	if ( ! $r['hide_if_empty'] || ! empty($series) )
		$output .= "</select>\n";


	$output = apply_filters( 'wp_dropdown_series', $output );

	if ( $echo )
		echo $output;

	return $output;
}

function wp_list_series($args = '') {
	global $orgseries;
	$defaults = array(
		'title_li' => __('Series', $orgseries->org_domain), 
		'taxonomy' => 'series',
		'echo' => 1
	);
	$args = wp_parse_args( $args, $defaults );
	$echo_ser = $args['echo'];
	$args['echo'] = 0; // to make sure wp_list_categories is always returned for the wrapper.
	$output = wp_list_categories( $args );
	
	if ( $echo_ser )
		echo $output;
		
	return $output;
}

function wp_set_post_series_transition( $post ){
	remove_action('save_post', 'wp_set_post_series');
	//remove_action('publish_post', 'wp_set_post_series');
	$post_ID = $post->ID;
	$ser_id = wp_get_post_series($post_ID);
	$series_id = $ser_id[0];
	wp_set_post_series( $post_ID, $post, $series_id );
}

function wp_set_post_series_draft_transition( $post ) {
	$post_ID = $post->ID;
	$ser_id = wp_get_post_series($post_ID);
	$series_id = $ser_id[0];
	wp_update_term_count( $series_id, 'series', true );
}
	
function wp_set_post_series( $post_ID = 0, $post, $series_id = 0) {
	$post_series = null;
	//fix for the revisions feature in WP 2.6+  && bulk-edit stuff.
	if ($post->post_type == 'revision' || ( isset($_GET['bulk_edit_series']) && $_GET['bulk_edit_series'] == 'bulk' ) ) {
		return;
	}

	//echo $post->post_status;
	/*if ( $post->post_status == 'draft' || $post->post_status == 'pending' || $post->post_status == 'future' )
		$update_count_forward = true;//*/
	$post_ID = (int) $post_ID;
	$old_series = wp_get_post_series($post_ID);
	
	if ( $series_id === 0 ) { 
		if (isset($_POST['post_series'])) $post_series = (int) $_POST['post_series'];
		if (isset($_GET['post_series'])) $post_series = (int) $_GET['post_series'];
	 } else {
		$post_series = (int) $series_id;
	}
	
	$s_part = (int) wp_series_part($post_ID);
	
	if ( isset($_POST) || isset($_GET)) {
		if ( isset($_POST['series_part']) ) $series_part = (int) $_POST['series_part'];
		if ( isset($_GET['series_part']) ) $series_part = (int) $_GET['series_part'];
		
		/*if ( $update_count_forward )
			wp_update_term_count( $post_series, 'series', false);//*/
			
		if ( (in_array($post_series, $old_series)) && $series_part == $s_part && $series_part != 0 && $post->post_status == 'publish') return; //get out of here if there's no change in series part!!
	 
	 } else {
		if ( $s_part )
			$series_part = $s_part;
		else
			$series_part = 0;
	}
	
	if ( ( $old_series != '' || !empty($old_series) ) && ( $post_series == '' || 0 == $post_series  ) )
		return wp_delete_post_series_relationship($post_ID);
	
	
	if ( $old_series != '' && ($post_series == '' || 0 == $post_series) ) $post_series = (int) $old_series[0]; //this takes care of future posts being published.  Need to set the $post_series variable - but ONLY if the post was associated with a series.
	
	$match = in_array($post_series, $old_series);
	
	if ( !$match && $old_series[0] != 0 ) {
		$old_series = (int) $old_series[0];
		wp_reset_series_order_meta_cache($post_ID, $old_series);
	}
	
	$success = wp_set_object_terms($post_ID, $post_series, 'series');
	
	if ( $success ) {
	return set_series_order($post_ID, $series_part, $post_series);
	} else {
	return FALSE;
	}
}

function wp_delete_post_series_relationship( $id ) {
	global $wpdb, $wp_rewrite;
	$postid = (int) $id;
	$series = get_the_series( $postid );
	
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
	$id = term_exists($series_name, 'series');
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
	
	return wp_insert_term( $series_name, 'series' );
}

function wp_create_series($series, $post_id = '') { 
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
function wp_delete_series($series_ID, $taxonomy_id) {
	global $wpdb;
			
	seriesicons_delete($series_ID);
	wp_reset_series_order_meta_cache('',$series_ID,TRUE);
}

function wp_insert_series($series_id, $taxonomy_id) {
	global $_POST;
	
	extract($_POST, EXTR_SKIP);
	$series_icon = $series_icon_loc;
	
	if ( isset($series_icon) || $series_icon != '' ) {
		$build_path = seriesicons_url();
		$series_icon = str_replace($build_path, '', $series_icon);
	}
	
	$series_icon = seriesicons_write($series_id, $series_icon);
}

function wp_update_series($series_id, $taxonomy_id) {
	global $_POST;
	extract($_POST, EXTR_SKIP);
	if ( empty($series_icon_loc) ) $series_icon_loc = '';
	if ( empty($delete_image) ) $delete_image = false;
	
	$series_icon = $series_icon_loc;
	
	if ( !empty($series_icon) || $series_icon != '' ) {
		$build_path = seriesicons_url();
		$series_icon = str_replace($build_path, '', $series_icon);
		
	}
	
	if ($delete_image) {
		seriesicons_delete($series_id);
	} else {
		$series_icon = seriesicons_write($series_id, $series_icon);
	}
}

function inline_edit_series($column_name, $type) {
	global $orgseries;
	if ( $type == 'post' && $column_name == 'series' ) {
		?>
	<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
		<div class="inline_edit_series_">
			<span><?php _e('Series:', $orgseries->org_domain); ?></span>
			<?php wp_dropdown_series('name=post_series&class=post_series_select&hide_empty=0&show_option_none=No Series&context=quick-edit'); ?>
			<span><?php _e('Part:', $orgseries->org_domain); ?></span>
			<input size="3" type="text" name="series_part" class="series_part"  />
			<input type="hidden" name="series_post_id" class="series_post_id"  />
		
		
	</div></div></fieldset>
		<?php
	}	
}

function bulk_edit_series($column_name, $type) {
	if ( $type == 'post' ) {
		?>
	<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
		<div class="inline-edit-group">
		<label class="inline-edit-series">
			<input type="hidden" name="bulk_edit_series" value="bulk" />
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

//add_action for quick edit column 
add_action('quick_edit_custom_box', 'inline_edit_series',9,2);
add_action('bulk_edit_custom_box', 'bulk_edit_series',9,2);
add_action('admin_print_scripts-edit.php', 'inline_edit_series_js'); 

//hook into save post for adding/updating series information to posts
add_action('save_post','wp_set_post_series',10,3);
add_action('future_to_publish','wp_set_post_series_transition',10,1);
add_action('draft_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('pending_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('delete_post','wp_delete_post_series_relationship',1);

//hooking into insert_term, update_term and delete_term 
add_action('created_series', 'wp_insert_series',1, 2);
add_action('edited_series', 'wp_update_series',1, 2);
add_action('delete_series', 'wp_delete_series', 1, 2);
?>
<?php
/* This file is for all the Publishpress Series related Term Queries and "template tags".

In most cases, the series functions listed here are just "wrappers" to save having to call the built-in functions for WordPress Custom Taxonomies.
 */

/**
 * get_the_series() - calls up all the series info from the taxonomy tables (for a particular post).
*/
function get_the_series( $id = false, $cache = true ) {
	global $post, $term_cache;

	$id = (int) $id;

	if ( !$id && ( !empty($post) || $post != '' || $post != null ) )
		$id = (int) $post->ID;

	if ( empty($id) )
		return false;

	$series = $cache ? get_object_term_cache($id, ppseries_get_series_slug()) : false;

	if (false === $series )
		$series = wp_get_object_terms($id, ppseries_get_series_slug());

	$series = apply_filters('get_the_series', $series); //adds a new filter for users to hook into

	if ( !empty($series) ) {
	    if ( function_exists('wp_list_sort') ) {
	        wp_list_sort($series);
        } else {
		    usort( $series, '_usort_terms_by_name' );
	    }
	}

	return $series;
}

// Get the ID of a series from its name
function get_series_ID($series_name='default') {
	$series = series_exists($series_name, ppseries_get_series_slug());
	if ( $series )
		return $series;
	return 0;
}

/* functions referenced by other files */
function &get_series($args = '') {
	global $wpdb;
	$series = array();

	$key = md5( serialize($args) );
	if ( $cache = wp_cache_get('get_series', ppseries_get_series_slug()) )
		if ( isset( $cache[ $key ] ) )
			$series = apply_filters('get_series', $cache[$key], $args);
			if (!empty($series)) {
				return $series;
			}

	$series = get_terms(ppseries_get_series_slug(), $args);

	if (is_wp_error($series) || empty($series)) {
		$series = [];
		return $series;
	}

	$cache[ $key ] = $series;
	wp_cache_set( 'get_series', $cache, ppseries_get_series_slug() );

	$series = apply_filters('get_series', $series, $args);
	return $series;
}

function &get_orgserial($orgserial, $output = OBJECT, $filter = 'raw') {
		$serie = get_term($orgserial, ppseries_get_series_slug(), $output, $filter);
		return $serie;
}


/*----------------------
 * POST RELATED FUNCTIONS (i.e. query etc. see post.php)
 --------------------*/

 //will have to add the following function for deleting the series relationship when a post is deleted.
function delete_series_post_relationship($postid) {
	$id = (int) $postid;
	$series = get_the_series($id);

	if ( !empty($series) ) {  //let's not waste any cycles
		foreach ( $series as $ser ) {
			wp_reset_series_order_meta_cache($id, $ser->term_id);
		}
		return wp_delete_object_term_relationships($id, array(ppseries_get_series_slug()));
	}
	return;
}

//this will reorder the series parts when a post of a serie is moved to trash.
function reset_series_order_on_trash($postid) {
	$id = (int) $postid;
	$series = get_the_series($id);

	if ( !empty($series) ) {  //let's not waste any cycles
		foreach ( $series as $ser ) {
			wp_reset_series_order_meta_cache($id, $ser->term_id);
		}
	}
}

//call up series post is associated with -- needed for the post-edit panel specifically.
function wp_get_post_series( $post_id = 0, $args = array() ) {
	$post_id = (int) $post_id;
	$defaults = array('fields' => 'ids');
	$args = wp_parse_args( $args, $defaults);
	$series = wp_get_object_terms($post_id, ppseries_get_series_slug(), $args);
	return $series;
}

//function to set the order that the post is in a series.
function set_series_order($series_id, $postid = 0, $series_part = 0, $is_published = false) {
	if ( !isset($series_id) ) return false; // if post doesn't belong to a series yet.
	$post_ids_in_series = get_objects_in_term($series_id, ppseries_get_series_slug());
	$series_posts = array();
 	$series_posts = get_series_order($post_ids_in_series, $postid, $series_id, true, false);
	$parts_array = array();

	$total_posts = is_array($series_posts) ? count($series_posts) + 1 : 1;

	// Find out how many posts in this series are unpublished
	$unpub_count = 0;
    
    if ((int)$series_part === 0) {
        foreach ($series_posts as $sposts) {
            $spostid = $sposts['id'];
            $spost_status = get_post($spostid)->post_status;
            if ($spost_status == 'draft' || $spost_status == 'future' || $spost_status == 'pending') {
                $unpub_count += 1;
            }
        }
    }
    if ((int)$series_part === 0) {
        // If the given Series Part is either higher than the current # of published posts, <=0, or # of published posts is only one,
        // then set variable $series_part to the maximum value
        //$published_posts = $total_posts - $unpub_count;
        if ((($total_posts < $series_part) || $series_part <=  0 || $total_posts == 1)) {
            /*if (($total_posts >= 1) && $is_published) {
                $series_part = $total_posts;
            }
            if (!$is_published) {
                $series_part = $total_posts < $series_part ? $total_posts : $series_part;
            }*/
            $series_part = $total_posts;
        }
    }
/*
	array_push($parts_array, $series_part);

	$ticker = 1;
	$count = $total_posts;
	$drop = false;
	$oldpart = 0;
	$rise = null;
	if ( $count >= 1 ) {
		foreach ( $series_posts as $sposts ) {
			$currentpart = absint($sposts['part']);
			$spostid = $sposts['id'];
			$spost_status = get_post($spostid)->post_status;
			$is_was_rise = FALSE;

			$spost_pchange=TRUE;
			$current_published = $spost_status == "publish";

			$rise_part = $currentpart + 1;
			$drop_part = $currentpart - 1;

			if ( ($currentpart >= $count || $currentpart != $ticker) && ($currentpart > 1) && (($currentpart - $oldpart) > 1) && $current_published && !in_array($drop_part, $parts_array) ) {
				$newpart = $drop_part;
				$drop = TRUE;
			}

			if ( $spost_pchange ) {
				if ( !in_array($drop_part, $parts_array) ) {
					if ( ( $ticker >= 1) && ( $series_part > 2 ) && ( ($series_part - $currentpart) >= 1) && $drop ) {
						$newpart = $drop_part;
						$drop = TRUE;
					}

					if ( ( $ticker == 1 ) && ( $currentpart == 2 ) && ($series_part != $currentpart) && ($count >= 2 ) && !$rise ) {
						$newpart = $drop_part;
						$drop = TRUE;
					}

					if ( ( $ticker == 1 ) && ( $series_part == $currentpart ) && ( $series_part == 2 ) && !$rise ) {
						$newpart = $drop_part;
						$drop = TRUE;
					}

					if ( ($series_part == $currentpart) && ( $series_part <= $count ) && ( $series_part > 1 ) && ($series_part != 2 ) && $drop ) {
						$newpart = ($currentpart - 1);
					}
				}

				if ( !in_array($rise_part, $parts_array) && (($currentpart != $count ) && ($currentpart !== '')) ) {
					if ( (($series_part == 1 ) && ($series_part >= $currentpart)) || (( $series_part == $currentpart ) && !$drop && ($currentpart - $oldpart) < 2) || (( $series_part < $currentpart ) && ( $currentpart == $oldpart ) && !$drop && ($currentpart != $count)) ) {
						$newpart = $rise_part;
						$rise = TRUE;
						$is_was_rise = TRUE;
					}
				}

				if ( !$is_was_rise && $is_published ) {
					$rise = TRUE;
				}

				if ( !in_array($drop_part, $parts_array) ) {
					if ( ($series_part == $currentpart) && ( $series_part > ( $count - 2 ) ) ) {
						if ( ($series_part != 1) && !$drop && !$rise ) {
							$newpart = $drop_part;
							$drop = TRUE;
						}
						if ( ($ticker == $count ) && ($series_part != 1) && !$rise ) {
							$newpart = $drop_part;
							$drop = TRUE;
						}
					}

					if ( isset($oldpart) && isset($newpart) && ($newpart - $oldpart) > 1 && !$drop && !$rise && ($newpart != ($count + 1)) ) {
						$newpart = $drop_part;
						$drop = TRUE;
					}
				}
			}

			if ( !isset($newpart) ) { // we don't need to change this post's part
				$newpart = $currentpart;
			}

			$series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
			delete_post_meta($spostid, $series_part_key);
			add_post_meta($spostid, $series_part_key, $newpart);
			if ( $current_published ) {
				$ticker++;
				$oldpart = $newpart;
				array_push($parts_array, $newpart);
			}
			unset($newpart);
		}
	}*/
	$series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
	delete_post_meta($postid, $series_part_key);
	add_post_meta($postid, $series_part_key, $series_part);
	return true;
}

function wp_reset_series_order_meta_cache ($post_id = 0, $series_id = 0, $reset = FALSE) {

	if ( 0 == $series_id ) return false; //post is not a part of a series so no need to waste cycles.

	$post_ids_in_series = get_objects_in_term($series_id, ppseries_get_series_slug());

	$addvalue = 1;

	$series_posts = get_series_order($post_ids_in_series, $post_id, $series_id, true, false);
	$series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);

	if ($reset) {
		foreach ($post_ids_in_series as $spost) {
		if (array_key_exists('object_id', $post_ids_in_series)) {
			$spost_id = $spost['object_id'];
			} else {
			$spost_id = $spost;
			}

			delete_post_meta($spost_id, $series_part_key);
		}
		return true;
	}

	foreach ($series_posts as $spost) {
		$spost_status = get_post($spost['id'])->post_status;
		$newpart = $addvalue;
		delete_post_meta($spost['id'], $series_part_key);
		add_post_meta($spost['id'], $series_part_key, $newpart);
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
	$post_types = apply_filters('orgseries_posttype_support', array('post'));
	$defaults = array('orderby' => 'term_id', 'order' => 'DESC', 'postTypes' => $post_types, 'hide_empty' => TRUE);
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
	elseif ( 'rand' == $orderby )
		$_orderby = 'RAND()';

	$having = '';

	if ( $hide_empty ) {
		$having = 'HAVING count(tp.id) > 0 ';
	}

	$postTypes = "'" . implode("','",$postTypes) . "'";

	$query = "SELECT t.term_id, t.name, t.slug FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id LEFT OUTER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT OUTER JOIN $wpdb->posts AS tp ON tp.ID = tr.object_id and tp.post_status IN ( 'publish', 'private' ) and tp.post_type in ($postTypes) WHERE tt.taxonomy = '".ppseries_get_series_slug()."' GROUP BY t.term_id, t.name, t.slug $having ORDER BY $_orderby $order";
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
		'selected' => 0, 'hierarchical' => 0, 'name' => SERIES_QUERYVAR, 'id' => '',
		'class' => 'postform', 'depth' => 0,
		'tab_index' => 0, 'taxonomy' => ppseries_get_series_slug(),
		'hide_if_empty' => false, 'context' => 'normal'
	);

	$series_id = get_query_var(SERIES_QUERYVAR);

	if ( is_numeric($series_id) && isset( $args['context'] ) && $args['context'] == 'normal' )
		$series_id = get_term_field('slug', $series_id, ppseries_get_series_slug());

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

	//remove $name from the get_terms() query because it means something different in 4.2, so we'll just exclude it
	//from $r
	if ( isset( $r['name'] ) ) {
		unset( $r['name'] );
	}
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
		$output .= "\t<option value='0' selected='selected'>$show_option_none</option>\n";
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

    if ($echo) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }

	return $output;
}

function wp_list_series($args = '') {
	global $orgseries;
	$defaults = array(
		'title_li' => __('Series', 'organize-series'),
		'taxonomy' => ppseries_get_series_slug(),
		'echo' => 1
	);
	$args = wp_parse_args( $args, $defaults );
	$echo_ser = $args['echo'];
	$args['echo'] = 0; // to make sure wp_list_categories is always returned for the wrapper.
	$output = wp_list_categories( $args );

    if ($echo_ser) {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }

	return $output;
}

function wp_set_post_series_transition( $post ) {
	remove_action('save_post', 'wp_set_post_series', 10);
	$post_ID = $post->ID;
	$ser_id = wp_get_post_series($post_ID);
	wp_set_post_series( $post, true, $post_ID, $ser_id, true );
	// ensure the post is added as the last part in the series
	$current_part = wp_series_part( $post_ID, $ser_id );
	if( empty($current_part) ) set_series_order( $ser_id, $post_ID, 0, true );
}

function wp_set_post_series_draft_transition( $post ) {
	remove_action('save_post', 'wp_set_post_series');
	$post_ID = $post->ID;
	$ser_id = wp_get_post_series($post_ID);
	wp_set_post_series($post, true, $post_ID, $ser_id, true);
}

function series_wp_save_post($post_ID, $post, $update){
	wp_set_post_series($post, $update, $post_ID);
}

function wp_set_post_series( $post, $update, $post_ID = 0, $series_id = array(), $dont_skip = false, $is_published = false) {
	$post_series = null;
	$post_shorttitle = array();
    global $orgseries;

    $settings = $orgseries->settings;

    $automatic_series_part = isset($settings['automatic_series_part']) ? (int)$settings['automatic_series_part'] : 0;

	if ( !is_bool($update) ){
		return; //safety check for users on earlier version of WP (so existing series don't get messed up)
	}

	if(!is_object($post)){
		return;
	}


	//fix for the revisions feature in WP 2.6+  && bulk-edit stuff.
	if ($post->post_type == 'revision' || ( isset($_GET['bulk_edit_series']) && $_GET['bulk_edit_series'] == 'bulk' && $_GET['post_series'] == -1 ) || !isset($_REQUEST['is_series_save'] ) ) {
		return;
	}


	$post_ID = (int) $post_ID;
	$old_series = wp_get_post_series($post_ID);
	$old_series = is_array($old_series) ? $old_series : array();

	if ( empty($series_id) ) {
		$post_series = isset( $_REQUEST['post_series'] ) && is_array($_REQUEST['post_series'] ) ? array_map('sanitize_text_field', $_REQUEST['post_series']) : array(sanitize_text_field($_REQUEST['post_series']));
	 } else {
		$post_series = is_array($series_id) ? $series_id : array($series_id);
	}

	$post_series = os_strarr_to_intarr($post_series);

	if ( empty($post_series) || (count($post_series) >= count($old_series)) ) {
		$match = false;
	} else {
		$match = array_diff($old_series, $post_series);
	}

	if (empty($post_series) || ( count($post_series) == 1 && $post_series[0] == 0 ) ) $post_series = array();

  
	if ( isset($_POST) || isset($_GET)) {

        if (isset($_POST['series_part'])) {
            $series_part = is_array($_POST['series_part']) ? array_map('sanitize_text_field', $_POST['series_part']) : array(sanitize_text_field($_POST['series_part']));
        }
        if (isset($_GET['series_part'])) {
            $series_part = is_array($_GET['series_part']) ? array_map('sanitize_text_field', $_GET['series_part']) : array(sanitize_text_field($_GET['series_part']));
        }
      
		//The "short" title of the post that will be displayed  in the OrgSeries widget.
        if (isset($_POST['serie_post_shorttitle'])) {
            $post_shorttitle = is_array($_POST['serie_post_shorttitle']) ? array_map('sanitize_text_field', $_POST['serie_post_shorttitle']) : sanitize_text_field($_POST['serie_post_shorttitle']);
        }
        if (isset($_GET['serie_post_shorttitle'])) {
            $post_shorttitle = is_array($_GET['serie_post_shorttitle']) ? array_map('sanitize_text_field', $_GET['serie_post_shorttitle']) : sanitize_text_field($_GET['serie_post_shorttitle']);
        }
        
		$st_ser_id = is_array($post_series) && isset($post_series[0]) ? (int) $post_series[0] : 0;
		$post_shorttitle = is_array($post_shorttitle) && isset($post_shorttitle[$st_ser_id]) ? trim($post_shorttitle[$st_ser_id]) : '';


		update_post_meta($post->ID, SPOST_SHORTTITLE_KEY, $post_shorttitle);

		//if we don't have any changes in the series or series part info (or series post status) then let's get out and save time.
		$p_status = $post->post_status;
		if ( $p_status != 'draft' && $p_status != 'future' && $p_status != 'pending' ) {
			$is_published = TRUE;
		}
		$count = count($post_series);
		$c_chk = 0;
		foreach ( $post_series as $ser ) {
			$post_series_part = wp_series_part( $post_ID, $ser );
			if ( in_array($ser, $old_series) && isset( $series_part[$ser] ) && ! empty( $post_series_part ) && $series_part[$ser] == $post_series_part && ! $dont_skip ) {
				$c_chk++;
				continue;
			} else {
				$p_ser_edit[] = $ser; //these are the series we need to set the parts for (leave the rest alone when we get to this section).
			}
		}

		if ( $c_chk == $count && !empty($post_series) && count($post_series) == count($old_series) && !$dont_skip ) return;  //there are no changes so let's just skip the rest

	}


	if ( empty($post_series) ) {
		foreach ( $old_series as $o_ser ) {
			$part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $o_ser);
			delete_post_meta( $post_ID, $part_key);
		}
	}

	foreach ( $old_series as $os_id ) {
		if ( !in_array($os_id, $post_series) ) {
			wp_delete_post_series_relationship($post_ID, $os_id);
			}
	}


	if ( !empty($match) && $match ) {
		foreach ($match as $part_reset_id) {
			wp_reset_series_order_meta_cache($post_ID, $part_reset_id);
		}
	}

	$success = wp_set_object_terms($post_ID, $post_series, ppseries_get_series_slug());

    if (empty($p_ser_edit)) {
        return; //let's get out we've done everything we need to do.
    }

  

	if ( $success ) {
		if ( $p_status != 'draft' && $p_status != 'future' && $p_status != 'pending' ) {
			$is_published = TRUE;
		}
		foreach ( $p_ser_edit as $ser_id ) {
			if ( empty($series_part[$ser_id]) && $automatic_series_part > 0 ) {
				$s_pt = wp_series_part($post_ID, $ser_id);
				if ( !$series_part ) $series_part = 0;
			}
			//If post is not published its part stays as set by user
			elseif ( !$is_published || $automatic_series_part === 0 ) {
				$s_pt = $series_part[$ser_id];
			}
			else {
				if ( isset($_GET['submit']) ) {
					$set_spart = sanitize_text_field($_GET['series_part']);
				}
				else {
					$set_spart =  sanitize_text_field($_POST['series_part']);
				}
                if(!empty($set_spart)){
                    $s_pt = $set_spart[$ser_id];
                }else{
                    $s_pt = '';
                }
			}
            
            if ($automatic_series_part > 0) 
            {
                set_series_order($ser_id, $post_ID, $s_pt, $is_published);
            }else {
                $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
                delete_post_meta($post_ID, $series_part_key);
                add_post_meta($post_ID, $series_part_key, $s_pt);
            }
		}

		return;
	} else {
		return FALSE;
	}
}

function wp_delete_post_series_relationship( $id, $ser_id = 0 ) {
	$postid = (int) $id;

	if (!empty($ser_id) ) {
		$series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
		delete_post_meta($postid, $series_part_key);
		delete_series_object_relationship($postid, $ser_id);
		return wp_reset_series_order_meta_cache($postid, $ser_id);
	}
	return false;
}

### taxonomy checks for series ####
function series_exists($series_name) {

	$series_name = is_numeric($series_name) ? (int) $series_name : $series_name;

	$id = term_exists($series_name, ppseries_get_series_slug());
	if ( is_array($id) )
		$id = $id['term_id'];
	return $id;
}

function delete_series_object_relationship( $object_id, $terms ) {
	global $wpdb;

	$object_id = (int) $object_id;
	$t_ids = array();

	if ( !is_array($terms) )
		$terms = array($terms);

	foreach ( $terms as $term ) {
		$t_obj = term_exists($term, ppseries_get_series_slug());
		if ( is_object($t_obj) )
			$t_ids[] = $t_obj->term_taxonomy_id;
	}

	if ( !empty($t_ids) ) {
		$in_tt_ids = "'" . implode("', '", $t_ids) . "'";
		$wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", $object_id) );
		wp_update_term_count($t_ids, ppseries_get_series_slug());
	}
}

function get_series_to_edit ( $id ) {
	$series = get_orgserial( $id, OBJECT, 'edit' );
	return $series;
}

function wp_create_single_series($series_name) {
	if ($id = series_exists($series_name) )
		return $id;

	return wp_insert_term( $series_name, ppseries_get_series_slug() );
}


function inline_edit_series($column_name, $type) {
	global $orgseries;
	$posttypes = apply_filters('orgseries_posttype_support', array('post') );
	if ( in_array($type, $posttypes) && $column_name == ppseries_get_series_slug() ) {
		?>
	<fieldset class="inline-edit-col-right"><div class="inline-edit-col">
		<div class="inline_edit_series_">
			<span><?php _e('Series:', 'organize-series'); ?></span>
			<?php wp_dropdown_series('name=post_series&class=post_series_select&hide_empty=0&show_option_none=No Series&context=quick-edit'); ?>
			<span><?php _e('Part:', 'organize-series'); ?></span>
			<input size="3" type="text" name="series_part" class="series_part"  />
			<input type="hidden" name="series_post_id" class="series_post_id"  />
			<input type="hidden" name="is_series_save" value="1" />


	</div></div></fieldset>
		<?php
	}
}

function bulk_edit_series($column_name, $type) {
	if ( $type == 'post' ) {
		?>
        <input type="hidden" name="bulk_edit_series" value="bulk" />
		<?php
	}


	if ( $type == 'post' && $column_name == ppseries_get_series_slug() ) {
		?>
        <fieldset class="inline-edit-col-right"><div class="inline-edit-col">
            <div class="inline_edit_series_">
                <span><?php _e('Series:', 'organize-series'); ?></span>
                <?php wp_dropdown_series('name=post_series&class=bulk_post_series_select&hide_empty=0&show_option_none=— No Change —&context=quick-edit'); ?>
			    <input type="hidden" name="series_part" class="series_part"  />
                <input type="hidden" name="series_post_id" class="series_post_id"  />
                <input type="hidden" name="is_series_save" value="1" />
    
    
        </div></div></fieldset>
		<?php
	}

}

function inline_edit_series_js() {
	wp_enqueue_script('inline-edit-series');
}





/**
 * Callback for split_shared_term action in WP.
 * This is used to help migrate users of Publishpress Series Multiples when term ids change.  This fix also will apply for
 * users of Publishpress Series Groups as well.
 * @param $old_term_id
 * @param $new_term_id
 * @param $term_taxonomy_id
 * @param $taxonomy
 */
function org_series_maybe_update_post_parts( $old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy ) {
	global $wpdb;
	//fix for series part for users of Publishpress Series Multiples
	$old_meta_key = SERIES_PART_KEY . '_' . $old_term_id;
	$new_meta_key = SERIES_PART_KEY . '_' . $new_term_id;
	$wpdb->update(
		$wpdb->postmeta,
		array( 'meta_key' => $new_meta_key ),
		array( 'meta_key' => $old_meta_key ),
		array( '%s' ),
		array( '%s' )
	);

	//fix for orgseries_grouping
	$old_group_title = 'series_grouping_' . $old_term_id;
	$new_group_title = 'series_grouping_' . $new_term_id;
	$wpdb->update(
		$wpdb->posts,
		array(
			'post_title' => $new_group_title,
			'post_name' => $new_group_title
		),
		array(
			'post_type' => 'series_grouping',
			'post_name' => $old_group_title
		),
		array( '%s', '%s' ),
		array( '%s', '%s' )
	);
}



function orgseries_fix_terms_changed() {
	if ( get_option( 'series_has_been_fixed', false ) ) {
		return;
	}
	global $wpdb;
	$terms_to_update = get_option('_split_terms');

	if ( $terms_to_update ) {
		foreach ( $terms_to_update as $old_term_id => $new_term_info ) {
			foreach ( $new_term_info as $taxonomy => $new_term_id ) {
				//fix series parts.
				$old_meta_key = SERIES_PART_KEY . '_' . $old_term_id;
				$new_meta_key = SERIES_PART_KEY . '_' . $new_term_id;
				$wpdb->update(
					$wpdb->postmeta,
					array( 'meta_key' => $new_meta_key ),
					array( 'meta_key' => $old_meta_key ),
					array( '%s' ),
					array( '%s' )
				);
				//fix for orgseries_grouping
				$old_group_title = 'series_grouping_' . $old_term_id;
				$new_group_title = 'series_grouping_' . $new_term_id;
				$wpdb->update(
					$wpdb->posts,
					array(
						'post_title' => $new_group_title,
						'post_name'  => $new_group_title
					),
					array(
						'post_type' => 'series_grouping',
						'post_name' => $old_group_title
					),
					array( '%s', '%s' ),
					array( '%s', '%s' )
				);
			}
		}
	}
	update_option( 'series_has_been_fixed', true );
}

/**
 * add_action() and add_filter() calls go here.
*/

//add_action for quick edit column
add_action('quick_edit_custom_box', 'inline_edit_series',9,2);
add_action('bulk_edit_custom_box', 'bulk_edit_series',9,2);
add_action('admin_print_scripts-edit.php', 'inline_edit_series_js');

//hook into save post for adding/updating series information to posts
add_action('save_post','series_wp_save_post',10,3);
add_action('future_to_publish','wp_set_post_series_transition',10,1);
add_action('draft_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('pending_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('delete_post','delete_series_post_relationship', 1);
add_action('trash_post', 'reset_series_order_on_trash', 1);
add_action('untrash_post', 'reset_series_order_on_trash', 1);

//prep for term splitting that happens in WP4.2+ (this is more for taking care of Publishpress Series Multiples users
add_action( 'split_shared_term', 'org_series_maybe_update_post_parts', 10, 4 );
add_action( 'admin_init', 'orgseries_fix_terms_changed' );

<?php
/**
* This file contains all the functions that can be used in themes for displaying series_grouping related info
*/

/** TEMPLATE TAGS **/


/** FIRST SOME UTILITY FUNCTIONS **/
/*
* get_series_in_group()
* This will return all the series in a specified group
* @package PublishPress Series
* @sub-package organize-series-grouping
* @since 1.0
*
* @param int|array $group_id The id for the group you want the list of series from or array of group ids.  group_id = term_id.
* @param string|array $args The values of what to search for when returning terms.  For a list of what args you can include see the "get_terms()" function found in /wp-includes/taxonomy.php
*
* @return array List of Series Objects.  Will return false if there are no series
*/
function get_series_in_group($group_id = '', $args = array() ) {
	$group_id = (int) $group_id;
	$seriesids = array();

	if (array_key_exists('taxonomy', $args) ) { //upgrade from 1.5
		$taxonomy = $args['taxonomy'];
	} else {
		$taxonomy = 'series_group';
	}

	if ( !empty( $group_id ) ) {
		$groups = get_objects_in_term($group_id, $taxonomy, $args);
	} else {
		return false;
	}
	if ( $groups ) {
		foreach ( $groups as $group ) {
			$g_id = orgseries_get_seriesid_from_group($group);
			if (!$g_id) continue;
			$seriesids[] = (int) $g_id;
		}
	}
	return $seriesids;
}

/* get_groups_in_series()
* This will return the groups (taxonomy) a series belongs to.
*
* @param int Series ID of series to list the groups for.
* @return stdClass[] array of term objects representing the groups the series belongs to (if there is) or false.
*/
function get_groups_in_series($seriesid) {
	$seriesid = (int) $seriesid;

	if ( $seriesid ) {
		$group_obj_id = orgseries_group_id($seriesid);
		$group_term_ids = wp_get_object_terms( $group_obj_id, 'series_group' );
	} else {
		return false;
	}

	return (array) $group_term_ids;
}

/* get_series_groups()
* This will return the ids for all the groups that contain series (useful for setting up a series_toc() page for groups).
* @param array ($args) allowing for changing which groups are returned.  Below are the options that can be affected by this $arg array:
*	orderby = Default is 'id', Can be id, name, or slug.
*	order - Default is ASC. Can use DESC.
* 	exclude = Default is empty array.  An array, comma- or space-delimited string of term_ids (groups) to exclude from the returned array.  If 'include' is non-empty, 'exclude' is ignored.
* 	exclude_tree - Default is an empty array. An array, comma or space-delimited string of group ids to exclude from the return array, along with all of their descendent groups.  If 'include' is non-empty, 'exclude_tree' is ignored.
* 	include - Default is empty array. An array, comma- or space-delimited string of term_ids to include in the return array.  If both include and exclude are empty then all the groups containing series will be returned.
*	number - The maximum number of groups to return. Default is to return them all.
* 	pad_counts - If set to true will include the quantity of a group's children in the quantity of each group's "count" object variable.
* 	offset - The number by which to offset the groups query.
* 	heirarchichal - Default is true. Use to indicate if we want any groups that have non-empty descendants (even if 'hide_empty' is set to true).
*	'child_of' - When used, should be set to the integer of the group_ID (term ID).  It's default is 0. If set to a non-zero value, all returned groups will be descendants of that group.
* 	'parent' - When used, should be set to the integer of a group ID (term ID). Its default is the empty string '', which has a different meaning from the integer 0. If set to an integer value, all returned groups will have as an immediate ancestor the group whose ID is specified by that integer. The 'parent' argument is different from 'child_of' in that a group X is considered a 'parent' of group Y only if group X is the father of group Y, not its grandfather or great-grandfather, etc.
* 	fields - Default is 'all', which returns an array of group objects (containing id, name, slug);
*		This can be set to just return 'ids', just 'names', or just 'slugs' OR you can ask for a count of all the series_groups. with 'count'
*
* @return array containing:  (term_id, name, slug) - for all "groups" that contain a series (and filtered by the submitted params.
*/
function get_series_groups( $args = array() ) {
	global $wpdb;

	$defaults = array (
		'orderby' => 'term_id',
		'order' => 'ASC',
		'exclude' => array(),
		'include' => array(),
		'number' => '',
		'fields' => 'all',
		'number' => '',
		'offset' => '',
		'exclusions' => '',
		'exclude_tree' => array(),
		'limit' => '',
		'parent' => '',
		'hierarchical' => true,
		'child_of' => 0,
		'pad_counts' => false,
		'hide_empty' => true,
		'taxonomy' => 'series_group'
		);

	$args = wp_parse_args( $args, $defaults );
	$args['number'] = absint( $args['number'] );
	$args['offset'] = absint( $args['offset'] );

	if ( '' !== $args['parent'] ) {
		$args['child_of'] = 0;
		$args['hierarchical'] = false;
		$args['pad_counts'] = false;
	}

	extract($args, EXTR_SKIP);

	if ( $child_of ) {
		$hierarchy = _get_term_hierarchy('series_group');
		if ( !isset($hierarchy[$parent]) )
			return $empty_array;
	}


	$_orderby = strtolower($orderby);
	if ( empty($_orderby) || 'id' == $_orderby )
		$orderby = 'seriest.term_id';
	elseif ( 'name' == $_orderby )
		$orderby = 'seriest.name';
	elseif ( 'slug' == $_orderby )
		$orderby = 'seriest.slug';
	elseif ( 'term_id' == $_orderby )
		$orderby = 'seriest.term_id';

	if ( !empty($orderby) )
		$orderby = "ORDER BY $orderby";
	else
		$order = '';

	$where = '';
	$inclusions = '';

	if ( !empty($include) ) {
		$exclude = '';
		$exclude_tree = '';
		$ingroups = wp_parse_id_list($include);

		foreach ( $ingroups as $ingroup ) {
			if ( empty($inclusions) )
				$inclusions = ' AND ( seriest.term_id = ' . intval($ingroup) . ' ';
			else
				$inclusions .= ' OR seriest.term_id = ' . intval($ingroup) . ' ';
		}
	}

	if ( !empty($inclusions) )
		$inclusions .= ')';
	$where .= $inclusions;

	$exclusions = '';
	if ( !empty( $exclude_tree ) ) {
		$excluded_trunks = wp_parse_id_list($exclude_tree);
		foreach ( $excluded_trunks as $extrunk ) {
			$excluded_children = (array) get_terms('series_group', array('child_of' => intval($extrunk), 'fields' => 'ids'));
			$excluded_children[] = $extrunk;
			foreach ( $excluded_children as $exgroup) {
				if ( empty($exclusions) )
					$exclusions = ' AND ( seriest.term_id <> ' . intval($exgroup) . ' ';
				else
					$exclusions .= ' AND seriest.term_id <> ' . intval($exgroup) . ' ';
			}
		}
	}

	if ( !empty($exclude) ) {
		$exgroups = wp_parse_id_list($exclude);
		foreach ( $exgroups as $exgroup ) {
			if ( empty($exclusions) )
				$exclusions = ' AND ( seriest.term_id <> ' . intval($exgroup) . ' ';
			else
				$exclusions .= ' AND seriest.term_id <> ' . intval($exgroup) . ' ';
		}
	}

	$where .= $exclusions;

	if ( '' !== $parent ) {
		$parent = (int) $parent;
		$where .= " AND seriesttt.parent = '$parent'";
	}

	if ( $hide_empty && !$hierarchical ) {
		$where .= ' AND seriesttt.count != 0';
	}

	//don't limit the query results when we have to descend the family tree
	if ( ! empty($number) && ! $hierarchical && empty( $child_of ) && '' === $parent ) {
		if ( $offset )
			$limit = 'LIMIT ' . $offset . ',' . $number;
		else
			$limit = 'LIMIT ' . $number;
	} else {
		$limit = '';
	}

	$selects = array();
	switch ( $fields ) {
		case 'all':
			$selects = array('seriest.*', 'seriesttt.*');
			break;
		case 'ids':
			$selects = array('seriest.term_id');
			break;
		case 'id=>parent':
			$selects = array('seriest.term_id', seriesttt.parent, 'seriesttt.count');
			break;
		case 'names':
			$selects = array('seriest.term_id', 'seriest.name');
			break;
		case 'slugs':
			$selects = array('seriest.term_id', 'seriest.slug');
			break;
		case 'count':
			$orderby = '';
			$order = '';
			$selects = array('COUNT(*)');
		}

	$select_this = implode(', ', $selects);

	$join = '';

	//OLD QUERY -> $query = "SELECT DISTINCT $select_this FROM $wpdb->term_relationships seriestt INNER JOIN $wpdb->posts seriesp ON seriestt.object_id = seriesp.ID INNER JOIN $wpdb->term_taxonomy seriesttt ON seriesttt.term_taxonomy_id = seriestt.term_taxonomy_id INNER JOIN $wpdb->terms seriest ON seriesttt.term_id = seriest.term_id WHERE seriesp.post_type = 'series_grouping' AND seriesttt.taxonomy = '$taxonomy' $where $orderby $order $limit";

	$query = $wpdb->prepare( "SELECT DISTINCT $select_this FROM $wpdb->terms AS seriest INNER JOIN $wpdb->term_taxonomy AS seriesttt ON seriesttt.term_id = seriest.term_id $join WHERE seriesttt.taxonomy = '%s' $where $orderby $order $limit", $taxonomy );

	if ( 'count' == $fields ) {
		$group_count = $wpdb->get_var($query);
		return $group_count;
	}

	$series_groups = $wpdb->get_results($query);


	if ( $child_of ) {
		$children = _get_term_hierarchy('series_group');
		if ( ! empty($children) )
			$series_groups = &_get_term_children($child_of, $series_groups, 'series_group');
	}

	//Update term counts to include children.
	if ( $pad_counts && 'all' == $fields )
		_pad_term_counts($series_groups, 'series_group');

	//Make sure we show empty groups that have children.
	if ( $hierarchical && $hide_empty && is_array($series_groups) ) {
		foreach ($series_groups as $k => $group ) {
			if ( ! $group->count ) {
				$children = _get_term_children($group->term_id, $series_groups, 'series_group');
				if ( is_array($children) )
					foreach ( $children as $child )
						if ( $child->count )
							continue 2;

				// It really is empty
				unset($series_groups[$k]);
			}
		}
	}
	reset ( $series_groups );

	$_groups = array();

	if ( 'id=>parent' == $fields ) {
		while ($group = array_shift($series_groups) )
			$_groups[$group->term_id] = $group->parent;
		$series_groups = $_groups;
	} elseif ( 'ids' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->term_id;
		$series_groups = $_groups;
	} elseif ( 'names' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->name;
		$series_groups = $_groups;
	} elseif ( 'slugs' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->slug;
		$series_groups = $_groups;
	}

	if ( 0 < $number && intval(@count($series_groups)) > $number )
		$series_groups = array_slice($series_groups, $offset, $number);

	return $series_groups;
}

/*
*  get_series_group_list() - used to output an unordered list containing a group title followed by the list of series in that group.  If no group_id is included then the function will return all the groups with the series in each group.  If group_id(s) are included then the groups requested will be returned.  Can also choose to "exclude" certain groups from the list.  This function uses the utility functions found on this page.
* @param $group_id can be just a string|int containing the id of one group that you want to return the list for OR it can be an array with four paramaters:  include (array, comma, or space deliminated list of group_ids to include), exclude (array, comma, or space deliminated list of group_ids to exclude), orderby (id=default, name, or slug), and order (asc=default/desc)
* @param $args (optional) you can indicate how you want the series list in each group to be returned.  For a list of what args you can include see the "get_terms()" function found in /wp-includes/taxonomy.php.
* @param bool (true|false) - true will echo the results, false will return the results.  default is true.
*
* @return string if false or echo string if true.
*/
function get_series_group_list( $group_id = array(), $args = array(), $echo = true ) {

	if ( !empty($group_id) ) {
		if ( !is_array($group_id) ) {
			$group_id = (int) $group_id;
			$group_id = array( 'include' => $group_id );
		} else {
			$group_id = array( 'include' => $group_id );
		}
	}

	$groups = get_series_groups( $group_id );
	$group_out = '';

	foreach ( $groups as $group ) {
		$group_out .= '<h3 id="group-title-'.$group->term_id.'" class="group-title">'.$group->name.'</h3>';
		$group_out .= '<ul id="group-list-'.$group->term_id.'" class="group-list-ul">';
		$series_in_group = get_series_in_group($group->term_id, $args );
		foreach ( $series_in_group as $series ) {
			$group_out .= '<li id="series-'.$series.'" class="series-list-li"><a href="'.get_series_link($series).'" title="'.get_series_name($series).' permalink">'.get_series_name($series).'</a></li>';
		}
		$group_out .= '</ul>';
	}

	if ( $echo )
		echo $group_out;
	else
		return $group_out;
}

/**
* DEPECRATED FUNCTIONS
* This section contains functions no longer active but still required for upgrade purposes.
**/
function get_old_series_groups( $args = array() ) {
	global $wpdb;

	$defaults = array (
		'orderby' => 'term_id',
		'order' => 'ASC',
		'exclude' => array(),
		'include' => array(),
		'number' => '',
		'fields' => 'all',
		'number' => '',
		'offset' => '',
		'exclusions' => '',
		'limit' => '',
		'hide_empty' => true,
		'taxonomy' => 'series_group'
		);

	$args = wp_parse_args( $args, $defaults );
	$args['number'] = absint( $args['number'] );
	$args['offset'] = absint( $args['offset'] );

	extract($args, EXTR_SKIP);

	$_orderby = strtolower($orderby);
	if ( empty($_orderby) || 'id' == $_orderby )
		$orderby = 'seriest.term_id';
	elseif ( 'name' == $_orderby )
		$orderby = 'seriest.name';
	elseif ( 'slug' == $_orderby )
		$orderby = 'seriest.slug';
	elseif ( 'term_id' == $_orderby )
		$orderby = 'seriest.term_id';

	if ( !empty($orderby) )
		$orderby = "ORDER BY $orderby";
	else
		$order = '';

	$where = '';
	$inclusions = '';

	if ( !empty($include) ) {
		$exclude = '';
		$ingroups = wp_parse_id_list($include);

		foreach ( $ingroups as $ingroup ) {
			if ( empty($inclusions) )
				$inclusions = ' AND ( seriest.term_id = ' . intval($ingroup) . ' ';
			else
				$inclusions .= ' OR seriest.term_id = ' . intval($ingroup) . ' ';
		}
	}

	if ( !empty($inclusions) )
		$inclusions .= ')';
	$where .= $inclusions;

	if ( !empty($exclude) ) {
		$exgroups = wp_parse_id_list($exclude);
		foreach ( $exgroups as $exgroup ) {
			if ( empty($exclusions) )
				$exclusions = ' AND ( seriest.term_id <> ' . intval($exgroup) . ' ';
			else
				$exclusions .= ' AND seriest.term_id <> ' . intval($exgroup) . ' ';
		}
	}

	$where .= $exclusions;

	if ( ! empty($number ) ) {
		if ( $offset )
			$limit = 'LIMIT ' . $offset . ',' . $number;
		else
			$limit = 'LIMIT ' . $number;
	}

	if ( $hide_empty ) {
		$where .= ' AND seriesttt.count != 0';
	}

	$selects = array();
	switch ( $fields ) {
		case 'all':
			$selects = array('seriest.term_id', 'seriest.name', 'seriest.slug');
			break;
		case 'ids':
			$selects = array('seriest.term_id');
			break;
		case 'names':
			$selects = array('seriest.term_id', 'seriest.name');
			break;
		case 'slugs':
			$selects = array('seriest.term_id', 'seriest.slug');
			break;
		case 'count':
			$orderby = '';
			$order = '';
			$selects = array('COUNT(*)');
		}

	$select_this = implode(', ', $selects);

	$query = $wpdb->prepare( "SELECT DISTINCT $select_this FROM $wpdb->term_relationships seriestt INNER JOIN $wpdb->posts seriesp ON seriestt.object_id = seriesp.ID INNER JOIN $wpdb->term_taxonomy seriesttt ON seriesttt.term_taxonomy_id = seriestt.term_taxonomy_id INNER JOIN $wpdb->terms seriest ON seriesttt.term_id = seriest.term_id WHERE seriesp.post_type = 'series_grouping' AND seriesttt.taxonomy = '%s' $where $orderby $order $limit", $taxonomy );

	if ( 'count' == $fields ) {
		$group_count = $wpdb->get_var($query);
		return $group_count;
	}

	$series_groups = $wpdb->get_results($query);

	$_groups = array();

	if ( 'ids' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->term_id;
		$series_groups = $_groups;
	} elseif ( 'names' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->name;
		$series_groups = $_groups;
	} elseif ( 'slugs' == $fields ) {
		while ( $group = array_shift($series_groups) )
			$_groups[] = $group->slug;
		$series_groups = $_groups;
	}

	if ( 0 < $number && intval(@count($series_groups)) > $number )
		$series_groups = array_slice($series_groups, $offset, $number);

	return $series_groups;

}
?>

<?php

defined('ABSPATH') || exit;
/* This file is for all the Publishpress Series related Term Queries and "template tags".

In most cases, the series functions listed here are just "wrappers" to save having to call the built-in functions for WordPress Custom Taxonomies.
 */

/**
 * get_the_series() - calls up all the series info from the taxonomy tables (for a particular post).
 */
function get_the_series($id = false, $cache = true)
{
    global $post, $term_cache;

    $id = (int) $id;

    if (!$id && (!empty($post) || $post != '' || $post != null)) {
        $id = (int) $post->ID;
    }

    if (empty($id)) {
        return false;
    }

    $series = $cache ? get_object_term_cache($id, ppseries_get_series_slug()) : false;

    if (false === $series) {
        $series = wp_get_object_terms($id, ppseries_get_series_slug());
    }

    $series = apply_filters('get_the_series', $series); //adds a new filter for users to hook into

    if (!empty($series)) {
        if (function_exists('wp_list_sort')) {
            wp_list_sort($series);
        } else {
            usort($series, '_usort_terms_by_name');
        }
    }

    return $series;
}

// Get the ID of a series from its name
function get_series_ID($series_name = 'default')
{
    $series = series_exists($series_name, ppseries_get_series_slug());
    if ($series) {
        return $series;
    }
    return 0;
}

/* functions referenced by other files */
function &get_series($args = '')
{
    global $wpdb;
    $series = array();

    $key = md5((string) wp_json_encode($args));
    if ($cache = wp_cache_get('get_series', ppseries_get_series_slug())) {
        if (isset($cache[$key])) {
            $series = apply_filters('get_series', $cache[$key], $args);
        }
    }
    if (!empty($series)) {
        return $series;
    }

    $series = get_terms(
        wp_parse_args(
            $args,
            array(
                'taxonomy' => ppseries_get_series_slug(),
            )
        )
    );

    if (is_wp_error($series) || empty($series)) {
        $series = [];
        return $series;
    }

    $cache = [];
    $cache[$key] = $series;
    wp_cache_set('get_series', $cache, ppseries_get_series_slug());

    $series = apply_filters('get_series', $series, $args);
    return $series;
}

function &get_orgserial($orgserial, $output = OBJECT, $filter = 'raw')
{
    $serie = get_term($orgserial, ppseries_get_series_slug(), $output, $filter);
    return $serie;
}


/*----------------------
 * POST RELATED FUNCTIONS (i.e. query etc. see post.php)
 --------------------*/

//will have to add the following function for deleting the series relationship when a post is deleted.
function delete_series_post_relationship($postid)
{
    $id = (int) $postid;
    $series = get_the_series($id);

    if (!empty($series)) { //let's not waste any cycles
        foreach ($series as $ser) {
            wp_reset_series_order_meta_cache($id, $ser->term_id);
        }
        return wp_delete_object_term_relationships($id, array(ppseries_get_series_slug()));
    }
    return;
}

//this will reorder the series parts when a post of a serie is moved to trash.
function reset_series_order_on_trash($postid)
{
    $id = (int) $postid;
    $series = get_the_series($id);

    if (!empty($series)) { //let's not waste any cycles
        foreach ($series as $ser) {
            wp_reset_series_order_meta_cache($id, $ser->term_id);
        }
    }
}

/**
 * Post statuses whose posts are counted and numbered in a series.
 *
 * Matches the statuses counted by _os_update_post_term_count(), so "part X of Y" uses the same set of posts.
 *
 * @return string[]
 */
function ppseries_series_counted_statuses()
{
    return (array) apply_filters('ppseries_series_counted_statuses', array('publish', 'private'));
}

/**
 * Whether a post currently counts towards (and is numbered in) a series.
 *
 * @param int $post_id Post ID.
 *
 * @return bool
 */
function ppseries_is_counted_series_post($post_id)
{
    $post = get_post((int) $post_id);
    if (! $post instanceof WP_Post) {
        return false;
    }

    return in_array($post->post_status, ppseries_series_counted_statuses(), true)
        && in_array($post->post_type, (array) apply_filters('orgseries_posttype_support', array('post')), true);
}

/**
 * Meta key that remembers where an unpublished post sat in a series.
 *
 * Holds array('part' => the part it had, 'previous' => IDs of all the posts before it, nearest first),
 * saved when it left the counted statuses.
 *
 * @param int $series_id Series term ID.
 *
 * @return string
 */
function ppseries_series_return_key($series_id)
{
    return apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id) . '_return';
}

/**
 * The place an unpublished post remembers in a series, if any.
 *
 * @param int $post_id   Post ID.
 * @param int $series_id Series term ID.
 *
 * @return array|null array('part' => int, 'previous' => int[]) or null.
 */
function ppseries_get_series_return($post_id, $series_id)
{
    $return = get_post_meta((int) $post_id, ppseries_series_return_key($series_id), true);
    if (! is_array($return) || ! isset($return['previous']) || ! is_array($return['previous'])) {
        return null;
    }

    return array(
        'part'     => isset($return['part']) ? (int) $return['part'] : 0,
        'previous' => array_map('intval', $return['previous']),
    );
}

/**
 * IDs of the counted, numbered posts in a series in part order (part, then date, then ID).
 *
 * Counted posts without a part number are left out.
 *
 * @param int $series_id       Series term ID.
 * @param int $include_post_id Also include this post (if it has a part) whatever its status.
 *
 * @return int[]
 */
function ppseries_get_numbered_series_post_ids($series_id, $include_post_id = 0)
{
    $series_id       = (int) $series_id;
    $include_post_id = (int) $include_post_id;
    $part_key        = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
    $object_ids      = get_objects_in_term($series_id, ppseries_get_series_slug());

    if (empty($object_ids) || is_wp_error($object_ids)) {
        return array();
    }

    $statuses = ppseries_series_counted_statuses();
    if ($include_post_id > 0 && get_post_status($include_post_id)) {
        $statuses[] = get_post_status($include_post_id);
    }

    $posts = get_posts(
        array(
            'include'                => array_map('intval', $object_ids),
            'post_type'              => (array) apply_filters('orgseries_posttype_support', array('post')),
            'post_status'            => array_unique($statuses),
            'numberposts'            => -1,
            'orderby'                => array('date' => 'ASC', 'ID' => 'ASC'),
            'update_post_term_cache' => false,
        )
    );

    $numbered = array();
    foreach ($posts as $index => $series_post) {
        $series_post_id = (int) $series_post->ID;
        if ($series_post_id !== $include_post_id && ! in_array($series_post->post_status, ppseries_series_counted_statuses(), true)) {
            continue;
        }

        $part = get_post_meta($series_post_id, $part_key, true);
        if (! is_numeric($part)) {
            continue;
        }

        $numbered[] = array(
            'id'    => $series_post_id,
            'part'  => (float) $part,
            'index' => $index,
        );
    }

    usort(
        $numbered,
        function ($a, $b) {
            if ($a['part'] === $b['part']) {
                return $a['index'] - $b['index'];
            }

            return ($a['part'] < $b['part']) ? -1 : 1;
        }
    );

    return array_map('intval', wp_list_pluck($numbered, 'id'));
}

/**
 * Add the posts that are away from a series (unpublished, trashed) at the place they remember.
 *
 * Used when another post leaves, so the place it remembers also accounts for neighbours that are away
 * and posts return in their original order whatever order they come back in.
 *
 * @param int   $series_id       Series term ID.
 * @param int[] $ordered_ids     Numbered post IDs in part order.
 * @param int   $skip_post_id    Post to leave out.
 *
 * @return int[]
 */
function ppseries_add_returning_series_posts($series_id, $ordered_ids, $skip_post_id = 0)
{
    $object_ids = get_objects_in_term((int) $series_id, ppseries_get_series_slug());
    if (empty($object_ids) || is_wp_error($object_ids)) {
        return $ordered_ids;
    }

    $away = array();
    foreach (array_map('intval', $object_ids) as $object_id) {
        if ($object_id === (int) $skip_post_id || in_array($object_id, $ordered_ids, true)) {
            continue;
        }
        $return = ppseries_get_series_return($object_id, $series_id);
        if (null !== $return) {
            $away[$object_id] = $return['previous'];
        }
    }

    //posts can remember other posts that are away, so place them over several passes.
    while (! empty($away)) {
        $placed = false;
        foreach ($away as $away_id => $previous_ids) {
            $position = empty($previous_ids) ? 0 : false;
            foreach ($previous_ids as $previous_id) {
                $previous_index = array_search($previous_id, $ordered_ids, true);
                if (false !== $previous_index) {
                    $position = $previous_index + 1;
                    break;
                }
                if (! isset($away[$previous_id])) {
                    continue;
                }
                $position = null; //wait until that post is placed.
                break;
            }
            if (null === $position) {
                continue;
            }
            array_splice($ordered_ids, false === $position ? 0 : $position, 0, array($away_id));
            unset($away[$away_id]);
            $placed = true;
        }
        if (! $placed) {
            break;
        }
    }

    return $ordered_ids;
}

/**
 * Renumber the counted posts of a series so their parts are unique and run 1..n.
 *
 * Posts keep their current relative order. Counted posts without a part number are left alone.
 * Optionally leave one post out and/or slot one post in.
 *
 * @param int        $series_id       Series term ID.
 * @param int        $exclude_post_id Post to leave out of the numbering. Its own part meta is not touched.
 * @param int        $insert_post_id  Post to slot in.
 * @param int        $insert_part     Part number to slot $insert_post_id in at. 0 or less appends; values past the end are clamped to the end.
 * @param int[]|null $insert_after    Post IDs (nearest first) to slot $insert_post_id in after; the first one still in the series wins,
 *                                    and if none is left the post becomes part 1. Overrides $insert_part when given.
 *
 * @return bool
 */
function ppseries_normalize_series_parts($series_id, $exclude_post_id = 0, $insert_post_id = 0, $insert_part = 0, $insert_after = null)
{
    $series_id       = (int) $series_id;
    $exclude_post_id = (int) $exclude_post_id;
    $insert_post_id  = (int) $insert_post_id;

    if ($series_id <= 0) {
        return false;
    }

    $part_key    = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
    $ordered_ids = array_values(array_diff(ppseries_get_numbered_series_post_ids($series_id), array($exclude_post_id, $insert_post_id)));

    if ($insert_post_id > 0) {
        if (is_array($insert_after)) {
            $position = 1;
            foreach (array_map('intval', $insert_after) as $previous_post_id) {
                $previous_index = array_search($previous_post_id, $ordered_ids, true);
                if (false !== $previous_index) {
                    $position = $previous_index + 2;
                    break;
                }
            }
        } else {
            $position = (int) $insert_part;
            if ($position <= 0 || $position > count($ordered_ids) + 1) {
                $position = count($ordered_ids) + 1;
            }
        }
        array_splice($ordered_ids, $position - 1, 0, array($insert_post_id));
    }

    foreach ($ordered_ids as $offset => $series_post_id) {
        $new_part = $offset + 1;
        if ((string) get_post_meta($series_post_id, $part_key, true) !== (string) $new_part) {
            delete_post_meta($series_post_id, $part_key);
            add_post_meta($series_post_id, $part_key, $new_part);
        }
    }

    return true;
}

/**
 * Keep part numbers unique and contiguous when a post enters or leaves the counted statuses.
 *
 * - Leaving (publish/private -> draft, pending, future, trash): the post's part is cleared and it remembers
 *   the posts that came before it; the other posts close the gap.
 * - Entering (draft, pending, future, trash -> publish/private): the post goes back right after the nearest
 *   of those posts still in the series (or first if none is left). Posts that never had a place use the part
 *   set while unpublished, or are appended.
 * - publish <-> private: nothing changes, both are counted.
 *
 * Runs before the {old}_to_{new} hooks, so set_series_order() there finds the post already in place.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Old post status.
 * @param WP_Post $post       Post object.
 */
function refresh_series_count_on_status_change($new_status, $old_status, $post)
{
    if (! $post instanceof WP_Post || $new_status === $old_status) {
        return;
    }

    $counted_statuses = ppseries_series_counted_statuses();
    $was_counted      = in_array($old_status, $counted_statuses, true);
    $is_counted       = in_array($new_status, $counted_statuses, true);

    if ($was_counted === $is_counted) {
        return;
    }

    if (! in_array($post->post_type, (array) apply_filters('orgseries_posttype_support', array('post')), true)) {
        return;
    }

    $series_ids = wp_get_post_series($post->ID);
    if (empty($series_ids) || is_wp_error($series_ids)) {
        return;
    }

    $series_ids = array_map('intval', $series_ids);
    foreach ($series_ids as $series_id) {
        $part_key   = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
        $return_key = ppseries_series_return_key($series_id);

        if ($is_counted) {
            $return = ppseries_get_series_return($post->ID, $series_id);
            delete_post_meta($post->ID, $return_key);
            ppseries_normalize_series_parts(
                $series_id,
                0,
                $post->ID,
                (int) get_post_meta($post->ID, $part_key, true),
                null !== $return ? $return['previous'] : null
            );
        } else {
            $ordered_ids = ppseries_add_returning_series_posts($series_id, ppseries_get_numbered_series_post_ids($series_id, $post->ID), $post->ID);
            $position    = array_search((int) $post->ID, $ordered_ids, true);
            if (false !== $position) {
                //all the posts before it, so it can still find its place after many of them are deleted.
                update_post_meta(
                    $post->ID,
                    $return_key,
                    array(
                        'part'     => (int) get_post_meta($post->ID, $part_key, true),
                        'previous' => array_reverse(array_slice($ordered_ids, 0, $position)),
                    )
                );
                delete_post_meta($post->ID, $part_key);
            }
            ppseries_normalize_series_parts($series_id, $post->ID);
        }
    }

    //wp_update_term_count_now() takes term_taxonomy IDs, not term IDs. Core recounts on status changes from WP 5.7; this covers 5.5/5.6.
    $series_tt_ids = wp_get_object_terms($post->ID, ppseries_get_series_slug(), array('fields' => 'tt_ids'));
    if (! empty($series_tt_ids) && ! is_wp_error($series_tt_ids)) {
        wp_update_term_count_now(array_map('intval', $series_tt_ids), ppseries_get_series_slug());
    }
    clean_object_term_cache($post->ID, ppseries_get_series_slug());
}

//call up series post is associated with -- needed for the post-edit panel specifically.
function wp_get_post_series($post_id = 0, $args = array())
{
    $post_id = (int) $post_id;
    $defaults = array('fields' => 'ids');
    $args = wp_parse_args($args, $defaults);
    $series = wp_get_object_terms($post_id, ppseries_get_series_slug(), $args);
    return $series;
}

//function to set the order that the post is in a series.
function set_series_order($series_id, $postid = 0, $series_part = 0, $is_published = false)
{
    if (!isset($series_id)) {
        return false; // if post doesn't belong to a series yet.
    }

    $postid = (int) $postid;
    $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);

    if ((int) $series_part === 0) {
        if (!$is_published) {
            //not published posts part shouldn't be updated
            return false;
        }
        //keep the part the post already has; a post without one is appended.
        $series_part = get_post_meta($postid, $series_part_key, true);
    }

    //counted posts are slotted in so the parts in the series stay unique and contiguous.
    if (ppseries_is_counted_series_post($postid)) {
        delete_post_meta($postid, ppseries_series_return_key($series_id));
        return ppseries_normalize_series_parts($series_id, 0, $postid, (int) $series_part);
    }

    if ((int) $series_part === 0) {
        return false;
    }

    //the Series box (classic editor, Pro box in the block editor, quick edit) posts back the part it loaded.
    //Saving an unpublished post with the part it had when it left is not a new choice: keep its place.
    $return = ppseries_get_series_return($postid, $series_id);
    if (null !== $return && $return['part'] > 0 && (int) $series_part === $return['part']) {
        return true;
    }

    //a part chosen for an unpublished post wins over the place it remembers.
    delete_post_meta($postid, ppseries_series_return_key($series_id));
    delete_post_meta($postid, $series_part_key);
    add_post_meta($postid, $series_part_key, $series_part);
    return true;
}

function wp_reset_series_order_meta_cache($post_id = 0, $series_id = 0, $reset = false)
{

    if (0 == $series_id) {
        return false; //post is not a part of a series so no need to waste cycles.
    }

    $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);

    if ($reset) {
        $post_ids_in_series = get_objects_in_term($series_id, ppseries_get_series_slug());
        foreach ((array) $post_ids_in_series as $spost) {
            $spost_id = is_array($spost) && isset($spost['object_id']) ? $spost['object_id'] : $spost;
            delete_post_meta($spost_id, $series_part_key);
        }
        return true;
    }

    //close the gap left by $post_id (being deleted or removed from the series).
    return ppseries_normalize_series_parts($series_id, (int) $post_id);
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
 *                              if single:  "'post'"
 *                              if multiple use comma to separate: "'post','page'"
 * @param bool $hide_empty - If True, only Series with published posts will be included (no empty series will be included)
 *
 * @return array $series - an array of all series rows as pulled from database (each series listed once)
 */

function get_series_ordered($args = '')
{
    global $wpdb;
    $post_types = apply_filters('orgseries_posttype_support', array('post'));
    $defaults = array('orderby' => 'term_id', 'order' => 'DESC', 'postTypes' => $post_types, 'hide_empty' => true);
    $args = wp_parse_args($args, $defaults);
    $orderby = $args['orderby'];
    $order = strtoupper($args['order']) === 'ASC' ? 'ASC' : 'DESC';
    $postTypes = $args['postTypes'];
    $hide_empty = $args['hide_empty'];

    $orderby = strtolower($orderby);
    $_orderby = 't.term_id';
    if ('post_date' == $orderby) {
        if ('ASC' == $order) {
            $_orderby = 'min(tp.post_date)';
        } else {
            $_orderby = 'max(tp.post_date)';
        }
    } elseif ('post_modified' == $orderby) {
        if ('ASC' == $order) {
            $_orderby = 'min(tp.post_modified)';
        } else {
            $_orderby = 'max(tp.post_modified)';
        }
    } elseif ('name' == $orderby) {
        $_orderby = 't.name';
    } elseif ('slug' == $orderby) {
        $_orderby = 't.slug';
    } elseif (empty($orderby) || 'id' == $orderby || 'term_id' == $orderby) {
        $_orderby = 't.term_id';
    } elseif ('count' == $orderby) {
        $_orderby = 'tt.count';
    } elseif ('rand' == $orderby) {
        $_orderby = 'RAND()';
    }

    $having = '';

    if ($hide_empty) {
        $having = 'HAVING count(tp.id) > 0 ';
    }

    $postTypes = array_values(array_filter(array_map('sanitize_key', (array) $postTypes)));
    if (!$postTypes) {
        return array();
    }
    $post_type_placeholders = implode(', ', array_fill(0, count($postTypes), '%s'));

    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Sort and HAVING fragments are fixed; post types and taxonomy use placeholders.
    $query = $wpdb->prepare("SELECT t.term_id, t.name, t.slug FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id LEFT OUTER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT OUTER JOIN $wpdb->posts AS tp ON tp.ID = tr.object_id and tp.post_status IN ( 'publish', 'private' ) and tp.post_type in ($post_type_placeholders) WHERE tt.taxonomy = %s GROUP BY t.term_id, t.name, t.slug $having ORDER BY $_orderby $order", array_merge($postTypes, array(ppseries_get_series_slug())));
    $series = $wpdb->get_results($query); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Query prepared above.
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
function wp_dropdown_series($args)
{
    $defaults = array(
        'show_option_all' => '',
        'show_option_none' => '',
        'show_option_not_in_series' => '',
        'orderby' => 'id',
        'order' => 'ASC',
        'show_last_update' => 0,
        'show_count' => 0,
        'hide_empty' => 1,
        'child_of' => 0,
        'exclude' => '',
        'echo' => 1,
        'selected' => 0,
        'hierarchical' => 0,
        'name' => SERIES_QUERYVAR,
        'id' => '',
        'class' => 'postform',
        'depth' => 0,
        'tab_index' => 0,
        'taxonomy' => ppseries_get_series_slug(),
        'hide_if_empty' => false,
        'context' => 'normal'
    );

    $series_id = get_query_var(SERIES_QUERYVAR);

    if (is_numeric($series_id) && isset($args['context']) && $args['context'] == 'normal') {
        $series_id = get_term_field('slug', $series_id, ppseries_get_series_slug());
    }

    $defaults['selected'] = (!empty($series_id) || $series_id != null) ? $series_id : 0;

    $r = wp_parse_args($args, $defaults);

    if (!isset($r['pad_counts']) && $r['show_count'] && $r['hierarchical']) {
        $r['pad_counts'] = true;
    }

    $r['include_last_update_time'] = $r['show_last_update'];
    $show_option_all = $r['show_option_all'];
    $show_option_none = $r['show_option_none'];
    $show_option_not_in_series = $r['show_option_not_in_series'];
    $hierarchical = $r['hierarchical'];
    $tab_index = $r['tab_index'];
    $name = $r['name'];
    $id = $r['id'];
    $class = $r['class'];
    $taxonomy = $r['taxonomy'];
    $echo = $r['echo'];

    $tab_index_attribute = '';
    if ((int) $tab_index > 0) {
        $tab_index_attribute = " tabindex=\"$tab_index\"";
    }

    //remove $name from the get_terms() query because it means something different in 4.2, so we'll just exclude it
    //from $r
    if (isset($r['name'])) {
        unset($r['name']);
    }
    $series = get_terms(array_merge($r, array('taxonomy' => $taxonomy)));
    $name = esc_attr($name);
    $class = esc_attr($class);
    $id = $id ? esc_attr($id) : $name;

    if (!$r['hide_if_empty'] || !empty($series)) {
        $output = "<select name='$name' id='$id' class='$class' $tab_index_attribute>\n";
    } else {
        $output = '';
    }

    if (empty($series) && !$r['hide_if_empty'] && !empty($show_option_none)) {
        $show_option_none = apply_filters('list_series', $show_option_none);
        $output .= "\t<option value='0' selected='selected'>$show_option_none</option>\n";
    }

    if (!empty($series)) {
        if ($show_option_all) {
            $show_option_all = apply_filters('list_series', $show_option_all);
            $selected = ('0' === strval($r['selected'])) ? " selected='selected'" : '';
            $output .= "\t<option value='0'$selected>$show_option_all</option>\n";
        }

        if ($show_option_none) {
            $show_option_none = apply_filters('list_series', $show_option_none);
            if ($r['selected'] == 0) {
                $r['selected'] = '-1';
            }
            $selected = ('-1' === strval($r['selected'])) ? " selected='selected'" : '';
            $output .= "\t<option value='-1'$selected>$show_option_none</option>\n";
        }

        if ($show_option_not_in_series) {
            $show_option_not_in_series = apply_filters('list_series', $show_option_not_in_series);
            $selected = ('0' === strval($r['selected'])) ? " selected='selected'" : '';
            $output .= "\t<option value='0'$selected>$show_option_not_in_series</option>\n";
        }

        if ($hierarchical) {
            $depth = $r['depth']; // Walk the full depth.
        } else {
            $depth = -1; // Flat.
        }

        $output .= walk_series_dropdown_tree($series, $depth, $r);
    }
    if (!$r['hide_if_empty'] || !empty($series)) {
        $output .= "</select>\n";
    }


    $output = apply_filters('wp_dropdown_series', $output);

    if ($echo) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }

    return $output;
}

function wp_list_series($args = '')
{
    global $orgseries;
    $defaults = array(
        'title_li' => _x('Series', 'series list title', 'organize-series'),
        'taxonomy' => ppseries_get_series_slug(),
        'echo' => 1
    );
    $args = wp_parse_args($args, $defaults);
    $echo_ser = $args['echo'];
    $args['echo'] = 0; // to make sure wp_list_categories is always returned for the wrapper.
    $output = wp_list_categories($args);

    if ($echo_ser) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $output;
    }

    return $output;
}

function wp_set_post_series_transition($post)
{
    remove_action('save_post', 'wp_set_post_series', 10);
    $post_ID = $post->ID;
    $ser_id = wp_get_post_series($post_ID);

    foreach ($ser_id as $sid) {
        set_series_order($sid, $post_ID, 0, true);
    }
}

function wp_set_post_series_draft_transition($post)
{
    remove_action('save_post', 'wp_set_post_series');
    $post_ID = $post->ID;
    $ser_id = wp_get_post_series($post_ID);
    foreach ($ser_id as $sid) {
        set_series_order($sid, $post_ID, 0, true);
    }
}

function orgseries_set_missing_series_order($object_id, $terms, $tt_ids, $taxonomy)
{
    if ($taxonomy !== ppseries_get_series_slug()) {
        return;
    }

    if (empty($terms) && empty($tt_ids)) {
        return;
    }

    $post_ID = (int) $object_id;
    if ($post_ID <= 0) {
        return;
    }

    $post_status = get_post_status($post_ID);
    if (!in_array($post_status, array('publish', 'private'), true)) {
        return;
    }

    $series_ids = array();
    foreach ((array) $terms as $term) {
        if (is_numeric($term)) {
            $series_ids[] = (int) $term;
        }
    }

    if (empty($series_ids)) {
        $series_ids = wp_get_post_series($post_ID);
    }

    if (empty($series_ids) || !is_array($series_ids)) {
        return;
    }

    foreach ($series_ids as $series_id) {
        $series_id = (int) $series_id;
        if ($series_id <= 0) {
            continue;
        }

        $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $series_id);
        $series_part     = get_post_meta($post_ID, $series_part_key, true);
        if (!empty(trim((string) $series_part))) {
            continue;
        }

        set_series_order($series_id, $post_ID, 0, true);
    }
}

function series_wp_save_post($post_ID, $post, $update)
{
    wp_set_post_series($post, $update, $post_ID);
}

// phpcs:disable WordPress.Security.NonceVerification -- Core post/inline/bulk edit handlers verify nonces before save_post.
function wp_set_post_series($post, $update, $post_ID = 0, $series_id = array(), $dont_skip = false, $is_published = false)
{
    $post_series = null;
    $post_shorttitle = array();
    global $orgseries;

    if (!isset($_REQUEST['series_part']) || !isset($_REQUEST['post_series'])) {
        return;
    }

    $settings = $orgseries->settings;

    $automatic_series_part = 0;

    if (!is_bool($update)) {
        return; //safety check for users on earlier version of WP (so existing series don't get messed up)
    }

    if (!is_object($post)) {
        return;
    }


    //fix for the revisions feature in WP 2.6+  && bulk-edit stuff.
    if ($post->post_type == 'revision' || (isset($_GET['bulk_edit_series']) && $_GET['bulk_edit_series'] == 'bulk' && isset($_GET['post_series']) && $_GET['post_series'] == -1) || !isset($_REQUEST['is_series_save'])) {
        return;
    }


    $post_ID = (int) $post_ID;
    $old_series = wp_get_post_series($post_ID);
    $old_series = is_array($old_series) ? $old_series : array();

    if (empty($series_id)) {
        $post_series = isset($_REQUEST['post_series']) && is_array($_REQUEST['post_series']) ? array_map('sanitize_text_field', wp_unslash($_REQUEST['post_series'])) : array(sanitize_text_field(wp_unslash($_REQUEST['post_series'])));
    } else {
        $post_series = is_array($series_id) ? $series_id : array($series_id);
    }

    $post_series = os_strarr_to_intarr($post_series);

    if (empty($post_series) || (count($post_series) >= count($old_series))) {
        $match = false;
    } else {
        $match = array_diff($old_series, $post_series);
    }

    if (empty($post_series) || (count($post_series) == 1 && $post_series[0] == 0)) {
        $post_series = array();
    }


    if (isset($_POST) || isset($_GET)) {
        if (isset($_POST['series_part'])) {
            $series_part = is_array($_POST['series_part']) ? array_map('sanitize_text_field', wp_unslash($_POST['series_part'])) : array(sanitize_text_field(wp_unslash($_POST['series_part'])));
        }
        if (isset($_GET['series_part'])) {
            $series_part = is_array($_GET['series_part']) ? array_map('sanitize_text_field', wp_unslash($_GET['series_part'])) : array(sanitize_text_field(wp_unslash($_GET['series_part'])));
        }

        //The "short" title of the post that will be displayed  in the OrgSeries widget.
        $update_short_title = false;
        if (isset($_POST['serie_post_shorttitle'])) {
            $update_short_title = true;
            $post_shorttitle = is_array($_POST['serie_post_shorttitle']) ? array_map('sanitize_text_field', wp_unslash($_POST['serie_post_shorttitle'])) : sanitize_text_field(wp_unslash($_POST['serie_post_shorttitle']));
        }
        if (isset($_GET['serie_post_shorttitle'])) {
            $update_short_title = true;
            $post_shorttitle = is_array($_GET['serie_post_shorttitle']) ? array_map('sanitize_text_field', wp_unslash($_GET['serie_post_shorttitle'])) : sanitize_text_field(wp_unslash($_GET['serie_post_shorttitle']));
        }

        $st_ser_id = is_array($post_series) && isset($post_series[0]) ? (int) $post_series[0] : 0;

        if (is_array($post_shorttitle) && isset($post_shorttitle[$st_ser_id])) {
            $post_shorttitle = trim($post_shorttitle[$st_ser_id]);
        } elseif (is_array($post_shorttitle) && count($post_shorttitle) === 1 && isset($post_shorttitle[0])) {
            $post_shorttitle = trim($post_shorttitle[0]);
        } else {
            $post_shorttitle = '';
        }

        if ($update_short_title) {
            update_post_meta($post->ID, SPOST_SHORTTITLE_KEY, $post_shorttitle);
        }

        //if we don't have any changes in the series or series part info (or series post status) then let's get out and save time.
        $p_status = $post->post_status;
        if ($p_status != 'draft' && $p_status != 'future' && $p_status != 'pending') {
            $is_published = true;
        }
        $count = count($post_series);
        $c_chk = 0;
        foreach ($post_series as $ser) {
            $post_series_part = wp_series_part($post_ID, $ser);
            if (in_array($ser, $old_series) && isset($series_part[$ser]) && !empty($post_series_part) && $series_part[$ser] == $post_series_part && !$dont_skip) {
                $c_chk++;
                continue;
            } else {
                $p_ser_edit[] = $ser; //these are the series we need to set the parts for (leave the rest alone when we get to this section).
            }
        }

        if ($c_chk == $count && !empty($post_series) && count($post_series) == count($old_series) && !$dont_skip) {
            return; //there are no changes so let's just skip the rest
        }
    }


    if (empty($post_series)) {
        foreach ($old_series as $o_ser) {
            $part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $o_ser);
            delete_post_meta($post_ID, $part_key);
        }
    }

    foreach ($old_series as $os_id) {
        if (!in_array($os_id, $post_series)) {
            wp_delete_post_series_relationship($post_ID, $os_id);
        }
    }


    if (!empty($match) && $match) {
        foreach ($match as $part_reset_id) {
            wp_reset_series_order_meta_cache($post_ID, $part_reset_id);
        }
    }

    $success = wp_set_object_terms($post_ID, $post_series, ppseries_get_series_slug());

    if (empty($p_ser_edit)) {
        return; //let's get out we've done everything we need to do.
    }



    if ($success) {
        if ($p_status != 'draft' && $p_status != 'future' && $p_status != 'pending') {
            $is_published = true;
        }
        foreach ($p_ser_edit as $ser_id) {
            //If post is not published its part stays as set by user
            if (!$is_published || $automatic_series_part === 0) {
                $s_pt = isset($series_part[$ser_id]) ? $series_part[$ser_id] : '';
            } else {
                if (isset($_GET['submit'])) {
                    $set_spart = isset($_GET['series_part']) ? array_map('sanitize_text_field', (array) wp_unslash($_GET['series_part'])) : array();
                } else {
                    $set_spart = isset($_POST['series_part']) ? array_map('sanitize_text_field', (array) wp_unslash($_POST['series_part'])) : array();
                }
                if (!empty($set_spart)) {
                    $s_pt = isset($set_spart[$ser_id]) ? $set_spart[$ser_id] : '';
                } else {
                    $s_pt = '';
                }
            }

            set_series_order($ser_id, $post_ID, $s_pt, $is_published);
        }

        return;
    } else {
        return false;
    }
}
// phpcs:enable WordPress.Security.NonceVerification

function wp_delete_post_series_relationship($id, $ser_id = 0)
{
    $postid = (int) $id;

    if (!empty($ser_id)) {
        $series_part_key = apply_filters('orgseries_part_key', SERIES_PART_KEY, $ser_id);
        delete_post_meta($postid, $series_part_key);
        delete_post_meta($postid, ppseries_series_return_key($ser_id));
        delete_series_object_relationship($postid, $ser_id);
        return wp_reset_series_order_meta_cache($postid, $ser_id);
    }
    return false;
}

### taxonomy checks for series ####
function series_exists($series_name)
{

    $series_name = is_numeric($series_name) ? (int) $series_name : $series_name;

    $id = term_exists($series_name, ppseries_get_series_slug());
    if (is_array($id)) {
        $id = $id['term_id'];
    }
    return $id;
}

function delete_series_object_relationship($object_id, $terms)
{
    global $wpdb;

    $object_id = (int) $object_id;
    $t_ids = array();

    if (!is_array($terms)) {
        $terms = array($terms);
    }

    foreach ($terms as $term) {
        $t_obj = term_exists($term, ppseries_get_series_slug());
        if (is_object($t_obj)) {
            $t_ids[] = $t_obj->term_taxonomy_id;
        }
    }

    if (!empty($t_ids)) {
        $t_ids = array_map('absint', $t_ids);
        $in_tt_ids = implode(', ', array_fill(0, count($t_ids), '%d'));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- The IN fragment contains only generated integer placeholders.
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d AND term_taxonomy_id IN ($in_tt_ids)", array_merge(array($object_id), $t_ids)));
        wp_update_term_count($t_ids, ppseries_get_series_slug());
    }
}

function get_series_to_edit($id)
{
    $series = get_orgserial($id, OBJECT, 'edit');
    return $series;
}

function wp_create_single_series($series_name)
{
    if ($id = series_exists($series_name)) {
        return $id;
    }

    return wp_insert_term($series_name, ppseries_get_series_slug());
}


function inline_edit_series($column_name, $type)
{
    global $orgseries;
    $posttypes = apply_filters('orgseries_posttype_support', array('post'));
    if (in_array($type, $posttypes) && $column_name == ppseries_get_series_slug()) {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <div class="inline_edit_series_">
                    <span><?php esc_html_e('Series:', 'organize-series'); ?></span>
                    <?php wp_dropdown_series('name=post_series&class=post_series_select&hide_empty=0&show_option_none=No Series&context=quick-edit'); ?>
                    <span style="display:none;"><?php esc_html_e('Part:', 'organize-series'); ?></span>
          <input style="display:none;" size="3" type="text" name="series_part" class="series_part" aria-label="<?php esc_attr_e('Series Part', 'organize-series'); ?>" />
                    <input type="hidden" name="series_post_id" class="series_post_id" />
                    <input type="hidden" name="is_series_save" value="1" />


                </div>
            </div>
        </fieldset>
        <?php
    }
}

function bulk_edit_series($column_name, $type)
{
    if ($type == 'post') {
        ?>
        <input type="hidden" name="bulk_edit_series" value="bulk" />
        <?php
    }


    if ($type == 'post' && $column_name == ppseries_get_series_slug()) {
        ?>
        <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
                <div class="inline_edit_series_">
                    <span><?php esc_html_e('Series:', 'organize-series'); ?></span>
                    <?php wp_dropdown_series('name=post_series&class=bulk_post_series_select&hide_empty=0&show_option_none=— No Change —&show_option_not_in_series=Not part of a series&selected=-1&context=quick-edit'); ?>
                    <input type="hidden" name="series_part" class="series_part" />
                    <input type="hidden" name="series_post_id" class="series_post_id" />
                    <input type="hidden" name="is_series_save" value="1" />


                </div>
            </div>
        </fieldset>
        <?php
    }
}

function inline_edit_series_js()
{
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
function org_series_maybe_update_post_parts($old_term_id, $new_term_id, $term_taxonomy_id, $taxonomy)
{
    global $wpdb;
    //fix for series part for users of Publishpress Series Multiples
    $old_meta_key = SERIES_PART_KEY . '_' . $old_term_id;
    $new_meta_key = SERIES_PART_KEY . '_' . $new_term_id;
    $wpdb->update(
        $wpdb->postmeta,
        array('meta_key' => $new_meta_key),
        array('meta_key' => $old_meta_key),
        array('%s'),
        array('%s')
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
        array('%s', '%s'),
        array('%s', '%s')
    );
}



function orgseries_fix_terms_changed()
{
    if (get_option('series_has_been_fixed', false)) {
        return;
    }
    global $wpdb;
    $terms_to_update = get_option('_split_terms');

    if ($terms_to_update) {
        foreach ($terms_to_update as $old_term_id => $new_term_info) {
            foreach ($new_term_info as $taxonomy => $new_term_id) {
                //fix series parts.
                $old_meta_key = SERIES_PART_KEY . '_' . $old_term_id;
                $new_meta_key = SERIES_PART_KEY . '_' . $new_term_id;
                $wpdb->update(
                    $wpdb->postmeta,
                    array('meta_key' => $new_meta_key),
                    array('meta_key' => $old_meta_key),
                    array('%s'),
                    array('%s')
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
                    array('%s', '%s'),
                    array('%s', '%s')
                );
            }
        }
    }
    update_option('series_has_been_fixed', true);
}

/**
 * add_action() and add_filter() calls go here.
 */

//add_action for quick edit column
add_action('quick_edit_custom_box', 'inline_edit_series', 9, 2);
add_action('bulk_edit_custom_box', 'bulk_edit_series', 9, 2);
add_action('admin_print_scripts-edit.php', 'inline_edit_series_js');

//hook into save post for adding/updating series information to posts
add_action('save_post', 'series_wp_save_post', 10, 3);
add_action('set_object_terms', 'orgseries_set_missing_series_order', 10, 4);
add_action('future_to_publish', 'wp_set_post_series_transition', 10, 1);
add_action('draft_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('pending_to_publish', 'wp_set_post_series_draft_transition', 10, 1);
add_action('transition_post_status', 'refresh_series_count_on_status_change', 10, 3);
//before_delete_post: wp_delete_post() removes the term relationships before delete_post fires.
add_action('before_delete_post', 'delete_series_post_relationship', 1);
add_action('delete_post', 'delete_series_post_relationship', 1);
//add_action('trash_post', 'reset_series_order_on_trash', 1);
//add_action('untrash_post', 'reset_series_order_on_trash', 1);

//prep for term splitting that happens in WP4.2+ (this is more for taking care of Publishpress Series Multiples users
add_action('split_shared_term', 'org_series_maybe_update_post_parts', 10, 4);
add_action('admin_init', 'orgseries_fix_terms_changed');

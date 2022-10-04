<?php
/**
 * Contains all the legacy includes (and hooks) for the plugin.  Eventually this will make its way into the new refactor
 * of the plugin. But while in transition this allows these legacy files to be included as necessary.
 */
global $pagenow, $wp_version, $checkpage;
$checkpage= $pagenow;
global $checkpage;
define('OS_GROUPING_LEGACY_LOADED', true);

//All inits
add_action('init', 'orgseries_grouping_posttype');
add_action('init', 'orgseries_grouping_import_existing_series');
add_action('init', 'orgseries_grouping_taxonomy');
//add_action('admin_init', 'orgseries_upgrade_check');
add_action('init', 'orgseries_manage_grouping_columns');

//allow filtering on manage_series page
add_filter('init', 'orgseries_manage_grouping_filter_setup');

//Create Admin Menu item under "Posts" for easy groups management
add_action('admin_menu', 'orgseries_groups_admin_menu');

//hook into the existing PublishPress Series Options page to add grouping options.
add_action('admin_init', 'orgseries_grouping_settings_setup');

//all scripts and css
add_action('admin_enqueue_scripts', 'orgseries_groups_scripts');
add_action('admin_enqueue_scripts', 'orgseries_groups_styles');

//hook in to existing Series Management page
add_filter('manage_edit-'.ppseries_get_series_slug().'_columns', 'manage_series_grouping_columns',10);
add_filter('manage_'.ppseries_get_series_slug().'_custom_column', 'manage_series_grouping_columns_inside',10,3);
add_action(''.ppseries_get_series_slug().'_add_form_fields', 'add_orgseries_group_fields',1);
add_action(''.ppseries_get_series_slug().'_edit_form', 'edit_orgseries_group_fields',2,2);
if ($wp_version < '3.1') {
    add_filter('manage_edit-tags_columns', 'manage_series_grouping_columns');
}

//add new queryvar and custom joins for the group filter (on manage series page) - TODO DISABLED currently - still working for future version.
//add_action('parse_query', 'orgseries_group_parsequery');
//add_filter('query_vars', 'orgseries_group_add_queryvars');
//add_filter('posts_where', 'orgseries_group_where');


//hook into terms api
add_action('created_'.ppseries_get_series_slug().'', 'wp_insert_series_group', 1, 2);
add_action('edited_'.ppseries_get_series_slug().'', 'wp_update_series_group', 1, 2);
add_action('delete_'.ppseries_get_series_slug().'', 'wp_delete_series_group', 1, 2);

//render Series Category template
add_filter('template_include','pp_series_group_template');

add_action('pp_series_advanced_tab_top', 'series_grouping_delete_output');

add_shortcode('publishpress_series_categories', 'publishpress_series_groups');

function pp_series_group_template($template){
    if ( is_tax('series_group') ) {
        $theme_template = locate_template( array( 'taxonomy-series_group.php' ) ); //checks in child theme (if child theme) and then parent theme.
        if ( !$theme_template ) {
            $template = plugin_dir_path(__FILE__) . 'theme/primary/taxonomy-series_group.php';
        }

        global $orgseries;
        if ( empty($orgseries) && class_exists('orgSeries') ) {
          $orgseries = new orgSeries();
        }
        $orgseries->series_load_theme_css();
    }
    return $template;
}

function orgseries_upgrade_check() {
    global $orgseries_groups_ver;
    //below is where I will indicate any upgrade routines that need to be run
    $version_check = get_option('orgseries_grouping_version');
    if ( !$version_check )  { //this may be the first time orgseries is used
        if ( $is_imported = get_option('orgseries_grouping_import_completed') ) // we know a version 1.5 and earlier was previously installed (before we saved version numbers) - update needed
            upgrade_orgseries_grouping_from_one_five();
        add_option('orgseries_grouping_version', $orgseries_groups_ver);
        add_option('orgser_grp_upgrade_'.$orgseries_groups_ver);
        return;
    }

    orgseries_grouping_upgrade($orgseries_groups_ver, $version_check);

    update_option('orgseries_grouping_version', $orgseries_groups_ver);
}

/*
*
* This is the function for doing any upgrades necessary
*/

function orgseries_grouping_upgrade($this_version, $old_version) {
    global $wpdb;

    if ( $old_version == '1.6' ) {
        //let's fix up any potential errors in the database from a bad 1.5-1.6 import
        //First up is a fix for object_id == 0;
        $object_id = 0;
        $wpdb->query( $wpdb->prepare("DELETE FROM $wpdb->term_relationships WHERE object_id = %d", $object_id) );

        //next up is reset the term_counts for all series_groups so they are correct.
        $args = array(
            'hide_empty' => false,
            'fields' => 'ids'
        );

        $groups = get_series_groups($args);
        $groups = array_map('intval', $groups);
        $groups = implode(', ',$groups);
        $query = "SELECT term_taxonomy_id FROM $wpdb->term_taxonomy WHERE term_id IN ( $groups ) AND taxonomy = 'series_group'";
        $terms = $wpdb->get_results( $query );
        while ( $group = array_shift($terms) )
            $_groups[] = $group->term_taxonomy_id;
        $series_groups = $_groups;
        update_series_group_count($series_groups, 'series_group');
        exit;
        return true;
    }
    return true;
}

function upgrade_orgseries_grouping_from_one_five() {
    //if ( !taxonomy_exists('series_group') )
    //orgseries_grouping_taxonomy();

    //let's get all the existing Series Categories in the old category system
    $args = array(
        'hide_empty' => false,
        'fields' => 'ids',
        'taxonomy' => 'category'
    );


    $old_groups = get_old_series_groups($args); //list of category ids that are groups

    $args_b = array(
        'include' => $old_groups,
        'hide_empty' => false
    );

    $_old_groups = get_terms('category', $args_b); //need to do this in order to get the description field.

    $args_c = array(
        'hide_empty' => false,
        'taxonomy' => 'category'
    );

    //let's set up the new groups in the new taxonomy system
    if ( empty($_old_groups) ) return;
    foreach ( $_old_groups as $new_group ) {
        wp_insert_term(
            $new_group->name,
            'series_group',
            array(
                'description' => $new_group->description,
                'slug' => $new_group->slug
            )
        );

        //let's get the series from the old groups, add to the new taxonomy, and then remove them from the old groups.  We'll leave the old groups (categories) in case there are regular posts added to them.
        $get_series = get_series_in_group($new_group->term_id, $args_c);
        $ser_term_id = (int) $new_group->term_id;

        if ( empty($get_series) ) continue;
        foreach ( $get_series as $serial ) {
            $id = orgseries_group_id($serial);

            $post_arr = array(
                'ID' => $id,
                'post_status' => 'publish',
            );
            wp_update_post($post_arr);
            wp_set_object_terms($id, $ser_term_id, 'series_group', true);
        }
    }

    $group_ids = get_objects_in_term( $old_groups, 'category', array( 'hide_empty' => false));

    if ( empty($group_ids) ) return;
    foreach ($group_ids as $p_id) {
        wp_delete_object_term_relationships($p_id,'category');
    }
}

function orgseries_grouping_posttype() {
    global $checkpage, $_GET;
    $args = array(
        'description' => 'Used for associating Series with series categoriess',
        'public' => false,
        'public_queryable' => true,
        'taxonomies' => array('category', 'series_group'),
        'rewrite' => array('slug' => 'seriesgroup')
    );

    register_post_type('series_grouping', $args);

    if ( 'edit-tags.php' == $checkpage &&  ( isset($_GET['taxonomy']) && ppseries_get_series_slug() == $_GET['taxonomy'] ) ) {
        require_once(ABSPATH.'wp-admin/includes/meta-boxes.php');
        add_action('quick_edit_custom_box', 'orgseries_group_inline_edit', 9,3);

    }

}

function orgseries_grouping_taxonomy() {
    $permalink_slug = 'series_group';
    $object_type = array('series_grouping');
    $capabilities = array(
        'manage_terms' => 'manage_series',
        'edit_terms' => 'manage_series',
        'delete_terms' => 'manage_series',
        'assign_terms' => 'manage_series'
    );
    $labels = array(
        'name' => _x('Series Categories', 'taxonomy general name', 'organize-series'),
        'singular_name' => _x('Series Category', 'taxonomy singular name', 'organize-series'),
        'search_items' => __('Search Series Categories', 'organize-series'),
        'popular_items' => __('Popular Series Categories', 'organize-series'),
        'all_items' => __('All Series Categories', 'organize-series'),
        'edit_item' => __('Edit Series Category', 'organize-series'),
        'update_item' => __('Update Series Category', 'organize-series'),
        'add_new_item' => __('Add New Series Category', 'organize-series'),
        'new_item_name' => __('New Series Category', 'organize-series'),
        'menu_name' => __('Series Categories', 'organize-series')
    );
    $args = array(
        'update_count_callback' => 'update_series_group_count',
        'labels' => $labels,
        'rewrite' => array( 'slug' => $permalink_slug, 'with_front' => true ),
        'show_ui' => true,
        'public' => true,
        'capabilities' => $capabilities,
        'query_var' => 'series_group',
        'hierarchical' => true
    );
    register_taxonomy( 'series_group', $object_type, $args );
}

function update_series_group_count($terms, $taxonomy) {
    global $wpdb;
    if ( !is_array($terms) ) $terms = (array) $terms;
    $terms = array_map('intval', $terms);
    $taxonomy = 'series_group';
    foreach ( (array) $terms as $term) {
        $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term) );
        $wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );
    }

    clean_term_cache($terms, '', false);
    return true;
}


function orgseries_grouping_import_existing_series() {
    //do a check to see if there are existing series and NO existing posts in the 'series_grouping' post_type.  If this is the case then we need to do the import.  If not, then we break out.
    if ( !($is_imported = get_option('orgseries_grouping_import_completed')) ) {
        $series = get_terms( ppseries_get_series_slug(), array('hide_empty'=>false, 'fields' => 'ids') );

        foreach ( $series as $this_series ) {
            $post_args = array(
                'post_type' => 'series_grouping',
                'to_ping' => 0,
                'post_title' => 'series_grouping_'.$this_series,
                'post_name' => 'series_grouping_'.$this_series
            );
            wp_insert_post($post_args);
        }
        add_option('orgseries_grouping_import_completed','1');

    }
}

function orgseries_groups_admin_menu() {
    add_submenu_page( 'edit.php', 'Series Categories', 'Series Categories', 'manage_series', 'edit-tags.php?taxonomy=series_group');
}

function orgseries_group_add_queryvars($qvs) {
    $qvs[] = 'series_group';
    return $qvs;
}

function orgseries_group_parsequery() {
    global $wp_query;
    if ( isset($wp_query->query_vars['series_group']) ) {
        $wp_query->is_series_group = true;
    } else {
        $wp_query->is_series_group = false;
    }
}

function orgseries_group_where($where) {
    global $wpdb, $wp_query;
    if ( $wp_query->is_series_group && is_admin() ) {
        $series_group = $wp_query->query_vars['series_group'];
        $series_array = get_series_in_group($series_group);
        $series_string = implode(",", $series);
        $where .= " AND t.term_id IN ({$series_string})";
    }
    return $where;
}

function orgseries_manage_grouping_filter_setup() {
    global $_GET, $wp_version;
    if ( !empty($_GET['ser_grp']) && is_admin() && $wp_version >= 3.1 ) {
        add_filter('get_terms_args', 'orgseries_grp_term_filter', 10, 2);
    }
}

function orgseries_grp_term_filter($args, $taxonomies) {
    global $_GET;
    if ( in_array(ppseries_get_series_slug(), $taxonomies) ) {
        $group_id = (int) $_GET['ser_grp'];
        $series_array = get_series_in_group($group_id);
        $args['include'] = $series_array;
    }
    return $args;
}

function orgseries_manage_grouping_columns() {
    global $wp_version;
    //hook into manage-series-groups page
    add_filter('manage_edit-series_group_columns', 'series_grouping_columns', 10);
    add_filter('manage_series_group_custom_column', 'series_grouping_columns_inside',1,3);
    add_filter('manage_edit-series_group_sortable_columns', 'series_group_sortable_columns');
    if ($wp_version >= '3.1')
        add_action('after-series-table', 'select_series_group_filter');
}

function orgseries_grouping_settings_setup() {
    //add_settings_field('series_grouping_delete_settings','Series Categories Addon Settings','series_grouping_delete_output', 'orgseries_options_page','series_uninstall_settings');
    register_setting('orgseries_options', 'org_series_options');
    add_filter('orgseries_options', 'series_grouping_options_validate', 10, 2);
}

function series_grouping_options_validate($newinput, $input) {
    $newinput['kill_grouping_on_delete'] = ( isset($input['kill_grouping_on_delete']) && $input['kill_grouping_on_delete'] == 1 ? 1 : 0 );
    return $newinput;
}

function series_grouping_delete_output() {
    global $orgseries;
    $org_opt = $orgseries->settings;
    $org_name = 'org_series_options';
    $k_on_delete = is_array($org_opt) && isset($org_opt['kill_grouping_on_delete']) ? $org_opt['kill_grouping_on_delete'] : 0;
    ?>
        	<tr valign="top">
            	<th scope="row"><label for="kill_grouping_on_delete">
                    <?php _e('Series Categories Feature', 'organize-series'); ?>
                	</label>
            	</th>
            	<td>
                    <label>
                    <input name="<?php echo $org_name; ?>[kill_grouping_on_delete]" id="kill_grouping_on_delete" type="checkbox" value="1" <?php checked('1', $k_on_delete); ?> />
                    <span class="description"><?php _e('Delete all "Series Categories" data from the database when deleting the plugin.', 'organize-series'); ?></span>
                	</label>
                </td>
        	</tr>
    <?php
}

function orgseries_groups_scripts() {
    global $checkpage;
    $url = plugin_dir_url(__FILE__).'/js/';

    if ( 'edit-tags.php' == $checkpage && ppseries_get_series_slug() == $_GET['taxonomy'] ) {
        wp_enqueue_script( 'wp_ajax_response' );
        wp_enqueue_script( 'wp-lists' );
        wp_enqueue_script( 'editor' );
        wp_enqueue_script( 'postbox' );
        wp_enqueue_script( 'post' );
        wp_register_script('inline-edit-groups', $url.'series-groups.js');
        wp_enqueue_script('inline-edit-groups');
    }
}

function orgseries_groups_styles() {
    global $checkpage;
    $plugin_path = plugin_dir_url(__FILE__) . '/';
    $csspath = $plugin_path . 'orgseries-grouping.css';
    $csspath_min = $plugin_path.'orgseries-grouping-small.css';
    wp_register_style('orgseries_group_main_style', $csspath, array('global'), get_bloginfo('version'),'screen and (min-width: 1100px)');
    wp_register_style('orgseries_group_small_style', $csspath_min, array('global'), get_bloginfo('version'), 'screen and (max-width: 1100px)');
    wp_register_style('orgseries_group_small_on_edit', $csspath_min);

    if ( 'edit-tags.php' == $checkpage && ppseries_get_series_slug() == $_GET['taxonomy'] && isset($_GET['action'] ) && 'edit' == $_GET['action'] ) {
        wp_enqueue_style('orgseries_group_main_style');
        wp_enqueue_style('orgseries_group_small_style');
    }

    if ( 'edit-tags.php' == $checkpage && ppseries_get_series_slug() == $_GET['taxonomy'] ) {
        wp_enqueue_style('orgseries_group_small_on_edit');
    }
}

function series_grouping_columns($columns) {
    unset($columns['posts']);
    $columns[''.ppseries_get_series_slug().''] =  __('Series', 'organize-series');
    return $columns;
}

function series_group_sortable_columns($sortable) {
    $sortable[''.ppseries_get_series_slug().''] = 'count';
    return $sortable;
}

function select_series_group_filter($taxonomy) {
    //TODO: would be much better if WordPress provided a way of simply adding this in via a do_action.  But for the time being we'll add this as a hidden form after the table and use jQuery to move it to the top of the table after page load.
    if ( !empty($_GET['ser_grp']) ) $group_id = (int) $_GET['ser_grp'];
    if ( empty($group_id) ) $group_id = -1;
    $dropdown_args = array(
        'show_option_all' => 'View all Series Categories',
        'selected' => $group_id,
        'taxonomy' => 'series_group',
        'name' => 'ser_grp',
        'hide_empty' => false
    );
    ?>
    <div style="display:none;">
        <form id="series_group_filter" style="float:right" action method="get">
            <input type="hidden" name="taxonomy" value="series" />
            <?php wp_dropdown_categories($dropdown_args); ?>
            <input type="submit" name="group_filter" id="filter-query-submit" class="button-secondary" value="Filter">
        </form>
    </div>
    <?php
}

function series_grouping_columns_inside($content, $column_name, $id) {
    global $wp_version;
    $column_return = $content;
    if ($column_name == ppseries_get_series_slug()) {
        $get = get_series_in_group($id);
        if ( $get == '' ) $count = '0';
        else $count = count($get);
        if ( $wp_version >= '3.1' )
            $g_link = '<a href="edit-tags.php?taxonomy='.ppseries_get_series_slug().'&ser_grp='.$id.'">'.$count.'</a>';
        else
            $g_link = $count;
        $column_return = '<p style="width: 40px; text-align: center;">'.$g_link.'</p>';
    }
    return $column_return;
}

function manage_series_grouping_columns($columns) {
    global $pagenow;
    $columns['group'] = __('Series Categories', 'organize-series');
    return $columns;
}

function manage_series_grouping_columns_inside($content, $column_name, $id) {
    $column_return = $content;
    if ($column_name == 'group') {
        $group_id = orgseries_group_id($id);

        $column_return .= '<div class="group_column">';

        if ( $groups = wp_get_object_terms($group_id, 'series_group') ) {
            foreach ( $groups as $group ) {
                $column_return .= '<div class="series-group">'.$group->name . '</div> ';
                $cat_id[] = $group->term_id;
                $cat_name[] = $group->name;
            }
            $category_ids = implode(",",$cat_id);
            $category_names = implode(",",$cat_name);
            $column_return .= '<div class="hidden" id="inline_group_'.$id.'"><div class="group_inline_edit" id="sergroup_'.$id.'">'.$category_ids.'</div><div class="group_inline_name">'.$category_names.'</div></div>';
        } else {
            $column_return .= '&mdash;';
            $column_return .= '<div class="hidden" id="inline_group_"><div class="group_inline_edit">0</div><div class="group_inline_name"></div></div>';
        }
        $column_return .= '</div>';
    }
    return $column_return;
}

function add_orgseries_group_fields($taxonomy) {
    $empty = '';
    $empty = (object) $empty;
    $empty->ID = '';
    $box['args'] = array(
        'taxonomy' => 'series_group'
    );
    ?>
    <div class="form-field metabox-holder has-right-sidebar">
        <div id="side-info-column" class="inner-sidebar">
            <div id="side-sortables" class="meta-box-sortables">
                <div id="categorydiv" class="postbox">
                    <div class="handlediv" title="<?php _e('Click to toggle'); ?>"><br /></div><h3 class='hndle'><span><?php _e('Series Categories', 'organize-series'); ?></span></h3>
                    <div class="inside">
                        <?php post_categories_meta_box( $empty, $box ); ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php
}

function edit_orgseries_group_fields($series, $taxonomy) {
    global $orgseries;
    $series_ID = $series->term_id;
    $groupID = orgseries_group_id($series_ID);
    $post_arr = array( 'ID' => $groupID, 'post_type' => 'series_grouping' );
    $groups = wp_get_object_terms(array($groupID), 'series_group', array('fields' => 'ids'));

    ?>
    <tr class="form-field term-groups-wrap">
			<th scope="row"><label for="groups"><h3 class='hndle'><span><?php _e('Series Categories'); ?></span></h3></label></th>
			<td>
        <div class="metabox-holder">
            <div id="side-info-column" class="">
                <div id="" class="">
                    <div id="categorydiv" class="postbox">
                        <div class="inside">
                            <div id="taxonomy-category" class="categorydiv">
                                <ul id="category-tabs" class="category-tabs">
                                    <li class="tabs"><a href="#category-all" tabindex="3"><?php _e('All Series Categories'); ?></a></li>
                                </ul>
                                <div id="category-all" class="tabs-panel">
                                    <ul id="categorychecklist" class="list:category categorychecklist form-no-clear">
                                        <?php wp_terms_checklist($groupID,array('selected_cats' => $groups, 'taxonomy' => 'series_group' )); ?>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
      </td>
		</tr>
    <?php
}

function orgseries_group_inline_edit($column_name, $type, $taxonomy) {
    if ( empty($taxonomy) && $type != 'edit-tags' )
        $taxonomy = $type; //this takes care of WP 3.1 changes
    if ( $taxonomy == ppseries_get_series_slug() && $column_name == 'group' ) { //takes care of WP3.1 changes (prevents duplicate output)
        ?>
        <fieldset class="inline-edit-col-right inline-edit-categories"><div class="inline-edit-col">
			<span class="title inline-edit-categories-label"><?php _e('Series Categories', 'organize-series'); ?>
                <span class="catshow"><?php _e('[more]'); ?></span>
				<span class="cathide" style="display:none;"><?php _e('[less]'); ?></span>
			</span>
                <div class="inline_edit_group_">
                    <input type="hidden" name="post_category[]" value="0" />
                    <input type="hidden" name="series_group_id" class="series_group_id" value="" />
                    <ul class="cat-checklist category-checklist">
                        <?php wp_terms_checklist(null, array('taxonomy' => 'series_group'))	 ?>
                    </ul>
                </div>
            </div></fieldset>
        <?php
    }
}

function wp_insert_series_group($series_id, $taxonomy_id) {
    global $_POST;
    extract($_POST, EXTR_SKIP);
    if ( !empty($tax_input['series_group']) )
        $terms = os_stringarray_to_intarray($tax_input['series_group']);

    $post_arr = array(
        'post_type' => 'series_grouping',
        'to_ping' => 0,
        'post_title' => 'series_grouping_'.$series_id,
        'post_name' => 'series_grouping_'.$series_id
    );
    $group_id = wp_insert_post($post_arr);
    if ( !empty($tax_input['series_group']) )
        wp_set_object_terms($group_id, $terms, 'series_group', true);
    return $group_id;
}

function wp_update_series_group($series_id, $taxonomy_id) {
    global $_POST;

    extract($_POST, EXTR_SKIP);

    $tax_input['series_group'] = !isset( $tax_input['series_group'] ) ? array() : $tax_input['series_group'];

    $terms = os_stringarray_to_intarray((array) $tax_input['series_group']);
    $id = orgseries_group_id($series_id);
    wp_set_object_terms($id, $terms, 'series_group');
    return $id;
}

function wp_delete_series_group($series_id, $taxonomy_id) {
    global $_POST;
    extract($_POST, EXTR_SKIP);
    $id = orgseries_group_id( (int) $series_id );
    wp_delete_post($id,true);
    //TODO check, do we need wp_delete_post_term_relationship here?
}

function orgseries_group_id($series_id) {
    $post_title = 'series_grouping_'.$series_id;
    $query = array( 'post_status' => array( 'draft', 'publish' ), 'name' => $post_title, 'post_type' => 'series_grouping');
    $series_posts = get_posts( $query );
    $series_post = reset( $series_posts );
    $group_id = $series_post instanceof WP_Post ? $series_post->ID : 0;
    if ( $series_id && ! $group_id ) {
        //looks like the series didn't get added as a custom post for some reason.  Let's fix that
        return wp_insert_series_group($series_id, '');
    }
    return $group_id;
}

function orgseries_get_seriesid_from_group($group_id) {
    $grouppost = get_post($group_id);
    if (!$grouppost || $grouppost->post_type != 'series_grouping' ) return false;
    $series_name = $grouppost->post_name;
    $series_id = ltrim($series_name, 'series_grouping_');
    $series_id = (int) $series_id;
    return $series_id;
}

//INCLUDE TEMPLATE TAGS FILE//
require_once(plugin_dir_path(__FILE__) . '/orgseries-grouping-template-tags.php');

//helper functions
function os_stringarray_to_intarray($array) {
    function to_int(&$val, $key) {
        $val = (int) $val;
    }

    array_walk($array, 'to_int');

    return $array;
}



/**
 * Shortcode to get the author box
 *
 * @param array $attributes
 *
 * @return string
 */
function publishpress_series_groups($attributes)
{

    return get_series_group_list([], [], false);
}
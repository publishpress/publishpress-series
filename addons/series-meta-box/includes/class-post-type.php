<?php
/**
 * Custom post type registration for Series Meta Boxes
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Meta_Box_Post_Type
{
    /**
     * Hook registration
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'register_post_type']);
    }

    /**
     * Register the CPT used to store Series Meta Boxes
     */
    public static function register_post_type()
    {
        $labels = [
            'name'                  => __('Series Meta Boxes', 'organize-series'),
            'singular_name'         => __('Series Meta Box', 'organize-series'),
            'menu_name'             => __('Series Meta Boxes', 'organize-series'),
            'name_admin_bar'        => __('Series Meta Box', 'organize-series'),
            'add_new'               => __('Add New', 'organize-series'),
            'add_new_item'          => __('Add New Series Meta Box', 'organize-series'),
            'new_item'              => __('New Series Meta Box', 'organize-series'),
            'edit_item'             => __('Edit Series Meta Box', 'organize-series'),
            'view_item'             => __('View Series Meta Box', 'organize-series'),
            'all_items'             => __('All Series Meta Boxes', 'organize-series'),
            'search_items'          => __('Search Series Meta Boxes', 'organize-series'),
            'not_found'             => __('No Series Meta Boxes found.', 'organize-series'),
            'not_found_in_trash'    => __('No Series Meta Boxes found in Trash.', 'organize-series'),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => false,
            'show_in_rest'       => false,
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title'],
            'rewrite'            => false,
            'has_archive'        => false,
            'menu_position'      => null,
        ];

        register_post_type(PPS_Series_Meta_Box_Utilities::POST_TYPE, $args);
    }
}

<?php
/**
 * Custom post type registration for Series Post Navigation
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Post_Type
{
    /**
     * Hook registration
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'register_post_type']);
    }

    /**
     * Register the CPT used to store Series Post Navigation
     */
    public static function register_post_type()
    {
        $labels = [
            'name'                  => __('Series Post Navigation', 'publishpress-series'),
            'singular_name'         => __('Series Post Navigation', 'publishpress-series'),
            'menu_name'             => __('Series Post Navigation', 'publishpress-series'),
            'name_admin_bar'        => __('Series Post Navigation', 'publishpress-series'),
            'add_new'               => __('Add New', 'publishpress-series'),
            'add_new_item'          => __('Add New Series Post Navigation', 'publishpress-series'),
            'new_item'              => __('New Series Post Navigation', 'publishpress-series'),
            'edit_item'             => __('Edit Series Post Navigation', 'publishpress-series'),
            'view_item'             => __('View Series Post Navigation', 'publishpress-series'),
            'all_items'             => __('All Series Post Navigation', 'publishpress-series'),
            'search_items'          => __('Search Series Post Navigation', 'publishpress-series'),
            'not_found'             => __('No Series Post Navigation found.', 'publishpress-series'),
            'not_found_in_trash'    => __('No Series Post Navigation found in Trash.', 'publishpress-series'),
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

        register_post_type(PPS_Series_Post_Navigation_Utilities::POST_TYPE, $args);
    }
}

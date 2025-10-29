<?php
/**
 * Custom post type registration for Series Post Details
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Details_Post_Type
{
    /**
     * Hook registration
     */
    public static function init()
    {
        add_action('init', [__CLASS__, 'register_post_type']);
    }

    /**
     * Register the CPT used to store Series Post Details
     */
    public static function register_post_type()
    {
        $labels = [
            'name'                  => __('Series Post Details', 'organize-series'),
            'singular_name'         => __('Series Post Details', 'organize-series'),
            'menu_name'             => __('Series Post Details', 'organize-series'),
            'name_admin_bar'        => __('Series Post Details', 'organize-series'),
            'add_new'               => __('Add New', 'organize-series'),
            'add_new_item'          => __('Add New Series Post Details', 'organize-series'),
            'new_item'              => __('New Series Post Details', 'organize-series'),
            'edit_item'             => __('Edit Series Post Details', 'organize-series'),
            'view_item'             => __('View Series Post Details', 'organize-series'),
            'all_items'             => __('All Series Post Details', 'organize-series'),
            'search_items'          => __('Search Series Post Details', 'organize-series'),
            'not_found'             => __('No Series Post Details found.', 'organize-series'),
            'not_found_in_trash'    => __('No Series Post Details found in Trash.', 'organize-series'),
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

        register_post_type(PPS_Series_Post_Details_Utilities::POST_TYPE, $args);
    }
}

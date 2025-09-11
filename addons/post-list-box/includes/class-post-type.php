<?php
/**
 * Post Type Registration for Post List Box
 */

class PPS_Post_List_Box_Post_Type {
    
    const POST_TYPE_BOXES = 'pps_post_list_box';
    
    /**
     * Initialize post type registration
     */
    public static function init() {
        // Register immediately during the current init cycle to avoid timing issues
        self::register_post_type();
    }
    
    /**
     * Register the post types.
     */
    public static function register_post_type()
    {
        $labelSingular = __('Post List Box', 'organize-series');
        $labelPlural = __('Post List Boxes', 'organize-series');

        $postTypeLabels = [
            'name' => _x('%2$s', 'Post List Box post type name', 'organize-series'),
            'singular_name' => _x(
                '%1$s',
                'singular post list box post type name',
                'organize-series'
            ),
            'add_new' => __('New %1s', 'organize-series'),
            'add_new_item' => __('Add New %1$s', 'organize-series'),
            'edit_item' => __('Edit %1$s', 'organize-series'),
            'new_item' => __('New %1$s', 'organize-series'),
            'all_items' => __('%2$s', 'organize-series'),
            'view_item' => __('View %1$s', 'organize-series'),
            'search_items' => __('Search %2$s', 'organize-series'),
            'not_found' => __('No %2$s found', 'organize-series'),
            'not_found_in_trash' => __('No %2$s found in Trash', 'organize-series'),
            'parent_item_colon' => '',
            'menu_name' => _x('%2$s', 'custom layout post type menu name', 'organize-series'),
            'featured_image' => __('%1$s Image', 'organize-series'),
            'set_featured_image' => __('Set %1$s Image', 'organize-series'),
            'remove_featured_image' => __('Remove %1$s Image', 'organize-series'),
            'use_featured_image' => __('Use as %1$s Image', 'organize-series'),
            'filter_items_list' => __('Filter %2$s list', 'organize-series'),
            'items_list_navigation' => __('%2$s list navigation', 'organize-series'),
            'items_list' => __('%2$s list', 'organize-series'),
        ];

        foreach ($postTypeLabels as $labelKey => $labelValue) {
            $postTypeLabels[$labelKey] = sprintf($labelValue, $labelSingular, $labelPlural);
        }

        $postTypeArgs = [
            'labels' => $postTypeLabels,
            'public' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false,
            'map_meta_cap' => true,
            'has_archive' => false,
            'hierarchical' => false,
            'rewrite' => false,
            'supports' => ['title'],
        ];
        register_post_type(self::POST_TYPE_BOXES, $postTypeArgs);
    }
}
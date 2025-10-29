<?php
/**
 * Fields Manager for Post List Box
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Post_List_Box_Fields {
    
    const META_PREFIX = 'pps_post_list_box_';
    
    /**
     * Get the fields tabs to be rendered in the post list box editor
     *
     * @param WP_Post $post object.
     *
     * @return array
     */
    public static function get_fields_tabs($post)
    {
        $fields_tabs = [
            'box'     => [
                'label'  => __('Box Container', 'organize-series'),
                'icon'   => 'dashicons dashicons-feedback',
            ],
            'item'  => [
                'label'  => __('Post', 'organize-series'),
                'icon'   => 'dashicons dashicons-screenoptions',
            ],
            'layout'  => [
                'label' => __('Layout', 'organize-series'),
                'icon'  => 'dashicons dashicons-editor-table',
            ],
        ];

        /**
         * Customize fields tabs presented in the post list boxes editor.
         *
         * @param array $fields_tabs Existing fields tabs to display.
         * @param WP_Post $post object.
         */
        $fields_tabs = apply_filters('pps_post_list_box_editor_fields_tabs', $fields_tabs, $post);

        return $fields_tabs;
    }

    /**
     * Get the fields to be rendered in the post list box editor
     *
     * @param WP_Post $post object.
     *
     * @return array
     */
    public static function get_fields($post)
    {
        $fields = [];

        /**
         * Customize fields presented in the post list box editor.
         *
         * @param array $fields Existing fields to display.
         * @param WP_Post $post object.
         */
        $fields = apply_filters('pps_post_list_box_editor_fields', $fields, $post);

        return $fields;
    }

    /**
     * Get Post List Box meta value
     *
     * @param integer $post_id
     * @param boolean $use_default
     * @return array $editor_data
     */
    public static function get_post_list_box_layout_meta_values($post_id, $use_default = false) {

        if ($use_default || empty(get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true))) {
            $editor_data = self::get_default_post_list_box_data();
        } else {
            $editor_data = (array) get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true);
        }

        $editor_data['post_id'] = $post_id;

        return apply_filters('pps_post_list_box_get_layout_meta_values', $editor_data, $post_id, $use_default);
    }

    /**
     * Get default post list box data
     *
     * @return array
     */
    public static function get_default_post_list_box_data() {
        return [
            'title_show' => 1,
            'title_html_tag' => 'h3',
            'title_color' => '#2971B1',
            'title_font_size' => 24,
            'background_color' => '#EEF5FF',
            'border_color' => '#e5e5e5',
            'border_width' => 1,
            'border_radius' => 4,
            'padding' => 20,
            'layout_style' => 'list',
            'orderby' => 'series_order',
            'order' => 'ASC',
            'gap_between_items' => 10,
            'highlight_current_post' => 1,
            'current_post_bg_color' => '#fff3cd',
            'current_post_border_color' => '#ffeaa7',
            'current_post_text_color' => '#2971B1',
            'show_post_titles' => 1,
            'post_title_color' => '#2971B1',
            'post_title_font_size' => 16,
            'show_post_excerpt' => 0,
            'excerpt_length' => 55,
            'excerpt_color' => '#383838',
            'show_post_thumbnail' => 1,
            'thumbnail_width' => 80,
            'thumbnail_height' => 80,
            'fallback_featured_image' => '',
            'show_post_author' => 0,
            'show_post_date' => 0,
            'item_padding' => 15,
            'item_border_width' => 1,
            'item_border_color' => '#e5e5e5',
            'post_list_background_color' => '#ffffff',
        ];
    }

    /**
     * Return post list box default tab
     *
     * @return string
     */
    public static function default_tab() {
        return apply_filters('pps_post_list_box_editor_default_tab', 'box');
    }
}
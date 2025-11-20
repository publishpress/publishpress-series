<?php
/**
 * Utilities for Post Navigation
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Utilities
{
    const POST_TYPE = 'pps_post_navigation';
    const META_PREFIX = 'pps_post_navigation_';

    /**
     * Get module path
     */
    public static function get_module_path($file = '')
    {
        $base = __DIR__ . '/../';
        return $file ? $base . $file : $base;
    }

    /**
     * Get module URL
     */
    public static function get_module_url($file = '')
    {
        $base = plugins_url('/', __FILE__) . '../';
        return $file ? $base . $file : $base;
    }

    /**
     * Get default navigation data
     */
    public static function get_default_post_navigation_data($post_id = null)
    {
        
        return [
            'previous_link_type' => 'custom',
            'previous_label' => __('Previous', 'publishpress-series'),
            'previous_show_featured_image' => 0,
            'previous_image_position' => 'left',
            'previous_image_width' => 80,
            'previous_image_height' => 80,
            'previous_show_arrow' => 0,
            'previous_arrow_type' => 'chevron_left',
            'previous_arrow_position' => 'left',
            'previous_arrow_size' => 16,
            'previous_custom_arrow_image' => 0,
            'next_link_type' => 'custom',
            'next_label' => __('Next', 'publishpress-series'),
            'next_show_featured_image' => 0,
            'next_image_position' => 'left',
            'next_image_width' => 80,
            'next_image_height' => 80,
            'next_show_arrow' => 0,
            'next_arrow_type' => 'chevron_right',
            'next_arrow_position' => 'right',
            'next_arrow_size' => 16,
            'next_custom_arrow_image' => 0,
            'first_link_type' => 'none',
            'first_label' => __('Series Home', 'publishpress-series'),
            'first_link_position' => 'right',
            'first_show_featured_image' => 0,
            'first_image_position' => 'left',
            'first_image_width' => 80,
            'first_image_height' => 80,
            'include_series_title' => 0,
            'series_title_alignment' => 'center',
            'series_title_color' => '#2971B1',
            'separator_text' => '|',
            'hide_when_single_post' => 1,
            'container_background_color' => 'transparent',
            'variant' => 'text-only',
            'arrows_style' => 'unicode',
            'link_color' => '#0073aa',
            'link_background_color' => 'transparent',
            'border_color' => '#dddddd',
            'border_width' => 1,
            'border_radius' => 4,
            'container_border_color' => '#dddddd',
            'container_border_width' => 0,
            'container_border_radius' => 0,
            'container_padding' => 20,
            'padding' => 8,
            'margin' => 0,
            'gap_between_links' => 10,
        ];
    }

    /**
     * Get navigation settings
     */
    public static function get_post_navigation_settings($post_id, $use_default = false)
    {
        $defaults = self::get_default_post_navigation_data($post_id);
        
        if ($use_default) {
            return $defaults;
        }

        $meta = get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true);
        
        if (empty($meta)) {
            return $defaults;
        }

        $settings = (array) $meta;
        return array_merge($defaults, $settings);
    }

    /**
     * Check if defaults have been created
     */
    public static function defaults_created()
    {
        return (bool) get_option('pps_post_navigation_defaults_created', false);
    }

    /**
     * Set defaults marker
     */
    public static function set_defaults_marker($data)
    {
        update_option('pps_post_navigation_defaults_created', true);
        if (isset($data['default_id'])) {
            update_option('pps_post_navigation_default_id', $data['default_id']);
        }
    }

    /**
     * Ensure default selection
     */
    public static function ensure_default_selection($default_id)
    {
        $options = get_option('org_series_options', []);
        if (! isset($options['series_post_navigation_selection']) || empty($options['series_post_navigation_selection'])) {
            $options['series_post_navigation_selection'] = $default_id;
            update_option('org_series_options', $options);
        }
    }

    /**
     * Get default post navigation ID
     */
    public static function get_default_post_navigation_id()
    {
        return (int) get_option('pps_post_navigation_default_id', 0);
    }

    /**
     * Ensure sample series term exists
     */
    public static function ensure_sample_series_term()
    {
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
        $terms = get_terms([
            'taxonomy' => $taxonomy_slug,
            'hide_empty' => false,
            'number' => 1,
        ]);

        if (! empty($terms) && ! is_wp_error($terms)) {
            return $terms[0];
        }

        // Create a sample series if none exist
        $result = wp_insert_term(
            __('Sample Series', 'publishpress-series'),
            $taxonomy_slug,
            ['description' => __('Sample series for preview purposes', 'publishpress-series')]
        );

        if (is_wp_error($result)) {
            return false;
        }

        return get_term($result['term_id'], $taxonomy_slug);
    }

    /**
     * Get sample series posts
     */
    public static function get_sample_series_posts($series_term_id)
    {
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
        $posts = get_posts([
            'post_type' => 'post',
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy_slug,
                    'field' => 'term_id',
                    'terms' => $series_term_id,
                ],
            ],
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        return ! empty($posts) ? $posts : [];
    }
}

<?php
/**
 * Utility helpers for Series Post Details module
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Details_Utilities
{
    /**
     * Module slug
     */
    const MODULE_SLUG = 'post-details';

    /**
     * Option key for default creations
     */
    const DEFAULTS_OPTION = 'pps_series_post_details_defaults_created';

    /**
     * Prefix for stored meta
     */
    const META_PREFIX = 'pps_series_post_details_';

    /**
     * CPT name
     */
    const POST_TYPE = 'pps_post_details';

    /**
     * Get module path
     */
    public static function get_module_path($path = '')
    {
        $base = trailingslashit(SERIES_PATH . 'addons/' . self::MODULE_SLUG);
        return $path ? $base . ltrim($path, '/') : $base;
    }

    /**
     * Get module URL
     */
    public static function get_module_url($file)
    {
        return plugins_url('', $file);
    }

    /**
     * Retrieve stored meta data and merge with defaults
     */
    public static function get_post_details_settings($post_id, $use_default = false)
    {
        $defaults = self::get_default_series_post_details_data($post_id);
        if ($use_default) {
            $data = $defaults;
        } else {
            $meta = get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true);
            $data = is_array($meta) ? array_merge($defaults, $meta) : $defaults;
        }

        $data['post_id'] = $post_id;

        return apply_filters('pps_series_post_details_settings', $data, $post_id, $use_default);
    }

    /**
     * Default data for new Series Post Details
     */
    public static function get_default_series_post_details_data($post_id = 0)
    {
        $settings = self::get_legacy_settings();

        return [
            // Content display settings
            'show_part_number'           => 1,
            'text_before'                => __('This entry is', 'organize-series'),
            'text_after'                 => __('in the series', 'organize-series'),

            // Layout settings
            'metabox_position'           => isset($settings['series_metabox_position']) ? $settings['series_metabox_position'] : 'top',
            'limit_to_single'            => isset($settings['limit_series_meta_to_single']) ? $settings['limit_series_meta_to_single'] : 0,
            'padding'                    => 20,
            'margin'                     => 0,
            'border_width'               => 1,
            'border_radius'              => 6,

            // Styling settings
            'background_color'           => '#eef5ff',
            'text_color'                 => '#383838',
            'link_color'                 => '#2971B1',
            'border_color'               => '#c7d7f5',
            'text_size'                  => 16,

            // Legacy template fields - empty by default for new post details
            // Only populated from legacy settings for backward compatibility when explicitly migrating
            'meta_template'              => '',
            'meta_excerpt_template'      => '',
        ];
    }

    /**
     * Retrieve settings array for the selected layout in options.
     *
     * @param array $options Plugin options array.
     *
     * @return array|null
     */
    public static function get_selected_layout_settings($options)
    {
        if (! is_array($options) || empty($options['series_post_details_selection'])) {
            return null;
        }

        $layout_id = (int) $options['series_post_details_selection'];
        if ($layout_id <= 0) {
            return null;
        }

        return self::get_post_details_settings($layout_id);
    }

    /**
     * Ensure the plugin options point to a valid default Series Post Details.
     *
     * @param int|null $default_id Optional default layout ID to enforce.
     */
    public static function ensure_default_selection($default_id = null)
    {
        if (null === $default_id) {
            $default_id = self::get_default_series_post_details_id();
        }

        if (! $default_id) {
            return;
        }

        $options = get_option('org_series_options');
        if (! is_array($options)) {
            $options = [];
        }

        $current = isset($options['series_post_details_selection']) ? (int) $options['series_post_details_selection'] : 0;

        if ($current === $default_id) {
            return;
        }

        $post = get_post($default_id);
        if (! $post || $post->post_type !== self::POST_TYPE || 'publish' !== $post->post_status) {
            return;
        }

        if ($current > 0) {
            $existing_post = get_post($current);
            if ($existing_post && $existing_post->post_type === self::POST_TYPE && 'publish' === $existing_post->post_status) {
                return;
            }
        }

        $options['series_post_details_selection'] = $default_id;
        update_option('org_series_options', $options, false);
    }

    /**
     * Get settings stored in plugin options
     */
    public static function get_legacy_settings()
    {
        $options = get_option('org_series_options');
        return is_array($options) ? $options : [];
    }

    /**
     * Returns default Series Post Details post ID
     */
    public static function get_default_series_post_details_id()
    {
        $defaults = get_option(self::DEFAULTS_OPTION);
        if (is_array($defaults) && isset($defaults['default_id']) && $defaults['default_id']) {
            return (int) $defaults['default_id'];
        }

        $post = get_page_by_path('default-series-post-details', OBJECT, self::POST_TYPE);
        return $post ? (int) $post->ID : 0;
    }

    /**
     * Record default creation details
     */
    public static function set_defaults_marker(array $data)
    {
        update_option(self::DEFAULTS_OPTION, $data, false);
    }

    /**
     * Determine whether defaults already created
     */
    public static function defaults_created()
    {
        $option = get_option(self::DEFAULTS_OPTION, []);
        return ! empty($option);
    }

    /**
     * Create sample series term for previews when none exist
     */
    public static function ensure_sample_series_term()
    {
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
        
        $terms = get_terms([
            'taxonomy'   => $taxonomy_slug,
            'number'     => 1,
            'hide_empty' => false,
        ]);

        if (! empty($terms) && ! is_wp_error($terms)) {
            return $terms[0];
        }

        $result = wp_insert_term(__('Sample Series', 'organize-series'), $taxonomy_slug);
        if (is_wp_error($result)) {
            return null;
        }

        return get_term($result['term_id'], $taxonomy_slug);
    }

    /**
     * Retrieve a collection of preview posts for a given series
     */
    public static function get_sample_series_posts($series_id)
    {
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
        
        $posts = get_posts([
            'post_type'      => 'post',
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy_slug,
                    'field'    => 'term_id',
                    'terms'    => $series_id,
                ],
            ],
            'posts_per_page' => 3,
        ]);

        if (! empty($posts)) {
            return $posts;
        }

        $sample_posts = [];
        for ($i = 1; $i <= 3; $i++) {
            $post            = new stdClass();
            $post->ID        = 'sample_' . $i;
            $post->post_title = sprintf(__('Sample Series Post %d', 'organize-series'), $i);
            $post->post_content = __('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus lacinia odio vitae vestibulum vestibulum.', 'organize-series');
            $post->post_excerpt = __('Sample excerpt preview content for the series meta box.', 'organize-series');
            $post->post_author = get_current_user_id() ?: 1;
            $post->post_date   = current_time('mysql');
            $sample_posts[] = $post;
        }

        return $sample_posts;
    }
}

<?php
/**
 * AJAX handlers for Series Post Navigation editor
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Ajax
{
    /**
     * Boot hooks
     */
    public static function init()
    {
        add_action('wp_ajax_pps_update_series_post_navigation_preview', [__CLASS__, 'update_preview']);
        add_action('wp_ajax_pps_export_series_post_navigation', [__CLASS__, 'export_layout']);
        add_action('wp_ajax_pps_import_series_post_navigation', [__CLASS__, 'import_layout']);
        add_action('wp_ajax_pps_reset_series_post_navigation', [__CLASS__, 'reset_layout']);
    }

    /**
     * Build preview output
     */
    public static function update_preview()
    {
        check_ajax_referer('series-post-navigation-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'publishpress-series')]);
        }

        $form_data = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        $parsed_settings = [];

        if ($form_data) {
            parse_str($form_data, $parsed_settings);
        }

        // Get default settings as base
        $base_settings = PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings($post_id);

        // Get all field definitions to know which fields to process
        $post = get_post($post_id);
        $fields = apply_filters('pps_series_post_navigation_fields', PPS_Series_Post_Navigation_Fields::get_fields($post), $post);
        
        // Merge settings, handling checkboxes properly
        $settings = $base_settings;
        foreach ($fields as $key => $args) {
            // Skip category separators
            if (isset($args['type']) && $args['type'] === 'category_separator') {
                continue;
            }
            
            // Handle checkboxes - if not in parsed settings, it's unchecked
            if (isset($args['type']) && $args['type'] === 'checkbox') {
                $settings[$key] = isset($parsed_settings[$key]) ? 1 : 0;
            } elseif (isset($parsed_settings[$key])) {
                $settings[$key] = $parsed_settings[$key];
            }
        }

        $series_id = isset($_POST['series_id']) ? (int) $_POST['series_id'] : 0;
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');

        $series_term = null;
        if ($series_id) {
            $series_term = get_term($series_id, $taxonomy_slug);
            if ($series_term instanceof WP_Error) {
                $series_term = null;
            }
        }

        if (! $series_term) {
            $series_term = PPS_Series_Post_Navigation_Utilities::ensure_sample_series_term();
        }

        if (! $series_term) {
            wp_send_json_error(['message' => __('No series available to preview.', 'publishpress-series')]);
        }

        $posts = PPS_Series_Post_Navigation_Utilities::get_sample_series_posts($series_term->term_id);
        $total_posts = count($posts);

        if (0 === $total_posts) {
            wp_send_json_error(['message' => __('No posts found in series to preview.', 'publishpress-series')]);
        }

        $current_index = $total_posts > 1 ? 1 : 0;
        $current_post  = isset($posts[$current_index]) ? $posts[$current_index] : null;

        if (! $current_post) {
            wp_send_json_error(['message' => __('No posts found in series to preview.', 'publishpress-series')]);
        }

        $previous_post = ($current_index + 1 < $total_posts) ? $posts[$current_index + 1] : null;
        $next_post     = ($current_index - 1) >= 0 ? $posts[$current_index - 1] : null;
        $first_post    = $posts[$total_posts - 1];

        ob_start();
        echo PostNavigationRenderer::render_from_settings(
            $settings,
            [
                'series_term' => $series_term,
                'post'        => $current_post,
                'total_posts' => $total_posts,
                'preview_posts' => [
                    'current'  => $current_post,
                    'previous' => $previous_post,
                    'next'     => $next_post,
                    'first'    => $first_post,
                ],
                'context'     => 'preview',
            ]
        );
        PostNavigationRenderer::output_dynamic_css();
        $preview = ob_get_clean();

        wp_send_json_success(['preview' => $preview]);
    }

    /**
     * Export settings JSON
     */
    public static function export_layout()
    {
        check_ajax_referer('series-post-navigation-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'publishpress-series')]);
        }

        $settings = PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings($post_id);
        $post      = get_post($post_id);

        wp_send_json_success([
            'settings' => $settings,
            'slug'     => $post ? $post->post_name : '',
        ]);
    }

    /**
     * Import settings
     */
    public static function import_layout()
    {
        check_ajax_referer('series-post-navigation-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];

        if (! $post_id || empty($settings)) {
            wp_send_json_error(['message' => __('Invalid import data.', 'publishpress-series')]);
        }

        update_post_meta($post_id, PPS_Series_Post_Navigation_Utilities::META_PREFIX . 'layout_meta_value', $settings);

        wp_send_json_success(['message' => __('Settings imported successfully.', 'publishpress-series')]);
    }

    /**
     * Reset layout to defaults
     */
    public static function reset_layout()
    {
        check_ajax_referer('series-post-navigation-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'publishpress-series')]);
        }

        $defaults = PPS_Series_Post_Navigation_Utilities::get_default_post_navigation_data($post_id);
        update_post_meta($post_id, PPS_Series_Post_Navigation_Utilities::META_PREFIX . 'layout_meta_value', $defaults);

        wp_send_json_success(['message' => __('Settings reset to defaults.', 'publishpress-series')]);
    }
}

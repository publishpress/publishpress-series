<?php
/**
 * AJAX handlers for Series Post Details editor
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Details_Ajax
{
    /**
     * Boot hooks
     */
    public static function init()
    {
        add_action('wp_ajax_pps_update_series_post_details_preview', [__CLASS__, 'update_preview']);
        add_action('wp_ajax_pps_export_series_post_details', [__CLASS__, 'export_layout']);
        add_action('wp_ajax_pps_import_series_post_details', [__CLASS__, 'import_layout']);
        add_action('wp_ajax_pps_reset_series_post_details', [__CLASS__, 'reset_layout']);
    }

    /**
     * Build preview output
     */
    public static function update_preview()
    {
        check_ajax_referer('series-post-details-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'organize-series')]);
        }

        $form_data = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : '';
        $parsed_settings = [];

        if ($form_data) {
            parse_str($form_data, $parsed_settings);
        }

        // Get default settings as base
        $base_settings = PPS_Series_Post_Details_Utilities::get_post_details_settings($post_id);

        // Get all field definitions to know which fields to process
        $post = get_post($post_id);
        $fields = apply_filters('pps_series_post_details_fields', PPS_Series_Post_Details_Fields::get_fields($post), $post);
        
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

        $series_term = PPS_Series_Post_Details_Utilities::ensure_sample_series_term();
        if (! $series_term) {
            wp_send_json_error(['message' => __('No series available to preview.', 'organize-series')]);
        }

        $posts = PPS_Series_Post_Details_Utilities::get_sample_series_posts($series_term->term_id);
        $current_post = $posts ? $posts[0] : null;

        ob_start();
        echo SeriesPostDetailsRenderer::render_from_settings(
            $settings,
            [
                'series_term' => $series_term,
                'post'        => $current_post,
                'total_posts' => count($posts),
                'series_part' => 1,
                'context'     => 'preview',
            ]
        );
        SeriesPostDetailsRenderer::output_dynamic_css();
        $preview = ob_get_clean();

        wp_send_json_success(['preview' => $preview]);
    }

    /**
     * Export settings JSON
     */
    public static function export_layout()
    {
        check_ajax_referer('series-post-details-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'organize-series')]);
        }

        $settings = PPS_Series_Post_Details_Utilities::get_post_details_settings($post_id);
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
        check_ajax_referer('series-post-details-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? $_POST['settings'] : [];

        if (! $post_id || empty($settings)) {
            wp_send_json_error(['message' => __('Invalid import data.', 'organize-series')]);
        }

        update_post_meta($post_id, PPS_Series_Post_Details_Utilities::META_PREFIX . 'layout_meta_value', $settings);

        wp_send_json_success(['message' => __('Settings imported successfully.', 'organize-series')]);
    }

    /**
     * Reset layout to defaults
     */
    public static function reset_layout()
    {
        check_ajax_referer('series-post-details-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
        if (! $post_id) {
            wp_send_json_error(['message' => __('Invalid post ID.', 'organize-series')]);
        }

        $defaults = PPS_Series_Post_Details_Utilities::get_default_series_post_details_data($post_id);
        update_post_meta($post_id, PPS_Series_Post_Details_Utilities::META_PREFIX . 'layout_meta_value', $defaults);

        wp_send_json_success(['message' => __('Settings reset to defaults.', 'organize-series')]);
    }
}

<?php
/**
 * AJAX Handlers for Post List Box
 */

class PPS_Post_List_Box_AJAX {

    const POST_TYPE_BOXES = 'pps_post_list_box';
    const META_PREFIX = 'pps_post_list_box_';

    /**
     * Initialize AJAX handlers
     */
    public static function init() {
        // AJAX handlers for preview
        add_action('wp_ajax_pps_update_post_list_box_preview', [__CLASS__, 'ajax_update_preview']);
        add_action('wp_ajax_pps_export_post_list_box', [__CLASS__, 'ajax_export_post_list_box']);
        add_action('wp_ajax_pps_import_post_list_box', [__CLASS__, 'ajax_import_post_list_box']);
        add_action('wp_ajax_pps_reset_post_list_box', [__CLASS__, 'ajax_reset_post_list_box']);
        add_action('wp_ajax_pps_quick_save_post_list_box', [__CLASS__, 'ajax_quick_save_post_list_box']);
    }

    /**
     * AJAX handler for updating preview
     */
    public static function ajax_update_preview()
    {
        check_ajax_referer('post-list-box-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $form_data = isset($_POST['settings']) ? $_POST['settings'] : '';
        $series_id = isset($_POST['series_id']) ? intval($_POST['series_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        // Parse the form data into settings array
        $settings = [];
        if (!empty($form_data)) {
            parse_str($form_data, $settings);
        } else {
            // Use saved settings when form_data is empty
            $settings = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post_id);
            if (!is_array($settings)) {
                $settings = [];
            }
        }

        // If no series_id provided, get a sample series for preview
        if (!$series_id) {
            $sample_series = get_terms([
                'taxonomy' => 'series',
                'number' => 1,
                'hide_empty' => false,
            ]);
            $series_id = !empty($sample_series) && !is_wp_error($sample_series) ? $sample_series[0]->term_id : 0;
        }

        if (!$series_id) {
            wp_send_json_error(['message' => 'No series found']);
        }

        // Get sample posts
        $sample_posts = PPS_Post_List_Box_Preview::get_sample_series_posts($series_id);

        if (empty($sample_posts)) {
            wp_send_json_error(['message' => 'No posts found']);
        }

        // Generate preview HTML - just the content, no wrapper divs
        $preview_html = PPS_Post_List_Box_Preview::render_preview_content($settings, $sample_posts);

        wp_send_json_success(['preview' => $preview_html]);
    }

    /**
     * AJAX handler for exporting settings
     */
    public static function ajax_export_post_list_box()
    {
        check_ajax_referer('post-list-box-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        $settings = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post_id);
        $post = get_post($post_id);

        wp_send_json_success([
            'settings' => $settings,
            'slug' => $post->post_name
        ]);
    }

    /**
     * AJAX handler for importing settings
     */
    public static function ajax_import_post_list_box()
    {
        check_ajax_referer('post-list-box-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $settings = isset($_POST['settings']) ? $_POST['settings'] : [];

        if (!$post_id || empty($settings)) {
            wp_send_json_error(['message' => 'Invalid data']);
        }

        // Update the settings
        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $settings);

        wp_send_json_success(['message' => 'Settings imported successfully']);
    }

    /**
     * AJAX handler for resetting settings
     */
    public static function ajax_reset_post_list_box()
    {
        check_ajax_referer('post-list-box-nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;

        if (!$post_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        // Reset to defaults
        $default_settings = PPS_Post_List_Box_Fields::get_default_post_list_box_data();
        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $default_settings);

        wp_send_json_success(['message' => 'Settings reset to defaults']);
    }

    /**
     * AJAX handler for quick save
     */
    public static function ajax_quick_save_post_list_box()
    {
        check_ajax_referer('post-list-box-nonce', 'nonce');

        // This would handle the actual save via the main save functionality
        // For now, just return success
        wp_send_json_success(['message' => 'Settings saved']);
    }
}
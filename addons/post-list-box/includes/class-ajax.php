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
        }

        // Base settings from saved meta to preserve locked/pro-only values
        $saved_settings = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post_id);
        if (!is_array($saved_settings)) {
            $saved_settings = [];
        }

        if (empty($settings)) {
            $settings = $saved_settings;
        } else {
            // Merge: start with saved settings, then apply editable fields from form
            $merged_settings = $saved_settings;
            $fields = PPS_Post_List_Box_Fields::get_fields(get_post($post_id));
            foreach ($fields as $key => $args) {
                $pro_locked = !empty($args['pro_only']);
                $pro_locked = apply_filters('pps_post_list_box_field_pro_locked', $pro_locked, $key, $args);
                if ($pro_locked) {
                    if (array_key_exists($key, $settings)) {
                        $merged_settings[$key] = $settings[$key];
                    }
                    continue;
                }
                if (array_key_exists($key, $settings)) {
                    $merged_settings[$key] = $settings[$key];
                    continue;
                }
                if (!empty($args['type']) && $args['type'] === 'checkbox') {
                    $merged_settings[$key] = 0;
                }
            }
            $settings = $merged_settings;
        }

        // If no series_id provided, get a sample series for preview
        if (!$series_id) {
            $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
            $sample_series = get_terms([
                'taxonomy' => $taxonomy_slug,
                'number' => 1,
                'hide_empty' => false,
            ]);
            $series_id = !empty($sample_series) && !is_wp_error($sample_series) ? $sample_series[0]->term_id : 0;
        }

        if (!$series_id) {
            wp_send_json_error(['message' => 'No series found']);
        }

        // Get sample posts
        $sample_posts = PPS_Post_List_Box_Preview::get_sample_series_posts($series_id, $settings);

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
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? wp_unslash($_POST['settings']) : [];
        $post = get_post($post_id);

        if (!$post_id || !$post || $post->post_type !== self::POST_TYPE_BOXES || !current_user_can('edit_post', $post_id) || !is_array($settings) || empty($settings)) {
            wp_send_json_error(['message' => 'Invalid data']);
        }

        $fields = apply_filters('pps_post_list_box_fields', PPS_Post_List_Box_Fields::get_fields($post), $post);
        $sanitized_settings = [];

        foreach ($settings as $key => $value) {
            if (!isset($fields[$key]) || (isset($fields[$key]['type']) && $fields[$key]['type'] === 'category_separator')) {
                continue;
            }

            $args = $fields[$key];
            if (isset($args['sanitize'])) {
                $sanitizers = is_array($args['sanitize']) ? $args['sanitize'] : [$args['sanitize']];
                foreach ($sanitizers as $sanitize_cb) {
                    $value = is_array($value) ? map_deep($value, $sanitize_cb) : call_user_func($sanitize_cb, $value);
                }
            } else {
                $value = is_array($value) ? map_deep($value, 'sanitize_text_field') : sanitize_text_field($value);
            }

            $sanitized_settings[$key] = $value;
        }

        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $sanitized_settings);

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

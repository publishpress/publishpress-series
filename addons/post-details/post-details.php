<?php
/**
 * Series Post Details module bootstrap
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-utilities.php';
require_once __DIR__ . '/includes/class-post-type.php';
require_once __DIR__ . '/includes/class-fields.php';
require_once __DIR__ . '/includes/class-preview.php';
require_once __DIR__ . '/includes/class-admin-ui.php';
require_once __DIR__ . '/includes/class-ajax.php';

if (! defined('PPS_SERIES_POST_DETAILS_NONCE')) {
    define('PPS_SERIES_POST_DETAILS_NONCE', 'series-post-details-editor');
}

if (! defined('PPS_SERIES_POST_DETAILS_NONCE_FIELD')) {
    define('PPS_SERIES_POST_DETAILS_NONCE_FIELD', 'series-post-details-editor-nonce');
}

class PPS_Series_Post_Details
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init module components
     */
    public function init()
    {
        PPS_Series_Post_Details_Post_Type::init();
        PPS_Series_Post_Details_Fields::init();
        PPS_Series_Post_Details_Admin_UI::init();
        PPS_Series_Post_Details_Ajax::init();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('save_post_' . PPS_Series_Post_Details_Utilities::POST_TYPE, [$this, 'save_post_details_data']);
        add_action('init', [$this, 'create_default_meta_boxes'], 6);
    }

    /**
     * Register admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        global $typenow, $pagenow, $post;

        if ($typenow !== PPS_Series_Post_Details_Utilities::POST_TYPE) {
            return;
        }

        if (! in_array($pagenow, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_code_editor(['type' => 'text/css']);

        $assets_base = plugins_url('assets/', __FILE__);

        $js_file = __DIR__ . '/assets/js/series-post-details-editor.js';
        $js_version = ORG_SERIES_VERSION . '-' . filemtime($js_file);
        
        wp_enqueue_script(
            'pps-series-post-details-editor',
            $assets_base . 'js/series-post-details-editor.js',
            ['jquery', 'wp-color-picker', 'underscore', 'code-editor'],
            $js_version,
            true
        );

        wp_localize_script(
            'pps-series-post-details-editor',
            'ppsSeriesPostDetailsEditor',
            [
                'post_id' => $post ? $post->ID : 0,
                'nonce' => wp_create_nonce('series-post-details-nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
            ]
        );

        $css_file = __DIR__ . '/assets/css/series-post-details-editor.css';
        $css_version = ORG_SERIES_VERSION . '-' . filemtime($css_file);
        
        wp_enqueue_style(
            'pps-series-post-details-editor',
            $assets_base . 'css/series-post-details-editor.css',
            [],
            $css_version
        );
    }

    /**
     * Persist meta box settings
     */
    public function save_post_details_data($post_id)
    {
        if (empty($_POST[PPS_SERIES_POST_DETAILS_NONCE_FIELD]) || ! wp_verify_nonce(sanitize_key($_POST[PPS_SERIES_POST_DETAILS_NONCE_FIELD]), PPS_SERIES_POST_DETAILS_NONCE)) {
            return;
        }

        $post = get_post($post_id);
        $fields = apply_filters('pps_series_post_details_fields', PPS_Series_Post_Details_Fields::get_fields($post), $post);
        $excluded = ['template_action', 'import_action'];
        $excluded_types = ['category_separator'];
        $meta = [];

        foreach ($fields as $key => $args) {
            // Skip excluded fields and separator types
            if (in_array($key, $excluded, true)) {
                continue;
            }
            
            if (isset($args['type']) && in_array($args['type'], $excluded_types, true)) {
                continue;
            }

            // Handle checkboxes - they won't be in $_POST if unchecked
            if (isset($args['type']) && $args['type'] === 'checkbox') {
                $value = isset($_POST[$key]) ? 1 : 0;
            } elseif (! isset($_POST[$key])) {
                continue;
            } else {
                $value = $_POST[$key];
            }

            if (isset($args['sanitize'])) {
                if (is_array($args['sanitize'])) {
                    foreach ($args['sanitize'] as $sanitize_cb) {
                        $value = is_array($value) ? map_deep($value, $sanitize_cb) : call_user_func($sanitize_cb, $value);
                    }
                } else {
                    $sanitize_cb = $args['sanitize'];
                    $value = is_array($value) ? $value : call_user_func($sanitize_cb, $value);
                }
            } else {
                $value = is_array($value) ? $value : sanitize_text_field($value);
            }

            $meta[$key] = $value;
        }

        update_post_meta($post_id, PPS_Series_Post_Details_Utilities::META_PREFIX . 'layout_meta_value', $meta);
    }

    /**
     * Create default meta boxes
     */
    public function create_default_meta_boxes()
    {
        if (PPS_Series_Post_Details_Utilities::defaults_created()) {
            return;
        }

        $defaults = [
            [
                'title' => __('Series Post Details #1', 'organize-series'),
                'slug' => 'series-post-details-1',
                'settings' => $this->get_post_details_1_settings(),
            ],
            [
                'title' => __('Series Post Details #2', 'organize-series'),
                'slug' => 'series-post-details-2',
                'settings' => $this->get_post_details_2_settings(),
            ],
            [
                'title' => __('Series Post Details #3', 'organize-series'),
                'slug' => 'series-post-details-3',
                'settings' => $this->get_post_details_3_settings(),
            ],
        ];

        $created_ids = [];

        foreach ($defaults as $data) {
            $existing = get_page_by_path($data['slug'], OBJECT, PPS_Series_Post_Details_Utilities::POST_TYPE);
            if ($existing) {
                $created_ids[] = $existing->ID;
                continue;
            }

            $post_id = wp_insert_post([
                'post_title'   => $data['title'],
                'post_name'    => $data['slug'],
                'post_type'    => PPS_Series_Post_Details_Utilities::POST_TYPE,
                'post_status'  => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            update_post_meta($post_id, PPS_Series_Post_Details_Utilities::META_PREFIX . 'layout_meta_value', $data['settings']);
            $created_ids[] = $post_id;
        }

        if (! empty($created_ids)) {
            PPS_Series_Post_Details_Utilities::set_defaults_marker([
                'default_id' => $created_ids[0],
                'created_ids' => $created_ids,
            ]);

            PPS_Series_Post_Details_Utilities::ensure_default_selection($created_ids[0]);
        }
    }

    /**
     * Post Details #1 - Light Blue (Default style)
     */
    private function get_post_details_1_settings()
    {
        $settings = PPS_Series_Post_Details_Utilities::get_default_series_post_details_data();
        // Uses all defaults - light blue background
        return $settings;
    }

    /**
     * Post Details #2 - Minimal Gray
     */
    private function get_post_details_2_settings()
    {
        $settings = PPS_Series_Post_Details_Utilities::get_default_series_post_details_data();
        $settings['show_part_number'] = 0; 
        $settings['background_color'] = '#f7f7f7';
        $settings['text_color'] = '#333333';
        $settings['link_color'] = '#0073aa';
        $settings['border_color'] = '#dddddd';
        $settings['border_width'] = 1;
        $settings['border_radius'] = 4;
        $settings['padding'] = 15;
        $settings['text_size'] = 14;
        return $settings;
    }

    /**
     * Post Details #3 - Bold Dark
     */
    private function get_post_details_3_settings()
    {
        $settings = PPS_Series_Post_Details_Utilities::get_default_series_post_details_data();
        $settings['text_before'] = 'This is';
        $settings['text_after'] = 'of';
        $settings['metabox_position'] = 'top';
        $settings['background_color'] = '#2c3e50';
        $settings['text_color'] = '#ecf0f1';
        $settings['link_color'] = '#3498db';
        $settings['border_color'] = '#34495e';
        $settings['border_width'] = 2;
        $settings['border_radius'] = 8;
        $settings['padding'] = 25;
        $settings['margin'] = 20;
        $settings['text_size'] = 15;
        return $settings;
    }
}

new PPS_Series_Post_Details();

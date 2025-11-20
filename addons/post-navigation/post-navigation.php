<?php
/**
 * Post Navigation module bootstrap
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

if (! defined('PPS_SERIES_POST_NAVIGATION_NONCE')) {
    define('PPS_SERIES_POST_NAVIGATION_NONCE', 'series-post-navigation-editor');
}

if (! defined('PPS_SERIES_POST_NAVIGATION_NONCE_FIELD')) {
    define('PPS_SERIES_POST_NAVIGATION_NONCE_FIELD', 'series-post-navigation-editor-nonce');
}

class PPS_Series_Post_Navigation
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
        PPS_Series_Post_Navigation_Post_Type::init();
        PPS_Series_Post_Navigation_Fields::init();
        PPS_Series_Post_Navigation_Admin_UI::init();
        PPS_Series_Post_Navigation_Ajax::init();

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('save_post_' . PPS_Series_Post_Navigation_Utilities::POST_TYPE, [$this, 'save_post_navigation_data']);
        add_action('init', [$this, 'create_default_navigation_layouts'], 6);
    }

    /**
     * Register admin assets
     */
    public function enqueue_admin_assets($hook)
    {
        global $typenow, $pagenow, $post;

        if ($typenow !== PPS_Series_Post_Navigation_Utilities::POST_TYPE) {
            return;
        }

        if (! in_array($pagenow, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_code_editor(['type' => 'text/css']);

        $assets_base = plugins_url('assets/', __FILE__);

        wp_enqueue_script(
            'pps-series-post-navigation-editor',
            $assets_base . 'js/series-post-navigation-editor.js',
            ['jquery', 'wp-color-picker', 'underscore', 'code-editor'],
            ORG_SERIES_VERSION,
            true
        );

        wp_localize_script(
            'pps-series-post-navigation-editor',
            'ppsSeriesPostNavigationEditor',
            [
                'post_id' => $post ? $post->ID : 0,
                'nonce' => wp_create_nonce('series-post-navigation-nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'i18n' => [
                    'loading_preview' => __('Loading preview...', 'publishpress-series-pro'),
                    'error_loading_preview' => __('Error updating preview.', 'publishpress-series-pro'),
                ],
            ]
        );

        wp_enqueue_style(
            'pps-series-post-navigation-editor',
            $assets_base . 'css/post-navigation-editor.css',
            [],
            ORG_SERIES_VERSION
        );
    }

    /**
     * Persist navigation settings
     */
    public function save_post_navigation_data($post_id)
    {
        if (empty($_POST[PPS_SERIES_POST_NAVIGATION_NONCE_FIELD]) || ! wp_verify_nonce(sanitize_key($_POST[PPS_SERIES_POST_NAVIGATION_NONCE_FIELD]), PPS_SERIES_POST_NAVIGATION_NONCE)) {
            return;
        }

        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        $fields = apply_filters('pps_series_post_navigation_fields', PPS_Series_Post_Navigation_Fields::get_fields($post), $post);
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

        update_post_meta($post_id, PPS_Series_Post_Navigation_Utilities::META_PREFIX . 'layout_meta_value', $meta);
    }

    /**
     * Create default navigation layouts
     */
    public function create_default_navigation_layouts()
    {
        if (PPS_Series_Post_Navigation_Utilities::defaults_created()) {
            return;
        }

        $defaults = [
            [
                'title' => __('Simple Navigation', 'publishpress-series'),
                'slug' => 'simple-navigation',
                'settings' => $this->get_simple_navigation_settings(),
            ],
            [
                'title' => __('Button Navigation', 'publishpress-series'),
                'slug' => 'button-navigation',
                'settings' => $this->get_button_navigation_settings(),
            ],
            [
                'title' => __('Image Navigation', 'publishpress-series'),
                'slug' => 'image-navigation',
                'settings' => $this->get_image_navigation_settings(),
            ],
        ];

        $created_ids = [];

        foreach ($defaults as $data) {
            $existing = get_page_by_path($data['slug'], OBJECT, PPS_Series_Post_Navigation_Utilities::POST_TYPE);
            if ($existing) {
                $created_ids[] = $existing->ID;
                continue;
            }

            $post_id = wp_insert_post([
                'post_title'   => $data['title'],
                'post_name'    => $data['slug'],
                'post_type'    => PPS_Series_Post_Navigation_Utilities::POST_TYPE,
                'post_status'  => 'publish',
            ]);

            if (is_wp_error($post_id)) {
                continue;
            }

            update_post_meta($post_id, PPS_Series_Post_Navigation_Utilities::META_PREFIX . 'layout_meta_value', $data['settings']);
            $created_ids[] = $post_id;
        }

        if (! empty($created_ids)) {
            PPS_Series_Post_Navigation_Utilities::set_defaults_marker([
                'default_id' => $created_ids[0],
                'created_ids' => $created_ids,
            ]);

            PPS_Series_Post_Navigation_Utilities::ensure_default_selection($created_ids[0]);
        }
    }

    /**
     * Simple Navigation - Default style
     */
    private function get_simple_navigation_settings()
    {
        $settings = PPS_Series_Post_Navigation_Utilities::get_default_post_navigation_data();
        $settings['show_previous_link'] = 1;
        $settings['include_series_title'] = 1;
        $settings['series_title_alignment'] = 'center';
        $settings['separator_text'] = ' | ';
        $settings['previous_link_type'] = 'post_title';
        $settings['previous_show_arrow'] = 1;
        $settings['previous_arrow_type'] = 'arrow_left';
        $settings['next_link_type'] = 'post_title';
        $settings['next_show_arrow'] = 1;
        $settings['next_arrow_type'] = 'arrow_right';
        $settings['first_link_type'] = 'none';
        $settings['link_color'] = '#2971B1';
        $settings['alignment'] = 'justify';
        $settings['border_width'] = '0';
        $settings['container_border_width'] = '1';
        $settings['container_border_color'] = '#e5e5e5';
        $settings['container_background_color'] = '#EEF5FF';
        $settings['container_padding'] = '20';
        $settings['container_border_radius'] = '4';

        return $settings;
    }

    /**
     * Button Navigation
     */
    private function get_button_navigation_settings()
    {
        $settings = PPS_Series_Post_Navigation_Utilities::get_default_post_navigation_data();
        $settings['separator_text'] = '';
        $settings['previous_show_arrow'] = 1;
        $settings['previous_arrow_type'] = 'double_left';
        $settings['next_show_arrow'] = 1;
        $settings['next_arrow_type'] = 'double_right';
        $settings['link_color'] = '#ffffff';
        $settings['link_background_color'] = '#3f0047';
        $settings['alignment'] = 'justify';
        $settings['border_width'] = '0';
        $settings['border_radius'] = '50';
        $settings['padding'] = '10';
        $settings['container_border_width'] = '1';
        $settings['container_border_color'] = '#e5e5e5';
        $settings['container_background_color'] = '#f7f7f7';
        $settings['container_padding'] = '20';
        $settings['container_border_radius'] = '50';
        return $settings;
    }

    /**
     * Image Navigation
     */
    private function get_image_navigation_settings()
    {
        $settings = PPS_Series_Post_Navigation_Utilities::get_default_post_navigation_data();
        $settings['separator_text'] = '';
        $settings['previous_show_arrow'] = 1;
        $settings['previous_arrow_type'] = 'chevron_left';
        $settings['previous_link_type'] = 'post_title';
        $settings['previous_show_featured_image'] = 1;
        $settings['previous_image_position'] = 'right';
        $settings['previous_image_width'] = 50;
        $settings['previous_image_height'] = 50;
        $settings['next_show_arrow'] = 1;
        $settings['next_arrow_type'] = 'chevron_right';
        $settings['next_link_type'] = 'post_title';
        $settings['next_show_featured_image'] = 1;
        $settings['next_image_position'] = 'left';
        $settings['next_image_width'] = 50;
        $settings['next_image_height'] = 50;
        $settings['link_color'] = '#0a0a0a';
        $settings['alignment'] = 'justify';
        $settings['border_width'] = '2';
        $settings['padding'] = '10';
        $settings['container_border_width'] = '1';
        $settings['container_border_color'] = '#e5e5e5';
        $settings['container_background_color'] = '#ffffff';
        $settings['container_padding'] = '20';
        $settings['container_border_radius'] = '4';
        return $settings;
    }
}

new PPS_Series_Post_Navigation();

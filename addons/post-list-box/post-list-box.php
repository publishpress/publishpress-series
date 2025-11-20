<?php
/**
 * Post List Box Module for PublishPress Series
 * 
 */

// Include component files
require_once __DIR__ . '/includes/class-post-type.php';
require_once __DIR__ . '/includes/class-fields.php';
require_once __DIR__ . '/includes/class-preview.php';
require_once __DIR__ . '/includes/class-admin-ui.php';
require_once __DIR__ . '/includes/class-ajax.php';
require_once __DIR__ . '/includes/class-utilities.php';

/**
 * class PPS_Post_List_Box
 * Main class that coordinates all components
 */
class PPS_Post_List_Box
{
    const POST_TYPE_BOXES = 'pps_post_list_box';
    const META_PREFIX = 'pps_post_list_box_';

    public $module_name = 'post_list_box';

    /**
     * Instance of the module
     *
     * @var stdClass
     */
    public $module;
    public $module_url;

    /**
     * @var array
     */
    protected $customFields = null;

    /**
     * Construct the PPS_Post_List_Box class
     */
    public function __construct()
    {
        $this->module_url = PPS_Post_List_Box_Utilities::get_module_url(__FILE__);

        // Initialize all components
        $this->init();
    }

    /**
     * Initialize the module and all components
     */
    public function init()
    {
        // Initialize post type
        PPS_Post_List_Box_Post_Type::init();
        
        // Initialize admin UI
        PPS_Post_List_Box_Admin_UI::init();
        
        // Initialize AJAX handlers
        PPS_Post_List_Box_AJAX::init();

        // Add remaining hooks
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('save_post_' . self::POST_TYPE_BOXES, [$this, 'save_post_list_box_data']);

        // Create default Post List Boxes if they don't exist
        add_action('init', [$this, 'create_default_post_list_boxes'], 10);
        
        // Set default post list box selection in settings
        add_filter('org_series_settings', [$this, 'set_default_post_list_box_selection']);
    }

    /**
     * Create default Post List Boxes if they don't exist
     *
     * @return void
     */
    public function create_default_post_list_boxes()
    {
        // Check if defaults have already been created
        $defaults_created = get_option('pps_post_list_box_defaults_created', false);
        if ($defaults_created) {
            return;
        }

        // Default Post List Boxes to create
        $default_boxes = [
            [
                'title' => __('Default List Box', 'organize-series'),
                'slug' => 'default-list-box',
            ],
            [
                'title' => __('Grid Style Box', 'organize-series'),
                'slug' => 'grid-style-box',
                'overrides' => [
                    'show_post_excerpt' => '1',
                    'excerpt_length' => '20',
                    'show_post_thumbnail' => 1,
                    'thumbnail_width' => '80',
                    'thumbnail_height' => '80',
                    'gap_between_items' => '20',
                    'layout_style' => 'grid',
                    'columns' => '2',
                ],
            ],
            [
                'title' => __('Simple List Box', 'organize-series'),
                'slug' => 'simple-list-box',
                'overrides' => [
                    'show_post_excerpt' => '1',
                    'excerpt_length' => '20',
                    'show_post_thumbnail' => 0,
                    'gap_between_items' => '20',
                    'show_post_date' => 0,
                    'item_padding' => '0',
                    'item_border_width' => '0',
                    'layout_style' => 'list',
                    'post_list_background_color' => '#f9f9f9',
                    'show_post_excerpt' => 0,
                    'highlight_current_post' => 0,
                    'gap_between_items' => '5',

                ],
            ],
        ];

        foreach ($default_boxes as $box_data) {
            $this->create_default_post_list_box($box_data);
        }

        // Mark that defaults have been created
        update_option('pps_post_list_box_defaults_created', true);
    }

    /**
     * Create a single default Post List Box
     *
     * @param array $box_data Box configuration data
     * @return void
     */
    private function create_default_post_list_box($box_data)
    {
        // Check if box already exists
        $existing = get_page_by_path($box_data['slug'], OBJECT, self::POST_TYPE_BOXES);
        if ($existing) {
            return;
        }

        // Get default data
        $default_data = PPS_Post_List_Box_Fields::get_default_post_list_box_data();

        // Apply overrides if any
        if (isset($box_data['overrides'])) {
            $default_data = array_merge($default_data, $box_data['overrides']);
        }

        // Create the post
        $post_data = [
            'post_title' => $box_data['title'],
            'post_name' => $box_data['slug'],
            'post_type' => self::POST_TYPE_BOXES,
            'post_status' => 'publish',
            'post_content' => '',
        ];

        $post_id = wp_insert_post($post_data);

        if ($post_id && !is_wp_error($post_id)) {
            // Save the default meta data
            update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $default_data);
        }
    }

    /**
     * Set default post list box selection in settings
     *
     * @param array $settings Current settings
     * @return array Modified settings
     */
    public function set_default_post_list_box_selection($settings)
    {
        // Only set default if not already set
        if (!isset($settings['series_post_list_box_selection'])) {
            $default_box_id = PPS_Post_List_Box_Utilities::get_default_post_list_box_id();
            $settings['series_post_list_box_selection'] = $default_box_id ?: '';
        }
        
        return $settings;
    }

    /**
     * Save Post List box data
     *
     * @param integer $post_id post id
     *
     * @return void
     */
    public function save_post_list_box_data($post_id) {
        if (empty($_POST['post-list-box-editor-nonce'])
            || !wp_verify_nonce(sanitize_key($_POST['post-list-box-editor-nonce']), 'post-list-box-editor')) {
            return;
        }

        $post = get_post($post_id);

        $fields = apply_filters('pps_post_list_box_fields', PPS_Post_List_Box_Fields::get_fields($post), $post);
        $excluded_input = ['template_action', 'import_action'];
        $meta_data = [];
        
        foreach ($fields as $key => $args) {
            if (!isset($_POST[$key]) || in_array($key, $excluded_input)) {
                continue;
            }
            if (isset($args['sanitize']) && is_array($args['sanitize']) && $_POST[$key] !== '') {
                $value = $_POST[$key]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                foreach ($args['sanitize'] as $sanitize) {
                    $value = is_array($value) ? map_deep($value, $sanitize) : $sanitize($value);
                }
                $meta_data[$key] = $value;
            } else {
                $sanitize = isset($args['sanitize']) ? $args['sanitize'] : 'sanitize_text_field';
                $meta_data[$key] = (isset($_POST[$key]) && $_POST[$key] !== '') ? $sanitize($_POST[$key]) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            }
        }

        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $meta_data);
    }

    /**
     * Enqueue Admin Scripts
     *
     * @return void
     */
    public function enqueue_admin_scripts()
    {
        global $pagenow, $post_type, $post;

        if (! in_array($pagenow, ['post.php', 'post-new.php'])
            || $post_type !== self::POST_TYPE_BOXES
        ) {
            return;
        }

        // Color picker style
        wp_enqueue_style('wp-color-picker');

        // Add code editor
        wp_enqueue_code_editor(['type' => 'text/css']);

        // Enqueue media library for media picker
        wp_enqueue_media();

        wp_enqueue_script(
            'post-list-box-editor-js',
            $this->module_url . '/assets/js/post-list-box-editor.js',
            [
                'jquery',
                'wp-color-picker',
                'underscore',
                'code-editor',
            ],
            ORG_SERIES_VERSION,
            true
        );

        wp_localize_script(
            'post-list-box-editor-js',
            'postListBoxEditor',
            [
                'post_id' => $post->ID,
                'nonce' => wp_create_nonce('post-list-box-nonce'),
            ]
        );

        wp_enqueue_style(
            'post-list-box-editor-css',
            $this->module_url . '/assets/css/post-list-box-editor.css',
            [],
            ORG_SERIES_VERSION
        );
    }

   
}

// Initialize the module
add_action('init', function() {
    new PPS_Post_List_Box();
}, 5);
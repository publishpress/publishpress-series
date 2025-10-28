<?php
/**
 * Admin UI for Post List Box
 */

class PPS_Post_List_Box_Admin_UI {

    const POST_TYPE_BOXES = 'pps_post_list_box';
    const META_PREFIX = 'pps_post_list_box_';

    /**
     * Initialize admin UI
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'admin_submenu'], 11);
        add_filter('post_updated_messages', [__CLASS__, 'set_post_update_messages']);
        add_filter('bulk_post_updated_messages', [__CLASS__, 'set_post_bulk_update_messages'], 10, 2);
        add_action('add_meta_boxes', [__CLASS__, 'add_preview_metabox']);
        add_action('add_meta_boxes', [__CLASS__, 'add_editor_metabox']);
        add_action('add_meta_boxes', [__CLASS__, 'add_shortcode_metabox']);
        add_filter('manage_edit-' . self::POST_TYPE_BOXES . '_columns', [__CLASS__, 'filter_post_list_box_columns']);
        add_action('manage_' . self::POST_TYPE_BOXES . '_posts_custom_column', [__CLASS__, 'manage_post_list_box_columns'], 10, 2);
        add_filter('parent_file', [__CLASS__, 'set_parent_file']);
    }

    /**
     * Set series menu as parent for post type so menu is shown
     * as active when on post type edit screen.
     *
     * @param string $parent_file
     *
     * @return string
     */
    public static function set_parent_file($parent_file)
    {
        global $submenu_file, $current_screen;

        // Check if the current screen is the Post List Box page
        if (!empty($current_screen->post_type) && $current_screen->post_type == self::POST_TYPE_BOXES) {
            $parent_file = 'orgseries_options_page';
            $submenu_file = 'edit.php?post_type=' . self::POST_TYPE_BOXES;
        }

        return $parent_file;
    }

    /**
     * @param $columns
     *
     * @return array
     */
    public static function filter_post_list_box_columns($columns)
    {
        $columns['default_post_list_box'] = esc_html__('Default Post List Box', 'organize-series');
        $columns['shortcode'] = esc_html__('Shortcode', 'organize-series');
        unset($columns['date']);

        return $columns;
    }

    /**
     * @param $column
     * @param $postId
     */
    public static function manage_post_list_box_columns($column, $postId)
    {
        if ($column === 'shortcode') {
            $layout_slug = self::POST_TYPE_BOXES . '_' . $postId;
        ?>
            <input readonly type="text" class="pps-shortcode-input" value='[pps_post_list_box layout="<?php echo esc_attr($layout_slug); ?>"]' />
        <?php
        } elseif ($column === 'default_post_list_box') {
            // Retrieve selected default Post List Box ID from settings
            $options = get_option('org_series_options');
            $has_selection = is_array($options) && array_key_exists('series_post_list_box_selection', $options);
            $selected_id = $has_selection ? (int) $options['series_post_list_box_selection'] : 0;

            
            if (!$has_selection) {
                $default_id = PPS_Post_List_Box_Utilities::get_default_post_list_box_id();
                if ($default_id) {
                    $selected_id = $default_id;
                }
            }

            if ($selected_id > 0 && $selected_id === (int) $postId) :
            ?>
                <span style="color: green; margin-left:30px;" class="dashicons dashicons-yes-alt"></span>
            <?php
            endif;
        }
    }

    /**
     * Add the admin submenu.
     */
    public static function admin_submenu()
    {
        // Add the submenu to the PublishPress Series menu.
        add_submenu_page(
            'orgseries_options_page',
            esc_html__('Post List Boxes', 'organize-series'),
            esc_html__('Post List Boxes', 'organize-series'),
            'manage_publishpress_series',
            'edit.php?post_type=' . self::POST_TYPE_BOXES
        );
    }

    /**
     * Add custom update messages to the post_updated_messages filter flow.
     *
     * @param array $messages Post updated messages.
     *
     * @return  array   $messages
     */
    public static function set_post_update_messages($messages)
    {
        $messages[self::POST_TYPE_BOXES] = [
            1 => __('Post List Box updated.', 'organize-series'),
            4 => __('Post List Box updated.', 'organize-series'),
            6 => __('Post List Box added.', 'organize-series'),
            7 => __('Post List Box saved.', 'organize-series'),
            8 => __('Post List Box submitted.', 'organize-series'),
        ];

        return $messages;
    }

    /**
     * Add custom update messages to the bulk_post_updated_messages filter flow.
     *
     * @param array $messages Array of messages.
     * @param array $counts Array of item counts for each message.
     *
     * @return  array   $messages
     */
    public static function set_post_bulk_update_messages($messages, $counts)
    {
        $countsUpdated = (int)$counts['updated'];
        $countsLocked = (int)$counts['locked'];
        $countsDeleted = (int)$counts['deleted'];
        $countsTrashed = (int)$counts['trashed'];
        $countsUntrashed = (int)$counts['untrashed'];

        $postTypeNameSingular = __('Post List Box', 'organize-series');
        $postTypeNamePlural = __('Post List Boxes', 'organize-series');

        $messages[self::POST_TYPE_BOXES] = [
            'updated' => sprintf(
                _n('%1$s %2$s updated.', '%1$s %3$s updated.', $countsUpdated),
                $countsUpdated,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'locked' => sprintf(
                _n(
                    '%1$s %2$s not updated, somebody is editing it.',
                    '%1$s %3$s not updated, somebody is editing them.',
                    $countsLocked
                ),
                $countsLocked,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'deleted' => sprintf(
                _n('%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $countsDeleted),
                $countsDeleted,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'trashed' => sprintf(
                _n('%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $countsTrashed),
                $countsTrashed,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'untrashed' => sprintf(
                _n('%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $countsUntrashed),
                $countsUntrashed,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
        ];

        return $messages;
    }

    /**
     * Add editor metabox
     *
     * @return void
     */
    public static function add_preview_metabox()
    {
        add_meta_box(
            self::META_PREFIX . 'preview_area',
            __('Post List Box Preview', 'organize-series'),
            [__CLASS__, 'render_preview_metabox'],
            self::POST_TYPE_BOXES,
            'normal',
            'high'
        );
    }

    /**
     * Add editor metabox
     *
     * @return void
     */
    public static function add_editor_metabox()
    {
        add_meta_box(
            self::META_PREFIX . 'editor_area',
            __('Post List Box Editor', 'organize-series'),
            [__CLASS__, 'render_editor_metabox'],
            self::POST_TYPE_BOXES,
            'normal',
            'high'
        );
    }

    /**
     * Add shortcode metabox
     *
     * @return void
     */
    public static function add_shortcode_metabox()
    {
        add_meta_box(
            self::META_PREFIX . 'shortcode',
            __('Shortcode', 'organize-series'),
            [__CLASS__, 'render_shortcode_metabox'],
            self::POST_TYPE_BOXES,
            'side'
        );
    }

    /**
     * Render layout slug metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public static function render_layout_slug_metabox(\WP_Post $post)
    {
        $layout_slug = self::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <input type="text" value="<?php echo esc_attr($layout_slug); ?>" readonly />
    <?php
    }

    /**
     * Render shortcode metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public static function render_shortcode_metabox(\WP_Post $post)
    {
        $layout_slug = self::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <p><label for="pps-post-list-box-shortcode"><?php esc_html_e('Use this shortcode:', 'organize-series'); ?></label></p>
        <textarea readonly>[pps_post_list_box layout="<?php echo esc_attr($layout_slug); ?>"]</textarea>
        <p class="description"><?php esc_html_e('Insert into posts or pages to display this Post List Box manually.', 'organize-series'); ?></p>
    <?php
    }

    /**
     * Render preview metabox
     *
     * @param \WP_Post $post
     *
     * @return void
     */
    public static function render_preview_metabox(\WP_Post $post)
    {
        $fields = apply_filters('pps_post_list_box_editor_fields', PPS_Post_List_Box_Fields::get_fields($post), $post);

        if ($post->post_status === 'auto-draft') {
            $editor_data = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post->ID, true);
        } else {
            $editor_data = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post->ID);
        }

        // Get all available series for the dropdown
        $all_series = get_terms([
            'taxonomy' => 'series',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $preview_series_id = 0;
        if (!empty($all_series) && !is_wp_error($all_series)) {
            $preview_series_id = $all_series[0]->term_id;
        }
        
        ?>
        <div class="pressshack-admin-wrapper publishpress-post-list-box-editor">
            <div class="preview-section wrapper-column">
                <div class="pps-series-selector-container" style="margin-bottom: 20px;">
                    <?php if (!empty($all_series) && !is_wp_error($all_series)) : ?>
                        <div class="pps-series-selector">
                            <label for="pps-preview-series-select">
                                <?php esc_html_e('Select Series to Preview:', 'organize-series'); ?>
                            </label>
                            <select id="pps-preview-series-select" style="margin-left: 10px; min-width: 200px;">
                                <?php foreach ($all_series as $series) : ?>
                                    <option value="<?php echo esc_attr($series->term_id); ?>">
                                        <?php echo esc_html($series->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="pps-post-list-box-preview">
                    
                    <div id="pps-preview-content">
                        <?php 
                        if (empty($all_series) || is_wp_error($all_series)) {
                            echo '<p>' . esc_html__('No series found. Create a series to see the preview.', 'organize-series') . '</p>';
                        } else {
                            // Initial preview with first series
                            $layout_slug = self::POST_TYPE_BOXES . '_' . $post->ID;
                            
                            // Load the renderer if not already loaded
                            if (!class_exists('PostListBoxRenderer')) {
                                require_once __DIR__ . '/classes/PostListBoxRenderer.php';
                            }
                            
                            // Create sample posts for preview
                            $sample_posts = PPS_Post_List_Box_Preview::get_sample_series_posts($preview_series_id);
                            if (!empty($sample_posts)) {
                                echo PPS_Post_List_Box_Preview::render_preview_content($editor_data, $sample_posts);
                            } else {
                                echo '<p>' . esc_html__('No posts found in selected series.', 'organize-series') . '</p>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Store the current selected series to maintain state
            var currentSeriesId = $('#pps-preview-series-select').val();
            
            $('#pps-preview-series-select').on('change', function() {
                currentSeriesId = $(this).val();
                var postId = <?php echo esc_js($post->ID); ?>;
                
                $('#pps-preview-content').html('<p><?php esc_html_e('Loading preview...', 'organize-series'); ?></p>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pps_update_post_list_box_preview',
                        post_id: postId,
                        series_id: currentSeriesId,
                        nonce: '<?php echo wp_create_nonce('post-list-box-nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#pps-preview-content').html(response.data.preview);
                        } else {
                            $('#pps-preview-content').html('<p><?php esc_html_e('Error loading preview.', 'organize-series'); ?></p>');
                        }
                    },
                    error: function() {
                        $('#pps-preview-content').html('<p><?php esc_html_e('Error loading preview.', 'organize-series'); ?></p>');
                    }
                });
            });
            
            // Prevent the series selector from triggering the global preview update
            $('#pps-preview-series-select').on('change input keyup click', function(e) {
                e.stopPropagation();
            });
        });
        
        </script>
        <?php
    }

    /**
     * Render editor metabox
     *
     * @param \WP_Post $post
     *
     * @return void
     */
    public static function render_editor_metabox(\WP_Post $post)
    {
        $fields_tabs  = apply_filters('pps_post_list_box_editor_fields_tabs', PPS_Post_List_Box_Fields::get_fields_tabs($post), $post);
        $fields = apply_filters('pps_post_list_box_editor_fields', PPS_Post_List_Box_Fields::get_fields($post), $post);
        ?>
        <div class="pressshack-admin-wrapper publishpress-post-list-box-editor">
            <div class="pps-post-list-box-editor-tabs">
                <ul>
                    <?php
                    foreach ($fields_tabs as $key => $args) {
                        $active_tab = ($key === PPS_Post_List_Box_Fields::default_tab()) ? ' active' : ''; ?>
                    <li>
                        <a data-tab="<?php esc_attr_e($key); ?>"
                            class="<?php esc_attr_e($active_tab); ?>"
                            href="#"
                            >
                            <span class="<?php esc_attr_e($args['icon']); ?>"></span>
                            <span class="item"><?php esc_html_e($args['label']); ?></span>
                            <?php if ($key === 'layout') : ?>
                                <span class="ppseries-pro-lock" >
                                    <span class="ppseries-pro-badge" style="padding: 1px 10px;">PRO</span>
                                    <span class="tooltip-text">
                                        <span><?php esc_html_e('This feature is available in PublishPress Series Pro', 'organize-series'); ?></span>
                                        <i></i>
                                    </span>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php
                    } ?>
                </ul>
            </div>

            <div class="pps-post-list-box-editor-fields wrapper-column">
                <table class="form-table pps-post-list-boxes-editor-table fixed" role="presentation">
                    <tbody>
                        <?php
                        if ($post->post_status === 'auto-draft') {
                            $editor_data = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post->ID, true);
                        } else {
                            $editor_data = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post->ID);
                        }

                        foreach ($fields as $key => $args) {
                            $args['key']       = $key;
                            $args['value']     = isset($editor_data[$key]) ? $editor_data[$key] : '';
                            $args['post_id']   = $post->ID;
                            echo PPS_Post_List_Box_Admin_UI::get_rendered_post_list_box_editor_partial($args);
                        }

                        wp_nonce_field('post-list-box-editor', 'post-list-box-editor-nonce');
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div id="post-list-field-icons-modal" style="display: none;">
            <div id="post-list-field-icons-container" class="post-list-field-icons-container"></div>
        </div>
        <?php
    }

    /**
     * Get a rendered field partial
     *
     * @param array $args Arguments to render in the partial.
     */
    public static function get_rendered_post_list_box_editor_partial($args)
    {
        $defaults = [
            'type'        => 'text',
            'tab'         => PPS_Post_List_Box_Fields::default_tab(),
            'options'     => [],
            'value'       => '',
            'label'       => '',
            'group_title' => '',
            'description' => '',
            'min'         => '',
            'max'         => '',
            'placeholder' => '',
            'rows'        => '20',
            'readonly'    => false,
            'tabbed'      => 0,
            'tab_name'    => '',
            'show_input'  => false,
            'post_id'     => false,
            'group_start' => false,
            'group_end'   => false,
        ];

        $args      = array_merge($defaults, $args);
        $key       = $args['key'];
        $tab_class = 'pps-boxes-editor-tab-content pps-' . $args['tab'] . '-tab ' . $args['type'] . ' pps-editor-' . $key;

        if ('range' === $args['type'] && $args['show_input']) {
            $tab_class .= ' double-input';
        }

        if ((int)$args['tabbed'] > 0) {
            $tab_class .= ' tabbed-content tabbed-content-' . $args['tab_name'];
        }

        $tab_style = ($args['tab'] === PPS_Post_List_Box_Fields::default_tab()) ? '' : 'display:none;';
        // Lock all fields under the "Layout" tab and specific Item fields for PRO
        $pro_locked = (
            ($args['tab'] === 'layout' && $args['type'] !== 'category_separator')
            || in_array($key, ['show_post_excerpt', 'show_post_author', 'show_post_date', 'fallback_featured_image'], true)
        );
        ob_start();
        $generate_tab_title = false;
        if (in_array($args['type'], ['textarea', 'export_action', 'import_action', 'template_action', 'line_break', 'code_editor', 'category_separator'])) {
            $th_style = 'display: none;';
            $colspan  = 2;
        } else {
            $th_style = '';
            $colspan  = '';
        }
        ?>
        <?php if ($args['group_start'] === true) :
           ?>
            <tr
                class="group-title-row <?php echo esc_attr($tab_class); ?>"
                data-tab="<?php echo esc_attr($args['tab']); ?>"
                style="<?php echo esc_attr($tab_style); ?>"
            >
                <td colspan="2" style="padding-left: 0; padding-right: 0;">
                <div class="post-list-boxes-group-table-wrap">
                    <div class="table-title"><?php echo esc_html($args['group_title']); ?></div>
                        <table>
        <?php endif; ?>
        <tr
            class="<?php echo esc_attr($tab_class); ?>"
            data-tab="<?php echo esc_attr($args['tab']); ?>"
            style="<?php echo esc_attr($tab_style); ?>"
            <?php if (!empty($args['depends_on'])) : ?>
                data-depends-on="<?php echo esc_attr($args['depends_on']); ?>"
                data-depends-value="<?php echo esc_attr($args['depends_value']); ?>"
            <?php endif; ?>
            >
            <?php if (!empty($args['label'])) : ?>
                <th scope="row" style="<?php echo esc_attr($th_style); ?>">
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($args['label']); ?>
                    </label>
                </th>
            <?php endif; ?>
            <td class="input" colspan="<?php echo esc_attr($colspan); ?>">
                <?php
                // If PRO-locked, start wrapper
                if ($pro_locked) : ?>
                    <div class="ppseries-pro-lock">
                <?php endif; ?>
                <?php
                if ('number' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        min="<?php echo esc_attr($args['min']); ?>"
                        max="<?php echo esc_attr($args['max']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>
                         />
                        <?php
                elseif ('checkbox' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="1"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>
                        <?php checked($args['value'], 1); ?> />
                <?php
                elseif ('select' === $args['type']) :
                    ?>
                    <select name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>
                        >
                        <?php foreach ($args['options'] as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>"
                                <?php selected($key, $args['value']); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php
                elseif ('color' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        class="pps-editor-color-picker"
                        id="<?php echo esc_attr($key); ?>"
                        type="text"
                        value="<?php echo esc_attr($args['value']); ?>"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ''; ?> />
                <?php
                elseif ('textarea' === $args['type']) :
                    ?>
                    <textarea name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>
                        ><?php echo esc_html($args['value']); ?></textarea>
                <?php
                elseif ('code_editor' === $args['type']) :
                    ?>
                    <label for="<?php echo esc_attr($key); ?>" class="code-editor-label">
                        <?php echo esc_html($args['label']); ?>
                    </label>
                    <div class="code-mirror-before"><div><?php echo htmlentities('<style type="text/css">'); ?></div></div>
                    <textarea
                        name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        data-editor_mode="<?php echo esc_attr($args['editor_mode']); ?>"
                        class="pps-post-list-code-editor"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>><?php echo esc_html($args['value']); ?></textarea>
                    <div class="code-mirror-after"><div><?php echo htmlentities('</style>'); ?></div></div>
                    <?php
                elseif ('category_separator' === $args['type']) :
                    ?>
                    <div class="pps-category-separator">
                        <h4 class="category-title"><?php echo esc_html($args['label']); ?></h4>
                        <hr class="category-divider">
                    </div>
                    <?php
                elseif ('media' === $args['type']) :
                    ?>
                    <div class="pps-media-picker-wrapper">
                        <input name="<?php echo esc_attr($key); ?>"
                            id="<?php echo esc_attr($key); ?>"
                            class="pps-media-picker-input"
                            type="hidden"
                            value="<?php echo esc_attr($args['value']); ?>" />
                        <div class="pps-media-preview">
                            <?php if (!empty($args['value'])) :
                                $image = wp_get_attachment_image_src($args['value'], 'thumbnail');
                                if ($image) : ?>
                                <img src="<?php echo esc_url($image[0]); ?>" alt="" style="max-width: 150px; height: auto;" />
                            <?php endif; endif; ?>
                        </div>
                        <button type="button" class="button pps-media-picker-button" data-field-id="<?php echo esc_attr($key); ?>" <?php echo $pro_locked ? 'disabled="disabled"' : ''; ?>>
                            <?php esc_html_e('Select Image', 'organize-series'); ?>
                        </button>
                        <?php if (!empty($args['value'])) : ?>
                            <button type="button" class="button pps-media-remove-button" data-field-id="<?php echo esc_attr($key); ?>" <?php echo $pro_locked ? 'disabled="disabled"' : ''; ?>>
                                <?php esc_html_e('Remove', 'organize-series'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php
                else : ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo $pro_locked ? 'disabled="disabled"' : ((isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''); ?>
                         />
                <?php endif; ?>
                <?php if ($pro_locked) : ?>
                        <span class="ppseries-pro-badge">PRO</span>
                        <span class="tooltip-text">
                            <span><?php esc_html_e('This feature is available in PublishPress Series Pro', 'organize-series'); ?></span>
                            <i></i>
                        </span>
                    </div>
                <?php endif; ?>
                <?php if (isset($args['description']) && !empty($args['description'])) : ?>
                        <?php if($args['type'] !== 'checkbox') : ?>
                            <br />
                        <?php endif; ?>
                        <span class="field-description description">
                            <?php echo $args['description']; ?>
                        </span>
                <?php endif; ?>
            </td>
        </tr>
        <?php if ($args['group_end'] === true) : ?>
                        </table>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
        <?php
        return ob_get_clean();
    }
}
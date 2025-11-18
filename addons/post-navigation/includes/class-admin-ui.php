<?php
/**
 * Admin UI for Series Post Navigation
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Admin_UI
{
    /**
     * Boot hooks
     */
    public static function init()
    {
        add_action('admin_menu', [__CLASS__, 'register_menu'], 12);
        add_filter('parent_file', [__CLASS__, 'highlight_menu']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_filter('manage_edit-' . PPS_Series_Post_Navigation_Utilities::POST_TYPE . '_columns', [__CLASS__, 'register_columns']);
        add_action('manage_' . PPS_Series_Post_Navigation_Utilities::POST_TYPE . '_posts_custom_column', [__CLASS__, 'render_columns'], 10, 2);
    }

    /**
     * Add submenu entry under Series options
     */
    public static function register_menu()
    {
        add_submenu_page(
            'orgseries_options_page',
            __('Post Navigation', 'publishpress-series'),
            __('Post Navigation', 'publishpress-series'),
            'manage_publishpress_series',
            'edit.php?post_type=' . PPS_Series_Post_Navigation_Utilities::POST_TYPE,
            null,
            50
        );
    }

    /**
     * Ensure proper menu highlighting when editing
     */
    public static function highlight_menu($parent_file)
    {
        global $current_screen;

        if (! empty($current_screen->post_type) && $current_screen->post_type === PPS_Series_Post_Navigation_Utilities::POST_TYPE) {
            $parent_file = 'orgseries_options_page';
        }

        return $parent_file;
    }

    /**
     * Setup meta boxes
     */
    public static function add_meta_boxes()
    {
        // Enqueue media scripts for the media uploader
        global $post_type;
        if ($post_type === PPS_Series_Post_Navigation_Utilities::POST_TYPE) {
            wp_enqueue_media();
        }

        add_meta_box(
            'pps_series_post_navigation_preview',
            __('Series Post Navigation Preview', 'publishpress-series'),
            [__CLASS__, 'render_preview_box'],
            PPS_Series_Post_Navigation_Utilities::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'pps_series_post_navigation_editor',
            __('Series Post Navigation Editor', 'publishpress-series'),
            [__CLASS__, 'render_editor_box'],
            PPS_Series_Post_Navigation_Utilities::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'pps_series_post_navigation_shortcode',
            __('Shortcode', 'publishpress-series'),
            [__CLASS__, 'render_shortcode_box'],
            PPS_Series_Post_Navigation_Utilities::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Preview metabox content
     */
    public static function render_preview_box(WP_Post $post)
    {
        echo '<div class="pps-series-post-navigation-preview">';
        echo '<div id="pps-series-post-navigation-preview-content" class="pps-series-post-navigation-preview-content">';
        PPS_Series_Post_Navigation_Preview::render_preview($post);
        echo '</div>';
        echo '</div>';
    }

    /**
     * Editor metabox content
     */
    public static function render_editor_box(WP_Post $post)
    {
        $tabs = apply_filters('pps_series_post_navigation_editor_tabs', [], $post);
        $fields = PPS_Series_Post_Navigation_Fields::get_fields($post);
        $settings = PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings($post->ID, $post->post_status === 'auto-draft');

        echo '<div class="publishpress-series-post-navigation-editor">';

        if (! empty($tabs)) {
            echo '<div class="pps-series-post-navigation-editor-tabs"><ul>';
            foreach ($tabs as $key => $data) {
                $active = $key === PPS_Series_Post_Navigation_Fields::DEFAULT_TAB ? ' active' : '';
                echo '<li><a href="#" data-tab="' . esc_attr($key) . '"' . $active . '>';
                if (! empty($data['icon'])) {
                    echo '<span class="dashicons ' . esc_attr($data['icon']) . '"></span> ';
                }
                echo esc_html($data['label']);
                
                echo '</a></li>';
            }
            echo '</ul></div>';
        }

        echo '<div class="pps-series-post-navigation-editor-fields">';
        echo '<table class="form-table pps-series-post-navigation-editor-table" role="presentation"><tbody>';
        foreach ($fields as $key => $field) {
            $value = isset($settings[$key]) ? $settings[$key] : '';
            $field['key'] = $key;
            $field['value'] = $value;
            self::render_field_row($field);
        }
        echo '</tbody></table>';

        wp_nonce_field(PPS_SERIES_POST_NAVIGATION_NONCE, PPS_SERIES_POST_NAVIGATION_NONCE_FIELD);

        echo '</div>'; // .pps-series-post-navigation-editor-fields
        echo '</div>'; // .publishpress-series-post-navigation-editor
    }

    /**
     * Render shortcode info
     */
    public static function render_shortcode_box(WP_Post $post)
    {
        $layout_slug = 'pps_nav_' . $post->ID;
        echo '<p><label for="pps-series-post-navigation-shortcode">' . esc_html__('Use this shortcode:', 'publishpress-series') . '</label></p>';
        echo '<textarea id="pps-series-post-navigation-shortcode" readonly class="widefat" rows="2">[pps_post_navigation layout="' . esc_attr($layout_slug) . '"]</textarea>';
        echo '<p class="description">' . esc_html__('Insert into posts or pages to display this Series Post Navigation manually.', 'publishpress-series') . '</p>';
    }

    /**
     * Add custom columns
     */
    public static function register_columns($columns)
    {
        $columns['series_nav_default'] = esc_html__('Default Navigation', 'publishpress-series');
        $columns['series_nav_shortcode'] = esc_html__('Shortcode', 'publishpress-series');
        unset($columns['date']);

        return $columns;
    }

    /**
     * Render column content
     */
    public static function render_columns($column, $post_id)
    {
        if ('series_nav_shortcode' === $column) {
            $layout_slug = 'pps_nav_' . $post_id;
            ?>
            <input readonly class="pps-shortcode-input" type="text" value='[pps_post_navigation layout="<?php echo esc_attr($layout_slug); ?>"]' />
            <?php
            return;
        }

        if ('series_nav_default' === $column) {
            $options = get_option('org_series_options');
            $selected = isset($options['series_post_navigation_selection']) ? (int) $options['series_post_navigation_selection'] : 0;
            if ($selected === (int) $post_id) {
                ?>
                <span style="color: green; margin-left:30px;" class="dashicons dashicons-yes-alt"></span>
                <?php
            }
        }
    }

    /**
     * Render individual field rows
     */
    private static function render_field_row(array $args)
    {
        $defaults = [
            'type'        => 'text',
            'tab'         => PPS_Series_Post_Navigation_Fields::DEFAULT_TAB,
            'label'       => '',
            'description' => '',
            'rows'        => 5,
            'options'     => [],
            'value'       => '',
            'depends_on'  => '',
            'depends_value' => '',
        ];

        $args = array_merge($defaults, $args);
        $tab_attr = ' data-tab="' . esc_attr($args['tab']) . '"';
        $row_style = $args['tab'] === PPS_Series_Post_Navigation_Fields::DEFAULT_TAB ? '' : ' style="display:none;"';

        // Add conditional field attributes
        $conditional_attrs = '';
        if (!empty($args['depends_on'])) {
            $conditional_attrs .= ' data-depends-on="' . esc_attr($args['depends_on']) . '"';
            $conditional_attrs .= ' data-depends-value="' . esc_attr($args['depends_value']) . '"';
        }

        echo '<tr' . $tab_attr . $row_style . $conditional_attrs . '>';

        // Category separators span both columns
        if ($args['type'] === 'category_separator') {
            echo '<td colspan="2" class="pps-field-separator" style="padding-top: 20px; padding-bottom: 10px; padding-left: 0px;">';
            self::render_field_input($args);
            echo '</td>';
        } else {
            echo '<th scope="row">';
            if (! empty($args['label'])) {
                echo '<label for="' . esc_attr($args['key']) . '">' . esc_html($args['label']) . '</label>';
            }
            echo '</th>';

            echo '<td class="pps-field">';
            self::render_field_input($args);
            if (! empty($args['description'])) {
                echo '<p class="description">' . wp_kses_post($args['description']) . '</p>';
            }
            echo '</td>';
        }

        echo '</tr>';
    }

    /**
     * Render different input types
     */
    private static function render_field_input(array $args)
    {
        $key = esc_attr($args['key']);
        $value = $args['value'];

        // Fields that should be PRO-locked in the free version
        $pro_locked_fields = [
            'previous_custom_arrow_image',
            'next_custom_arrow_image',
            'previous_show_featured_image',
            'next_show_featured_image',
            'first_show_featured_image',
            'previous_image_position',
            'next_image_position',
            'first_image_position',
            'previous_image_width',
            'previous_image_height',
            'next_image_width',
            'next_image_height',
            'first_image_width',
            'first_image_height',
            'gap_between_links',
            'alignment',
            'separator_text',
        ];

        $pro_locked = (
            $args['type'] !== 'category_separator' &&
            in_array($args['key'], $pro_locked_fields, true)
        );

        switch ($args['type']) {
            case 'category_separator':
                ?>
                <div class="pps-category-separator">
                    <h4 class="category-title"><?php echo esc_html($args['label']); ?></h4>
                    <hr class="category-divider" />
                </div>
                <?php
                break;

            default:
                if ($pro_locked) : ?>
                    <div class="ppseries-pro-lock">
                <?php
                endif;

                switch ($args['type']) {
                    case 'textarea':
                        printf(
                            '<textarea id="%1$s" name="%1$s" rows="%2$d" class="widefat"%3$s>%4$s</textarea>',
                            $key,
                            (int) $args['rows'],
                            $pro_locked ? ' disabled="disabled"' : '',
                            esc_textarea($value)
                        );
                        break;

                    case 'checkbox':
                        printf(
                            '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s%3$s /> %4$s</label>',
                            $key,
                            checked(1, (int) $value, false),
                            $pro_locked ? ' disabled="disabled"' : '',
                            ''
                        );
                        break;

                    case 'select':
                        echo '<select id="' . $key . '" name="' . $key . '"' . ($pro_locked ? ' disabled="disabled"' : '') . '>';
                        foreach ($args['options'] as $option_value => $label) {
                            printf(
                                '<option value="%1$s" %2$s>%3$s</option>',
                                esc_attr($option_value),
                                selected($option_value, $value, false),
                                esc_html($label)
                            );
                        }
                        echo '</select>';
                        break;

                    case 'color':
                        printf(
                            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="pps-color-picker"%3$s />',
                            $key,
                            esc_attr($value),
                            $pro_locked ? ' disabled="disabled"' : ''
                        );
                        break;

                    case 'number':
                        $min_attr = isset($args['min']) ? ' min="' . esc_attr($args['min']) . '"' : '';
                        $max_attr = isset($args['max']) ? ' max="' . esc_attr($args['max']) . '"' : '';
                        printf(
                            '<input type="number" id="%1$s" name="%1$s" value="%2$s" class="small-text"%3$s%4$s%5$s />',
                            $key,
                            esc_attr($value),
                            $min_attr,
                            $max_attr,
                            $pro_locked ? ' disabled="disabled"' : ''
                        );
                        break;

                    case 'media':
                        $attachment_id = (int) $value;
                        $image_url = '';
                        $has_image = false;
                        
                        if ($attachment_id > 0) {
                            $image_url = wp_get_attachment_image_url($attachment_id, 'thumbnail');
                            $has_image = !empty($image_url);
                        }
                        
                        echo '<div class="pps-media-field-wrapper">';
                        printf(
                            '<input type="hidden" id="%1$s" name="%1$s" value="%2$s" class="pps-media-field-value" />',
                            $key,
                            esc_attr($attachment_id)
                        );
                        
                        echo '<div class="pps-media-preview" style="margin-bottom: 10px;">';
                        if ($has_image) {
                            printf(
                                '<img src="%s" style="max-width: 150px; max-height: 150px; display: block; margin-bottom: 10px;" />',
                                esc_url($image_url)
                            );
                        }
                        echo '</div>';
                        
                        printf(
                            '<button type="button" class="button pps-media-upload-button" data-field-id="%s"%s>%s</button> ',
                            $key,
                            $pro_locked ? ' disabled="disabled"' : '',
                            $has_image ? __('Change Image', 'publishpress-series') : __('Select Image', 'publishpress-series')
                        );
                        
                        if ($has_image) {
                            printf(
                                '<button type="button" class="button pps-media-remove-button" data-field-id="%s"%s>%s</button>',
                                $key,
                                $pro_locked ? ' disabled="disabled"' : '',
                                __('Remove Image', 'publishpress-series')
                            );
                        }
                        
                        echo '</div>';
                        break;

                    default:
                        printf(
                            '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text"%3$s />',
                            $key,
                            esc_attr($value),
                            $pro_locked ? ' disabled="disabled"' : ''
                        );
                        break;
                }

                if ($pro_locked) : ?>
                        <span class="ppseries-pro-badge">PRO</span>
                        <span class="tooltip-text">
                            <span><?php esc_html_e('This feature is available in PublishPress Series Pro', 'organize-series'); ?></span>
                            <i></i>
                        </span>
                    </div>
                <?php
                endif;
                break;
        }
    }
}

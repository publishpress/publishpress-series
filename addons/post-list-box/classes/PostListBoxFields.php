<?php
/**
 * Post List Box Fields class
 * Defines the fields for Post List Box editor
 */

class PostListBoxFields
{
    /**
     * Initialize the fields
     */
    public static function init()
    {
        add_filter('pps_post_list_box_editor_fields', [__CLASS__, 'get_fields'], 10, 2);
    }

    /**
     * Get all fields for the post list box editor
     *
     * @param array $fields Existing fields
     * @param WP_Post $post Post object
     * @return array
     */
    public static function get_fields($fields, $post)
    {
        // Ensure $fields is an array
        if (!is_array($fields)) {
            $fields = [];
        }

        // Box Tab
        $fields['separator_title'] = [
            'type' => 'category_separator',
            'label' => __('Box Title', 'organize-series'),
            'tab' => 'box',
        ];
        $fields = array_merge($fields, self::get_title_fields());

        // Box Tab
        $fields['separator_box_style'] = [
            'type' => 'category_separator',
            'label' => __('Box Style', 'organize-series'),
            'tab' => 'box',
        ];
        $fields = array_merge($fields, self::get_box_style_fields());

        // Item Tab
        $fields['separator_content'] = [
            'type' => 'category_separator',
            'label' => __('Content Display', 'organize-series'),
            'tab' => 'item',
        ];
        $fields = array_merge($fields, self::get_content_fields());

        // Item Tab
        $fields['separator_thumbnail'] = [
            'type' => 'category_separator',
            'label' => __('Featured Image Settings', 'organize-series'),
            'tab' => 'item',
        ];
        $fields = array_merge($fields, self::get_thumbnail_fields());

        // Item Tab
        $fields['separator_item_style'] = [
            'type' => 'category_separator',
            'label' => __('Post Style', 'organize-series'),
            'tab' => 'item',
        ];
        $fields = array_merge($fields, self::get_item_style_fields());

        // Layout Tab
        $fields['separator_layout'] = [
            'type' => 'category_separator',
            'label' => __('Layout Display', 'organize-series'),
            'tab' => 'layout',
        ];
        $fields = array_merge($fields, self::get_layout_fields());

        // Layout Tab
        $fields['separator_highlights_current'] = [
            'type' => 'category_separator',
            'label' => __('Highlights Current Post', 'organize-series'),
            'tab' => 'item',
        ];
        $fields = array_merge($fields, self::get_highlights_current_fields());

        return $fields;
    }

    /**
     * Get title fields
     *
     * @return array
     */
    private static function get_title_fields()
    {
        return [
            'title_show' => [
                'label' => __('Show Box Title', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'box',
                'sanitize' => 'sanitize_text_field',
                'default' => 1,
            ],
            'title_type' => [
                'label' => __('Box Title Type', 'organize-series'),
                'type' => 'select',
                'tab' => 'box',
                'options' => [
                    'series' => __('Use Series Title', 'organize-series'),
                    'custom' => __('Custom Title', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'series',
                'description' => __('Choose whether to display the series title or a custom title', 'organize-series'),
                'depends_on' => 'title_show',
                'depends_value' => '1',
            ],
            'title_text' => [
                'label' => __('Custom Box Title Text', 'organize-series'),
                'type' => 'text',
                'tab' => 'box',
                'sanitize' => 'sanitize_text_field',
                'default' => __('Series Posts', 'organize-series'),
                'placeholder' => __('Enter custom title for the post list', 'organize-series'),
                'description' => __('Only used when "Custom Title" is selected above', 'organize-series'),
                'depends_on' => 'title_type',
                'depends_value' => 'custom',
            ],
            'title_html_tag' => [
                'label' => __('Box Title HTML Tag', 'organize-series'),
                'type' => 'select',
                'tab' => 'box',
                'options' => [
                    'h1' => 'H1',
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                    'div' => 'DIV',
                    'p' => 'P',
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'h3',
                'depends_on' => 'title_show',
                'depends_value' => '1',
            ],
            'title_color' => [
                'label' => __('Box Title Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'box',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#333333',
                'depends_on' => 'title_show',
                'depends_value' => '1',
            ],
            'title_font_size' => [
                'label' => __('Box Title Font Size (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'box',
                'min' => 10,
                'max' => 72,
                'sanitize' => 'absint',
                'default' => 24,
                'depends_on' => 'title_show',
                'depends_value' => '1',
            ],
        ];
    }

    /**
     * Get box style fields
     *
     * @return array
     */
    private static function get_box_style_fields()
    {
        return [
            'background_color' => [
                'label' => __('Background Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'box',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#f9f9f9',
            ],
            'border_color' => [
                'label' => __('Border Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'box',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#e5e5e5',
            ],
            'border_width' => [
                'label' => __('Border Width (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'box',
                'min' => 0,
                'max' => 20,
                'sanitize' => 'absint',
                'default' => 1,
            ],
            'border_radius' => [
                'label' => __('Border Radius (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'box',
                'min' => 0,
                'max' => 50,
                'sanitize' => 'absint',
                'default' => 4,
            ],
            'padding' => [
                'label' => __('Padding (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'box',
                'min' => 0,
                'max' => 100,
                'sanitize' => 'absint',
                'default' => 20,
            ],
        ];
    }

    /**
     * Get content display fields
     *
     * @return array
     */
    private static function get_content_fields()
    {
        return [
            'show_post_titles' => [
                'label' => __('Show Post Titles', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 1,
            ],
            'post_title_color' => [
                'label' => __('Post Title Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#0073aa',
                'depends_on' => 'show_post_titles',
                'depends_value' => '1',
            ],
            'post_title_font_size' => [
                'label' => __('Post Title Font Size (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 10,
                'max' => 36,
                'sanitize' => 'absint',
                'default' => 16,
                'depends_on' => 'show_post_titles',
                'depends_value' => '1',
            ],
            'show_post_excerpt' => [
                'label' => __('Show Post Excerpt', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 0,
            ],
            'excerpt_length' => [
                'label' => __('Excerpt Length (words)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 10,
                'max' => 500,
                'sanitize' => 'absint',
                'default' => 55,
                'depends_on' => 'show_post_excerpt',
                'depends_value' => '1',
            ],
            'excerpt_color' => [
                'label' => __('Excerpt Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#666666',
                'depends_on' => 'show_post_excerpt',
                'depends_value' => '1',
            ],
            'show_post_author' => [
                'label' => __('Show Post Author', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 0,
            ],
            'show_post_date' => [
                'label' => __('Show Post Date', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 1,
            ],
        ];
    }

    /**
     * Get thumbnail fields
     *
     * @return array
     */
    private static function get_thumbnail_fields()
    {
        return [
            'show_post_thumbnail' => [
                'label' => __('Show Featured Image', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 1,
            ],
            'thumbnail_width' => [
                'label' => __('Featured Image Width (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 50,
                'max' => 800,
                'sanitize' => 'absint',
                'default' => 150,
                'description' => __('Width of the featured image in pixels', 'organize-series'),
                'depends_on' => 'show_post_thumbnail',
                'depends_value' => '1',
            ],
            'thumbnail_height' => [
                'label' => __('Featured Image Height (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 50,
                'max' => 800,
                'sanitize' => 'absint',
                'default' => 150,
                'description' => __('Height of the featured image in pixels', 'organize-series'),
                'depends_on' => 'show_post_thumbnail',
                'depends_value' => '1',
            ],
        ];
    }

    /**
     * Get item style fields
     *
     * @return array
     */
    private static function get_item_style_fields()
    {
        return [
            'item_padding' => [
                'label' => __('Post Padding (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 0,
                'max' => 100,
                'sanitize' => 'absint',
                'default' => 15,
            ],
            'item_border_width' => [
                'label' => __('Post Border Width (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'item',
                'min' => 0,
                'max' => 20,
                'sanitize' => 'absint',
                'default' => 1,
            ],
            'item_border_color' => [
                'label' => __('Post Border Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#e5e5e5',
            ],
            'post_list_background_color' => [
                'label' => __('Post Background Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#ffffff',
                'description' => __('Background color for the post list container', 'organize-series'),
            ],
        ];
    }

    /**
     * Get layout fields
     *
     * @return array
     */
    private static function get_layout_fields()
    {
        return [
            'layout_style' => [
                'label' => __('Layout Style', 'organize-series'),
                'type' => 'select',
                'tab' => 'layout',
                'options' => [
                    'list' => __('List', 'organize-series'),
                    'grid' => __('Grid', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'list',
            ],
            'columns' => [
                'label' => __('Columns', 'organize-series'),
                'type' => 'select',
                'tab' => 'layout',
                'options' => [
                    '2' => __('2 Columns', 'organize-series'),
                    '3' => __('3 Columns', 'organize-series'),
                    '4' => __('4 Columns', 'organize-series'),
                    '6' => __('6 Columns', 'organize-series'),
                ],
                'sanitize' => 'absint',
                'default' => '3',
                'data-depends-on' => 'layout_style',
                'data-depends-value' => 'grid',
            ],
            'orderby' => [
                'label' => __('Order By', 'organize-series'),
                'type' => 'select',
                'tab' => 'layout',
                'options' => [
                    'date' => __('Date', 'organize-series'),
                    'title' => __('Title', 'organize-series'),
                    'series_order' => __('Series Order', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'series_order',
            ],
            'order' => [
                'label' => __('Order', 'organize-series'),
                'type' => 'select',
                'tab' => 'layout',
                'options' => [
                    'ASC' => __('Ascending', 'organize-series'),
                    'DESC' => __('Descending', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'ASC',
            ],
            'gap_between_items' => [
                'label' => __('Gap Between Items (px)', 'organize-series'),
                'type' => 'number',
                'tab' => 'layout',
                'min' => 0,
                'max' => 50,
                'sanitize' => 'absint',
                'default' => 10,
            ],
            'post_list_position' => [
                'label' => __('Post List Box Location', 'publishpress-series-pro'),
                'type' => 'select',
                'tab' => 'layout',
                'options' => [
                    'top' => __('Top', 'organize-series'),
                    'bottom' => __('Bottom', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default' => 'top',
                'description' => __('Choose where to display the post list box in relation to the content', 'organize-series'),
            ],
            'maximum_items' => [
                'label' => __('Maximum number of items', 'publishpress-series-pro'),
                'type' => 'number',
                'tab' => 'layout',
                'min' => 0,
                'sanitize' => 'absint',
                'default' => '',
                'description' => __('Limit the number of posts shown in the series list. Leave empty for no limit.', 'organize-series'),
            ],
        ];
    }

    /**
     * Get highlights current fields
     *
     * @return array
     */
    private static function get_highlights_current_fields()
    {
        return [
            'highlight_current_post' => [
                'label' => __('Highlight Current Post', 'organize-series'),
                'type' => 'checkbox',
                'tab' => 'item',
                'sanitize' => 'sanitize_text_field',
                'default' => 1,
                'description' => __('Highlight the current post when viewing a specific series post', 'organize-series'),
            ],
            'current_post_bg_color' => [
                'label' => __('Current Post Background Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#fff3cd',
            ],
            'current_post_border_color' => [
                'label' => __('Current Post Border Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#ffeaa7',
            ],
            'current_post_text_color' => [
                'label' => __('Current Post Text Color', 'organize-series'),
                'type' => 'color',
                'tab' => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default' => '#856404',
            ],
        ];
    }

    /**
     * Initialize the class
     */
    public static function register()
    {
        static::init();
    }
}

// Initialize the fields
PostListBoxFields::register();
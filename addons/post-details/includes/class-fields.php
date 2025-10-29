<?php
/**
 * Field definitions for Series Post Details editor
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Details_Fields
{
    const DEFAULT_TAB = 'general';

    /**
     * Hook provider
     */
    public static function init()
    {
        add_filter('pps_series_post_details_editor_tabs', [__CLASS__, 'get_tabs'], 10, 2);
        add_filter('pps_series_post_details_fields', [__CLASS__, 'filter_fields'], 10, 2);
    }

    /**
     * Define tabs for editor UI
     */
    public static function get_tabs($tabs, $post)
    {
        $base_tabs = [
            'general' => [
                'label' => __('General', 'organize-series'),
                'icon'  => 'dashicons-admin-generic',
            ],
            'styling' => [
                'label' => __('Styling', 'organize-series'),
                'icon'  => 'dashicons-art',
            ],
        ];

        if (! is_array($tabs)) {
            $tabs = [];
        }

        return array_merge($base_tabs, $tabs);
    }

    /**
     * Prepare field definitions and merge custom filters
     */
    public static function filter_fields($fields, $post)
    {
        if (! is_array($fields)) {
            $fields = [];
        }

        $fields = array_merge($fields, self::get_general_fields());
        $fields = array_merge($fields, self::get_styling_fields());

        return $fields;
    }

    /**
     * Ensure admin UI can request raw definitions
     */
    public static function get_fields($post)
    {
        // Start with base fields and apply filters
        $fields = [];
        $fields = self::filter_fields($fields, $post);

        return $fields;
    }

    /**
     * Default settings for meta boxes
     */
    public static function get_default_settings($post_id = 0)
    {
        return PPS_Series_Post_Details_Utilities::get_default_series_post_details_data($post_id);
    }

    /**
     * General tab fields
     */
    private static function get_general_fields()
    {
        return [
            'content_separator' => [
                'type'  => 'category_separator',
                'label' => __('Content', 'organize-series'),
                'tab'   => 'general',
            ],
            'show_part_number' => [
                'label'    => __('Show Part Number', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 1,
                'description' => __('Display "part X of Y" in the series text', 'organize-series'),
            ],
            'text_before' => [
                'label'    => __('Text Before', 'organize-series'),
                'type'     => 'text',
                'tab'      => 'general',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('This entry is', 'organize-series'),
                'description' => __('Text shown at the beginning', 'organize-series'),
            ],
            'text_after' => [
                'label'    => __('Text After', 'organize-series'),
                'type'     => 'text',
                'tab'      => 'general',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('in the series', 'organize-series'),
                'description' => __('Text shown before the series name', 'organize-series'),
            ],
            'position_separator' => [
                'type'  => 'category_separator',
                'label' => __('Display', 'organize-series'),
                'tab'   => 'general',
            ],
            'metabox_position' => [
                'label'    => __('Meta Box Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'general',
                'options'  => [
                    'top'     => __('Top', 'organize-series'),
                    'bottom'  => __('Bottom', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'top',
                'description' => __('Choose where to display the meta box in relation to the content', 'organize-series'),
            ],
            'limit_to_single' => [
                'label'    => __('Limit to Single Posts', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Enable to display meta box only on single post view, not on archives.', 'organize-series'),
            ],
        ];
    }

    /**
     * Styling fields
     */
    private static function get_styling_fields()
    {
        return [
            'typography_separator' => [
                'type'  => 'category_separator',
                'label' => __('Typography', 'organize-series'),
                'tab'   => 'styling',
            ],
            'text_size' => [
                'label'    => __('Text Size (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'styling',
                'min'      => 10,
                'max'      => 32,
                'sanitize' => 'absint',
                'default'  => 16,
                'description' => __('Font size for text in the meta box', 'organize-series'),
            ],
            'text_color' => [
                'label'    => __('Text Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'styling',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#1d2327',
            ],
            'link_color' => [
                'label'    => __('Link Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'styling',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#1a5aff',
            ],
            'background_separator' => [
                'type'  => 'category_separator',
                'label' => __('Background', 'publishpress-series-pro'),
                'tab'   => 'styling',
            ],
            'background_color' => [
                'label'    => __('Background Color', 'publishpress-series-pro'),
                'type'     => 'color',
                'tab'      => 'styling',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#eef5ff',
                'description' => __('Background color for the meta box container', 'publishpress-series-pro'),
            ],
            'spacing_separator' => [
                'type'  => 'category_separator',
                'label' => __('Spacing', 'organize-series'),
                'tab'   => 'styling',
            ],
            'padding' => [
                'label'    => __('Padding (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'styling',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 20,
                'description' => __('Padding applied to all sides of the content area', 'organize-series'),
            ],
            'margin' => [
                'label'    => __('Margin (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'styling',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Margin applied to all sides of the meta box', 'organize-series'),
            ],
            'border_separator' => [
                'type'  => 'category_separator',
                'label' => __('Border', 'organize-series'),
                'tab'   => 'styling',
            ],
            'border_width' => [
                'label'    => __('Border Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'styling',
                'min'      => 0,
                'max'      => 10,
                'sanitize' => 'absint',
                'default'  => 1,
            ],
            'border_radius' => [
                'label'    => __('Border Radius (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'styling',
                'min'      => 0,
                'max'      => 60,
                'sanitize' => 'absint',
                'default'  => 6,
            ],
            'border_color' => [
                'label'    => __('Border Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'styling',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#c7d7f5',
            ],
        ];
    }
}

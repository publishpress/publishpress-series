<?php
/**
 * Field definitions for Series Post Navigation editor
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Fields
{
    const DEFAULT_TAB = 'general';

    /**
     * Hook provider
     */
    public static function init()
    {
        add_filter('pps_series_post_navigation_editor_tabs', [__CLASS__, 'get_tabs'], 10, 2);
        add_filter('pps_series_post_navigation_fields', [__CLASS__, 'filter_fields'], 10, 2);
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
            'item' => [
                'label' => __('Item', 'organize-series'),
                'icon'  => 'dashicons-ellipsis',
            ],
            'layout' => [
                'label' => __('Layout', 'organize-series'),
                'icon'  => 'dashicons-editor-table',
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
        $fields = array_merge($fields, self::get_item_fields());
        $fields = array_merge($fields, self::get_layout_fields());

        return $fields;
    }

    /**
     * Ensure admin UI can request raw definitions
     */
    public static function get_fields($post)
    {
        $fields = [];
        $fields = self::filter_fields($fields, $post);
        return $fields;
    }

    /**
     * General tab fields
     */
    private static function get_general_fields()
    {
        return [
            'general_separator' => [
                'type'  => 'category_separator',
                'label' => __('General', 'organize-series'),
                'tab'   => 'general',
            ],
            'include_series_title' => [
                'label'    => __('Include Series Title', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'series_title_alignment' => [
                'label'    => __('Series Title Alignment', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'general',
                'options'  => [
                    'left'   => __('Left', 'organize-series'),
                    'center' => __('Center', 'organize-series'),
                    'right'  => __('Right', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'center',
            ],
            'series_title_color' => [
                'label'    => __('Series Title Color', 'publishpress-series-pro'),
                'type'     => 'color',
                'tab'      => 'general',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#2971B1',
            ],
            'hide_when_single_post' => [
                'label'    => __('Hide When Single Post', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 1,
                'description' => __('Do not display navigation if series has only one post', 'organize-series'),
            ],
            
        ];
    }

    /**
     * Item tab fields
     */
    private static function get_item_fields()
    {
        return [
            'prev_separator' => [
                'type'  => 'category_separator',
                'label' => __('Previous', 'organize-series'),
                'tab'   => 'item',
            ],
            'previous_link_type' => [
                'label'    => __('Previous Navigation Type', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'organize-series'),
                    'post_title'  => __('Previous Post Title', 'organize-series'),
                    'custom'      => __('Custom Label', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'custom',
                'description' => __('Choose how to display the previous navigation', 'organize-series'),
            ],
            'previous_label' => [
                'label'    => __('Previous Label', 'organize-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Previous', 'organize-series'),
                'description' => __('Custom text for previous post link', 'organize-series'),
            ],
            'previous_show_featured_image' => [
                'label'    => __('Show Featured Image', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for previous post', 'organize-series'),
            ],
            'previous_image_position' => [
                'label'    => __('Image Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'organize-series'),
                    'right' => __('Right', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to the navigation', 'organize-series'),
            ],
            'previous_image_width' => [
                'label'    => __('Image Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'organize-series'),
            ],
            'previous_image_height' => [
                'label'    => __('Image Height (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'organize-series'),
            ],
            'previous_show_arrow' => [
                'label'    => __('Show Arrow', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display an arrow icon with the previous navigation', 'organize-series'),
            ],
            'previous_arrow_type' => [
                'label'    => __('Arrow Type', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'chevron_left'     => __('Chevron Left (‹)', 'organize-series'),
                    'arrow_left'       => __('Arrow Left (←)', 'organize-series'),
                    'double_left'      => __('Double Chevron Left («)', 'organize-series'),
                    'triangle_left'    => __('Triangle Left (◄)', 'organize-series'),
                    'custom'           => __('Custom Image', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'chevron_left',
                'description' => __('Choose the arrow icon style', 'organize-series'),
            ],
            'previous_arrow_position' => [
                'label'    => __('Arrow Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'organize-series'),
                    'right' => __('Right', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the arrow relative to the navigation', 'organize-series'),
            ],
            'previous_arrow_size' => [
                'label'    => __('Arrow Size (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 16,
                'min'      => 8,
                'max'      => 64,
                'description' => __('Size of the arrow icon in pixels (applies to both width and height)', 'organize-series'),
            ],
            'previous_custom_arrow_image' => [
                'label'    => __('Custom Arrow Image', 'organize-series'),
                'type'     => 'media',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Select custom arrow image from media library', 'organize-series'),
            ],
            'next_separator' => [
                'type'  => 'category_separator',
                'label' => __('Next', 'organize-series'),
                'tab'   => 'item',
            ],
            'next_link_type' => [
                'label'    => __('Next Navigation Type', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'organize-series'),
                    'post_title'  => __('Next Post Title', 'organize-series'),
                    'custom'      => __('Custom Label', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'custom',
                'description' => __('Choose how to display the next post link', 'organize-series'),
            ],
            'next_label' => [
                'label'    => __('Next Label', 'organize-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Next', 'organize-series'),
                'description' => __('Custom text for next post link', 'organize-series'),
            ],
            'next_show_featured_image' => [
                'label'    => __('Show Featured Image', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for next post', 'organize-series'),
            ],
            'next_image_position' => [
                'label'    => __('Image Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'organize-series'),
                    'right' => __('Right', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to navigation', 'organize-series'),
            ],
            'next_image_width' => [
                'label'    => __('Image Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'organize-series'),
            ],
            'next_image_height' => [
                'label'    => __('Image Height (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'organize-series'),
            ],
            'next_show_arrow' => [
                'label'    => __('Show Arrow', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display an arrow icon with the next navigation', 'organize-series'),
            ],
            'next_arrow_type' => [
                'label'    => __('Arrow Type', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'chevron_right'    => __('Chevron Right (›)', 'organize-series'),
                    'arrow_right'      => __('Arrow Right (→)', 'organize-series'),
                    'double_right'     => __('Double Chevron Right (»)', 'organize-series'),
                    'triangle_right'   => __('Triangle Right (►)', 'organize-series'),
                    'custom'           => __('Custom Image', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'chevron_right',
                'description' => __('Choose the arrow icon style', 'organize-series'),
            ],
            'next_arrow_position' => [
                'label'    => __('Arrow Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left of Text', 'organize-series'),
                    'right' => __('Right of Text', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'right',
                'description' => __('Position of the arrow relative to the link text', 'organize-series'),
            ],
            'next_arrow_size' => [
                'label'    => __('Arrow Size (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 16,
                'min'      => 8,
                'max'      => 64,
                'description' => __('Size of the arrow icon in pixels (applies to both width and height)', 'organize-series'),
            ],
            'next_custom_arrow_image' => [
                'label'    => __('Custom Arrow Image', 'organize-series'),
                'type'     => 'media',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Select custom arrow image from media library', 'organize-series'),
            ],
            'first_separator' => [
                'type'  => 'category_separator',
                'label' => __('First', 'organize-series'),
                'tab'   => 'item',
            ],
            'first_link_type' => [
                'label'    => __('First Navigation Type', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'organize-series'),
                    'post_title'  => __('First Post Title', 'organize-series'),
                    'custom'      => __('Custom Label', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'none',
                'description' => __('Choose how to display the first navigation', 'organize-series'),
            ],
            'first_label' => [
                'label'    => __('First Label', 'organize-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Go to First', 'organize-series'),
                'description' => __('Custom text for first navigation', 'organize-series'),
            ],
            'first_link_position' => [
                'label'    => __('First Navigation Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'   => __('Left (First → Previous → Next)', 'organize-series'),
                    'center' => __('Center (Previous → First → Next)', 'organize-series'),
                    'right'  => __('Right (Previous → Next → First)', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'right',
                'description' => __('Position of the first/series home link in navigation', 'organize-series'),
            ],
            'first_show_featured_image' => [
                'label'    => __('Show Featured Image', 'organize-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for first post', 'organize-series'),
            ],
            'first_image_position' => [
                'label'    => __('Image Position', 'organize-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'organize-series'),
                    'right' => __('Right', 'organize-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to the link text', 'organize-series'),
            ],
            'first_image_width' => [
                'label'    => __('Image Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'organize-series'),
            ],
            'first_image_height' => [
                'label'    => __('Image Height (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'organize-series'),
            ],
            'typography_separator' => [
                'type'  => 'category_separator',
                'label' => __('Color', 'organize-series'),
                'tab'   => 'item',
            ],
            'link_color' => [
                'label'    => __('Text Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#0073aa',
            ],
            'link_background_color' => [
                'label'    => __('Background Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '',
            ],
            'border_separator' => [
                'type'  => 'category_separator',
                'label' => __('Border', 'organize-series'),
                'tab'   => 'item',
            ],
            'border_color' => [
                'label'    => __('Border Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#dddddd',
            ],
            'border_width' => [
                'label'    => __('Border Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 10,
                'sanitize' => 'absint',
                'default'  => 1,
            ],
            'border_radius' => [
                'label'    => __('Border Radius (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 60,
                'sanitize' => 'absint',
                'default'  => 4,
            ],
            'spacing_separator' => [
                'type'  => 'category_separator',
                'label' => __('Spacing', 'organize-series'),
                'tab'   => 'item',
            ],
            'padding' => [
                'label'    => __('Padding (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 8,
            ],
            'margin' => [
                'label'    => __('Margin (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'gap_between_links' => [
                'label'    => __('Gap Between Links (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 10,
                'description' => __('Space between navigation links', 'organize-series'),
            ],
        ];
    }

    /**
     * Layout tab fields
     */
    private static function get_layout_fields()
    {
        return [
            'container_border_separator' => [
                'type'  => 'category_separator',
                'label' => __('Container Style', 'organize-series'),
                'tab'   => 'layout',
            ],
            'container_background_color' => [
                'label'    => __('Container Background Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'layout',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '',
                'description' => __('Background color for the navigation container', 'organize-series'),
            ],
            'container_border_color' => [
                'label'    => __('Container Border Color', 'organize-series'),
                'type'     => 'color',
                'tab'      => 'layout',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#dddddd',
            ],
            'container_border_width' => [
                'label'    => __('Container Border Width (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 10,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'container_border_radius' => [
                'label'    => __('Container Border Radius (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 60,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'container_padding' => [
                'label'    => __('Container Padding (px)', 'organize-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Inner spacing for the container', 'organize-series'),
            ],
        ];
    }

}

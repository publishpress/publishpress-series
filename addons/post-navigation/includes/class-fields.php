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
                'label' => __('General', 'publishpress-series'),
                'icon'  => 'dashicons-admin-generic',
            ],
            'item' => [
                'label' => __('Item', 'publishpress-series'),
                'icon'  => 'dashicons-ellipsis',
            ],
            'layout' => [
                'label' => __('Layout', 'publishpress-series'),
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
                'label' => __('General', 'publishpress-series'),
                'tab'   => 'general',
            ],
            'include_series_title' => [
                'label'    => __('Include Series Title', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'series_title_alignment' => [
                'label'    => __('Series Title Alignment', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'general',
                'options'  => [
                    'left'   => __('Left', 'publishpress-series'),
                    'center' => __('Center', 'publishpress-series'),
                    'right'  => __('Right', 'publishpress-series'),
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
                'label'    => __('Hide When Single Post', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'general',
                'sanitize' => 'absint',
                'default'  => 1,
                'description' => __('Do not display navigation if series has only one post', 'publishpress-series'),
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
                'label' => __('Previous', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'previous_link_type' => [
                'label'    => __('Previous Navigation Type', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'publishpress-series'),
                    'post_title'  => __('Previous Post Title', 'publishpress-series'),
                    'custom'      => __('Custom Label', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'custom',
                'description' => __('Choose how to display the previous navigation', 'publishpress-series'),
            ],
            'previous_label' => [
                'label'    => __('Previous Label', 'publishpress-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Previous', 'publishpress-series'),
                'description' => __('Custom text for previous post link', 'publishpress-series'),
            ],
            'previous_show_featured_image' => [
                'label'    => __('Show Featured Image', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for previous post', 'publishpress-series'),
            ],
            'previous_image_position' => [
                'label'    => __('Image Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'publishpress-series'),
                    'right' => __('Right', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to the navigation', 'publishpress-series'),
            ],
            'previous_image_width' => [
                'label'    => __('Image Width (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'publishpress-series'),
            ],
            'previous_image_height' => [
                'label'    => __('Image Height (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'publishpress-series'),
            ],
            'previous_show_arrow' => [
                'label'    => __('Show Arrow', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display an arrow icon with the previous navigation', 'publishpress-series'),
            ],
            'previous_arrow_type' => [
                'label'    => __('Arrow Type', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'chevron_left'     => __('Chevron Left (‹)', 'publishpress-series'),
                    'arrow_left'       => __('Arrow Left (←)', 'publishpress-series'),
                    'double_left'      => __('Double Chevron Left («)', 'publishpress-series'),
                    'triangle_left'    => __('Triangle Left (◄)', 'publishpress-series'),
                    'custom'           => __('Custom Image', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'chevron_left',
                'description' => __('Choose the arrow icon style', 'publishpress-series'),
            ],
            'previous_arrow_position' => [
                'label'    => __('Arrow Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'publishpress-series'),
                    'right' => __('Right', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the arrow relative to the navigation', 'publishpress-series'),
            ],
            'previous_arrow_size' => [
                'label'    => __('Arrow Size (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 16,
                'min'      => 8,
                'max'      => 64,
                'description' => __('Size of the arrow icon in pixels (applies to both width and height)', 'publishpress-series'),
            ],
            'previous_custom_arrow_image' => [
                'label'    => __('Custom Arrow Image', 'publishpress-series'),
                'type'     => 'media',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Select custom arrow image from media library', 'publishpress-series'),
            ],
            'next_separator' => [
                'type'  => 'category_separator',
                'label' => __('Next', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'next_link_type' => [
                'label'    => __('Next Navigation Type', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'publishpress-series'),
                    'post_title'  => __('Next Post Title', 'publishpress-series'),
                    'custom'      => __('Custom Label', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'custom',
                'description' => __('Choose how to display the next post link', 'publishpress-series'),
            ],
            'next_label' => [
                'label'    => __('Next Label', 'publishpress-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Next', 'publishpress-series'),
                'description' => __('Custom text for next post link', 'publishpress-series'),
            ],
            'next_show_featured_image' => [
                'label'    => __('Show Featured Image', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for next post', 'publishpress-series'),
            ],
            'next_image_position' => [
                'label'    => __('Image Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'publishpress-series'),
                    'right' => __('Right', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to navigation', 'publishpress-series'),
            ],
            'next_image_width' => [
                'label'    => __('Image Width (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'publishpress-series'),
            ],
            'next_image_height' => [
                'label'    => __('Image Height (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'publishpress-series'),
            ],
            'next_show_arrow' => [
                'label'    => __('Show Arrow', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display an arrow icon with the next navigation', 'publishpress-series'),
            ],
            'next_arrow_type' => [
                'label'    => __('Arrow Type', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'chevron_right'    => __('Chevron Right (›)', 'publishpress-series'),
                    'arrow_right'      => __('Arrow Right (→)', 'publishpress-series'),
                    'double_right'     => __('Double Chevron Right (»)', 'publishpress-series'),
                    'triangle_right'   => __('Triangle Right (►)', 'publishpress-series'),
                    'custom'           => __('Custom Image', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'chevron_right',
                'description' => __('Choose the arrow icon style', 'publishpress-series'),
            ],
            'next_arrow_position' => [
                'label'    => __('Arrow Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left of Text', 'publishpress-series'),
                    'right' => __('Right of Text', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'right',
                'description' => __('Position of the arrow relative to the link text', 'publishpress-series'),
            ],
            'next_arrow_size' => [
                'label'    => __('Arrow Size (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 16,
                'min'      => 8,
                'max'      => 64,
                'description' => __('Size of the arrow icon in pixels (applies to both width and height)', 'publishpress-series'),
            ],
            'next_custom_arrow_image' => [
                'label'    => __('Custom Arrow Image', 'publishpress-series'),
                'type'     => 'media',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Select custom arrow image from media library', 'publishpress-series'),
            ],
            'first_separator' => [
                'type'  => 'category_separator',
                'label' => __('First', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'first_link_type' => [
                'label'    => __('First Navigation Type', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'none'        => __('None', 'publishpress-series'),
                    'post_title'  => __('First Post Title', 'publishpress-series'),
                    'custom'      => __('Custom Label', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'none',
                'description' => __('Choose how to display the first navigation', 'publishpress-series'),
            ],
            'first_label' => [
                'label'    => __('First Label', 'publishpress-series'),
                'type'     => 'text',
                'tab'      => 'item',
                'sanitize' => 'sanitize_text_field',
                'default'  => __('Go to First', 'publishpress-series'),
                'description' => __('Custom text for first navigation', 'publishpress-series'),
            ],
            'first_link_position' => [
                'label'    => __('First Navigation Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'   => __('Left (First → Previous → Next)', 'publishpress-series'),
                    'center' => __('Center (Previous → First → Next)', 'publishpress-series'),
                    'right'  => __('Right (Previous → Next → First)', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'right',
                'description' => __('Position of the first/series home link in navigation', 'publishpress-series'),
            ],
            'first_show_featured_image' => [
                'label'    => __('Show Featured Image', 'publishpress-series'),
                'type'     => 'checkbox',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Display featured image for first post', 'publishpress-series'),
            ],
            'first_image_position' => [
                'label'    => __('Image Position', 'publishpress-series'),
                'type'     => 'select',
                'tab'      => 'item',
                'options'  => [
                    'left'  => __('Left', 'publishpress-series'),
                    'right' => __('Right', 'publishpress-series'),
                ],
                'sanitize' => 'sanitize_text_field',
                'default'  => 'left',
                'description' => __('Position of the featured image relative to the link text', 'publishpress-series'),
            ],
            'first_image_width' => [
                'label'    => __('Image Width (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Width of the featured image in pixels', 'publishpress-series'),
            ],
            'first_image_height' => [
                'label'    => __('Image Height (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'sanitize' => 'absint',
                'default'  => 80,
                'description' => __('Height of the featured image in pixels', 'publishpress-series'),
            ],
            'typography_separator' => [
                'type'  => 'category_separator',
                'label' => __('Color', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'link_color' => [
                'label'    => __('Text Color', 'publishpress-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#0073aa',
            ],
            'link_background_color' => [
                'label'    => __('Background Color', 'publishpress-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '',
            ],
            'border_separator' => [
                'type'  => 'category_separator',
                'label' => __('Border', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'border_color' => [
                'label'    => __('Border Color', 'publishpress-series'),
                'type'     => 'color',
                'tab'      => 'item',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#dddddd',
            ],
            'border_width' => [
                'label'    => __('Border Width (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 10,
                'sanitize' => 'absint',
                'default'  => 1,
            ],
            'border_radius' => [
                'label'    => __('Border Radius (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 60,
                'sanitize' => 'absint',
                'default'  => 4,
            ],
            'spacing_separator' => [
                'type'  => 'category_separator',
                'label' => __('Spacing', 'publishpress-series'),
                'tab'   => 'item',
            ],
            'padding' => [
                'label'    => __('Padding (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 8,
            ],
            'margin' => [
                'label'    => __('Margin (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'gap_between_links' => [
                'label'    => __('Gap Between Links (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'item',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 10,
                'description' => __('Space between navigation links', 'publishpress-series'),
            ],
        ];
    }

    /**
     * Layout tab fields
     */
    private static function get_layout_fields()
    {
        return [
            'layout_separator' => [
                'type'  => 'category_separator',
                'label' => __('Layout Options', 'publishpress-series'),
                'tab'   => 'layout',
            ],
            'separator_text' => [
                'label'    => __('Navigation Separator', 'publishpress-series'),
                'type'     => 'text',
                'tab'      => 'layout',
                'sanitize' => 'sanitize_text_field',
                'default'  => '|',
                'description' => __('Text to display between navigation links (e.g., |, •, →)', 'publishpress-series'),
            ],
            'container_border_separator' => [
                'type'  => 'category_separator',
                'label' => __('Container Style', 'publishpress-series'),
                'tab'   => 'layout',
            ],
            'container_background_color' => [
                'label'    => __('Container Background Color', 'publishpress-series'),
                'type'     => 'color',
                'tab'      => 'layout',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '',
                'description' => __('Background color for the navigation container', 'publishpress-series'),
            ],
            'container_border_color' => [
                'label'    => __('Container Border Color', 'publishpress-series'),
                'type'     => 'color',
                'tab'      => 'layout',
                'sanitize' => 'sanitize_hex_color',
                'default'  => '#dddddd',
            ],
            'container_border_width' => [
                'label'    => __('Container Border Width (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 10,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'container_border_radius' => [
                'label'    => __('Container Border Radius (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 60,
                'sanitize' => 'absint',
                'default'  => 0,
            ],
            'container_padding' => [
                'label'    => __('Container Padding (px)', 'publishpress-series'),
                'type'     => 'number',
                'tab'      => 'layout',
                'min'      => 0,
                'max'      => 100,
                'sanitize' => 'absint',
                'default'  => 0,
                'description' => __('Inner spacing for the container', 'publishpress-series'),
            ],
        ];
    }

}

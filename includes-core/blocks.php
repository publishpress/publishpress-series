<?php

/**
 * Gutenberg blocks for PublishPress Series.
 */

namespace PublishPress\Series;

if (! defined('ABSPATH')) {
    exit;
}

if (class_exists('PublishPress\\Series\\Blocks')) {
    return;
}

class Blocks
{
    public const CATEGORY = 'publishpress-series';
    public const SCRIPT_HANDLE = 'publishpress-series-blocks';

    /**
     * Register the block category and block registration callback.
     *
     * @return void
     */
    public static function init()
    {
        \add_filter('block_categories_all', [__CLASS__, 'addCategory'], 10, 2);
        \add_filter('block_categories', [__CLASS__, 'addCategory'], 10, 2);
        \add_action('init', [__CLASS__, 'registerBlocks'], 20);
        \add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueueEditorAssets']);
    }

    /**
     * Add the PublishPress Series section to the block inserter.
     *
     * @param array       $categories Existing block categories.
     * @param object|null $context     Block editor context.
     *
     * @return array
     */
    public static function addCategory($categories, $context = null)
    {
        foreach ($categories as $category) {
            if (isset($category['slug']) && self::CATEGORY === $category['slug']) {
                return $categories;
            }
        }

        $categories[] = [
            'slug'  => self::CATEGORY,
            'title' => \__('PublishPress Series', 'organize-series'),
        ];

        return $categories;
    }

    /**
     * Register the three dynamic blocks and their editor script.
     *
     * @return void
     */
    public static function registerBlocks()
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        $script_url = defined('PPSERIES_URL')
            ? PPSERIES_URL . 'assets/js/publishpress-series-blocks.js'
            : \plugins_url('assets/js/publishpress-series-blocks.js', dirname(__FILE__));

        \wp_register_script(
            self::SCRIPT_HANDLE,
            $script_url,
            [
                'wp-blocks',
                'wp-element',
                'wp-components',
                'wp-block-editor',
                'wp-server-side-render',
                'wp-i18n',
            ],
            defined('ORG_SERIES_VERSION') ? ORG_SERIES_VERSION : false,
            true
        );

        \wp_localize_script(
            self::SCRIPT_HANDLE,
            'publishPressSeriesBlocks',
            [
                'layouts' => [
                    'postListBox'     => self::getLayoutOptions('pps_post_list_box'),
                    'postDetails'     => self::getLayoutOptions('pps_post_details'),
                    'postNavigation' => self::getLayoutOptions('pps_post_navigation'),
                ],
                'series'  => self::getSeriesOptions(),
            ]
        );

        $common = [
            'category'      => self::CATEGORY,
            'editor_script' => self::SCRIPT_HANDLE,
            'attributes'    => [
                'layoutId' => [
                    'type'    => 'integer',
                    'default' => 0,
                ],
                'seriesId' => [
                    'type'    => 'integer',
                    'default' => 0,
                ],
            ],
            'supports'       => [
                'html' => false,
            ],
        ];

        $blocks = [
            'publishpress-series/post-list-box' => [
                'title'       => \__('Post List Boxes', 'organize-series'),
                'description' => \__('Display a styled list of posts from a Series.', 'organize-series'),
                'icon'        => 'list-view',
                'render_callback' => [__CLASS__, 'renderPostListBox'],
            ],
            'publishpress-series/post-details' => [
                'title'       => \__('Post Details', 'organize-series'),
                'description' => \__('Display the current post details from a Series.', 'organize-series'),
                'icon'        => 'editor-help',
                'render_callback' => [__CLASS__, 'renderPostDetails'],
            ],
            'publishpress-series/post-navigation' => [
                'title'       => \__('Post Navigation', 'organize-series'),
                'description' => \__('Display navigation links for posts in a Series.', 'organize-series'),
                'icon'        => 'menu',
                'render_callback' => [__CLASS__, 'renderPostNavigation'],
            ],
        ];

        foreach ($blocks as $name => $settings) {
            $block_settings = array_merge($common, $settings);

            \register_block_type($name, $block_settings);
        }
    }

    /**
     * Enqueue the block editor script on WordPress versions that do not
     * automatically enqueue scripts from directly registered block types.
     *
     * @return void
     */
    public static function enqueueEditorAssets()
    {
        \wp_enqueue_script(self::SCRIPT_HANDLE);
    }

    /**
     * Render any of the registered Series blocks.
     *
     * @param array       $attributes Block attributes.
     * @param string       $content    Inner block content.
     * @param WP_Block|null $block     Block instance.
     *
     * @return string
     */
    public static function renderPostListBox($attributes, $content = '', $block = null)
    {
        return self::renderFeature('publishpress-series/post-list-box', $attributes);
    }

    /**
     * Render a Post Details block.
     *
     * @param array        $attributes Block attributes.
     * @param string       $content    Inner block content.
     * @param \WP_Block|null $block     Block instance.
     *
     * @return string
     */
    public static function renderPostDetails($attributes, $content = '', $block = null)
    {
        return self::renderFeature('publishpress-series/post-details', $attributes);
    }

    /**
     * Render a Post Navigation block.
     *
     * @param array        $attributes Block attributes.
     * @param string       $content    Inner block content.
     * @param \WP_Block|null $block     Block instance.
     *
     * @return string
     */
    public static function renderPostNavigation($attributes, $content = '', $block = null)
    {
        return self::renderFeature('publishpress-series/post-navigation', $attributes);
    }

    /**
     * Render a Series feature block using its existing shortcode.
     *
     * @param string $block_name Block name.
     * @param array  $attributes Block attributes.
     *
     * @return string
     */
    private static function renderFeature($block_name, $attributes)
    {
        $definitions = [
            'publishpress-series/post-list-box' => [
                'postType'      => 'pps_post_list_box',
                'defaultMethod' => ['PPS_Post_List_Box_Utilities', 'get_default_post_list_box_id'],
                'shortcode'     => 'pps_post_list_box',
                'layoutPrefix'  => 'pps_post_list_box_',
            ],
            'publishpress-series/post-details' => [
                'postType'      => 'pps_post_details',
                'defaultMethod' => ['PPS_Series_Post_Details_Utilities', 'get_default_series_post_details_id'],
                'shortcode'     => 'pps_post_details',
                'layoutPrefix'  => 'pps_meta_box_',
            ],
            'publishpress-series/post-navigation' => [
                'postType'      => 'pps_post_navigation',
                'defaultMethod' => ['PPS_Series_Post_Navigation_Utilities', 'get_default_post_navigation_id'],
                'shortcode'     => 'pps_post_navigation',
                'layoutPrefix'  => 'pps_nav_',
            ],
        ];

        if (! isset($definitions[$block_name])) {
            return '';
        }

        $definition = $definitions[$block_name];
        $layout_id  = self::getValidLayoutId($attributes, $definition);

        if (! $layout_id || ! \shortcode_exists($definition['shortcode'])) {
            return '';
        }

        $series_id = isset($attributes['seriesId']) ? \absint($attributes['seriesId']) : 0;
        $series    = self::getSeriesSlug($series_id);

        // An explicitly selected invalid series must not fall back to the current series.
        if ($series_id && '' === $series) {
            return '';
        }

        $shortcode = sprintf(
            '[%1$s layout="%2$s"%3$s]',
            $definition['shortcode'],
            \esc_attr($definition['layoutPrefix'] . $layout_id),
            $series ? ' series="' . \esc_attr($series) . '"' : ''
        );

        return \do_shortcode($shortcode);
    }

    /**
     * Get published layout choices for the block editor.
     *
     * @param string $post_type Layout post type.
     *
     * @return array
     */
    private static function getLayoutOptions($post_type)
    {
        $options = [
            [
                'label' => \__('Default layout', 'organize-series'),
                'value' => '0',
            ],
        ];

        $layouts = \get_posts([
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'numberposts'    => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        foreach ($layouts as $layout) {
            $options[] = [
                'label' => \wp_strip_all_tags($layout->post_title),
                'value' => (string) $layout->ID,
            ];
        }

        return $options;
    }

    /**
     * Get series choices for the block editor.
     *
     * @return array
     */
    private static function getSeriesOptions()
    {
        $options = [
            [
                'label' => \__('Current series', 'organize-series'),
                'value' => '0',
            ],
        ];

        $taxonomy = function_exists('ppseries_get_series_slug')
            ? \ppseries_get_series_slug()
            : 'series';
        $series = \get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'orderby'    => 'name',
            'order'      => 'ASC',
        ]);

        if (\is_wp_error($series)) {
            return $options;
        }

        foreach ($series as $term) {
            $options[] = [
                'label' => \wp_strip_all_tags($term->name),
                'value' => (string) $term->term_id,
            ];
        }

        return $options;
    }

    /**
     * Resolve and validate the requested layout.
     *
     * @param array $attributes Block attributes.
     * @param array $definition Block definition.
     *
     * @return int
     */
    private static function getValidLayoutId($attributes, $definition)
    {
        $layout_id = isset($attributes['layoutId']) ? \absint($attributes['layoutId']) : 0;
        if ($layout_id) {
            $layout = \get_post($layout_id);
            if ($layout && $definition['postType'] === $layout->post_type && 'publish' === $layout->post_status) {
                return $layout_id;
            }

            return 0;
        }

        if (is_callable($definition['defaultMethod'])) {
            $default_id = call_user_func($definition['defaultMethod']);
            if ($default_id) {
                $layout = \get_post((int) $default_id);
                if ($layout && $definition['postType'] === $layout->post_type && 'publish' === $layout->post_status) {
                    return (int) $default_id;
                }
            }
        }

        return 0;
    }

    /**
     * Resolve a selected series ID to its safe slug.
     *
     * @param int $series_id Series term ID.
     *
     * @return string
     */
    private static function getSeriesSlug($series_id)
    {
        if (! $series_id) {
            return '';
        }

        $taxonomy = function_exists('ppseries_get_series_slug')
            ? \ppseries_get_series_slug()
            : 'series';
        $term = \get_term($series_id, $taxonomy);

        if (! $term || \is_wp_error($term)) {
            return '';
        }

        return \sanitize_title($term->slug);
    }
}

Blocks::init();

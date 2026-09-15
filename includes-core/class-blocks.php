<?php

/**
 * Gutenberg blocks for PublishPress Series.
 */

namespace PublishPress\Series;

if (! defined('ABSPATH')) {
    exit;
}

class Blocks
{
    public const CATEGORY = 'publishpress-series';
    public const SCRIPT_HANDLE = 'publishpress-series-blocks';
    public const STYLE_HANDLE = 'publishpress-series-blocks';

    /**
     * Register the block category and block registration callback.
     *
     * @return void
     */
    public static function init()
    {
        global $wp_version;

        $category_filter = version_compare($wp_version, '5.8', '>=')
            ? 'block_categories_all'
            : 'block_categories';

        \add_filter($category_filter, [__CLASS__, 'addCategory'], 10, 2);
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
     * Register the dynamic blocks and their editor assets.
     *
     * @return void
     */
    public static function registerBlocks()
    {
        if (! function_exists('register_block_type')) {
            return;
        }

        $plugin_file = dirname(__DIR__) . '/orgSeries.php';
        $script_url = \plugins_url('assets/js/publishpress-series-blocks.js', $plugin_file);
        $style_url = \plugins_url('assets/css/publishpress-series-blocks.css', $plugin_file);

        \wp_register_script(
            self::SCRIPT_HANDLE,
            $script_url,
            [
                'wp-blocks',
                'wp-element',
                'wp-components',
                'wp-block-editor',
                'wp-server-side-render',
                'wp-data',
                'wp-i18n',
            ],
            defined('ORG_SERIES_VERSION') ? ORG_SERIES_VERSION : false,
            true
        );

        \wp_register_style(
            self::STYLE_HANDLE,
            $style_url,
            [],
            defined('ORG_SERIES_VERSION') ? ORG_SERIES_VERSION : false
        );

        $feature_styles = [
            'pps-post-list-box-frontend' => 'addons/post-list-box/assets/css/post-list-box-frontend.css',
            'pps-series-post-details-frontend' => 'addons/post-details/assets/css/series-post-details-frontend.css',
            'pps-series-post-navigation-frontend' => 'addons/post-navigation/assets/css/post-navigation-frontend.css',
        ];
        foreach ($feature_styles as $handle => $path) {
            \wp_register_style(
                $handle,
                \plugins_url($path, $plugin_file),
                'pps-series-post-navigation-frontend' === $handle ? ['dashicons'] : [],
                defined('ORG_SERIES_VERSION') ? ORG_SERIES_VERSION : false
            );
        }

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
            'uses_context'   => [
                'postId',
                'postType',
            ],
        ];

        $blocks = [
            'publishpress-series/post-list-box' => [
                'title'       => \__('Post List Boxes', 'organize-series'),
                'description' => \__('Display a styled list of posts from a Series.', 'organize-series'),
                'icon'        => 'list-view',
                'style'       => 'pps-post-list-box-frontend',
                'render_callback' => [__CLASS__, 'renderPostListBox'],
            ],
            'publishpress-series/post-details' => [
                'title'       => \__('Post Details', 'organize-series'),
                'description' => \__('Display the current post details from a Series.', 'organize-series'),
                'icon'        => 'editor-help',
                'style'       => 'pps-series-post-details-frontend',
                'render_callback' => [__CLASS__, 'renderPostDetails'],
            ],
            'publishpress-series/post-navigation' => [
                'title'       => \__('Post Navigation', 'organize-series'),
                'description' => \__('Display navigation links for posts in a Series.', 'organize-series'),
                'icon'        => 'menu',
                'style'       => 'pps-series-post-navigation-frontend',
                'render_callback' => [__CLASS__, 'renderPostNavigation'],
            ],
            'publishpress-series/series-list' => [
                'title'       => \__('Series List', 'organize-series'),
                'description' => \__('Display a customizable list of Series or Series Categories.', 'organize-series'),
                'icon'        => 'screenoptions',
                'style'       => self::STYLE_HANDLE,
                'editor_style' => self::STYLE_HANDLE,
                'attributes'  => [
                    'contentType' => [
                        'type'    => 'string',
                        'default' => 'series',
                    ],
                    'view' => [
                        'type'    => 'string',
                        'default' => 'grid',
                    ],
                    'columns' => [
                        'type'    => 'integer',
                        'default' => 3,
                    ],
                    'numberOfItems' => [
                        'type'    => 'integer',
                        'default' => 6,
                    ],
                    'orderBy' => [
                        'type'    => 'string',
                        'default' => 'name',
                    ],
                    'order' => [
                        'type'    => 'string',
                        'default' => 'asc',
                    ],
                    'hideEmpty' => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'showIcon' => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'showDescription' => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                    'showPostCount' => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
                ],
                'render_callback' => [__CLASS__, 'renderSeriesList'],
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
        \wp_enqueue_style(self::STYLE_HANDLE);

        if (is_callable(['PostListBoxRenderer', 'enqueue_frontend_styles'])) {
            \PostListBoxRenderer::enqueue_frontend_styles();
        }

        if (is_callable(['SeriesPostDetailsRenderer', 'enqueue_frontend_assets'])) {
            \SeriesPostDetailsRenderer::enqueue_frontend_assets();
        }

        if (is_callable(['PostNavigationRenderer', 'enqueue_frontend_assets'])) {
            \PostNavigationRenderer::enqueue_frontend_assets();
        }
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
        return self::renderFeature('publishpress-series/post-list-box', $attributes, $block);
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
        return self::renderFeature('publishpress-series/post-details', $attributes, $block);
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
        return self::renderFeature('publishpress-series/post-navigation', $attributes, $block);
    }

    /**
     * Render a list of Series or Series Category terms.
     *
     * @param array         $attributes Block attributes.
     * @param string        $content    Inner block content.
     * @param \WP_Block|null $block      Block instance.
     *
     * @return string
     */
    public static function renderSeriesList($attributes, $content = '', $block = null)
    {
        $views = ['grid', 'list'];
        $order_by_options = ['name', 'count', 'term_id'];
        $content_types = ['series', 'series_group'];
        $content_type = isset($attributes['contentType']) && in_array($attributes['contentType'], $content_types, true)
            ? $attributes['contentType']
            : 'series';
        $is_series_category = 'series_group' === $content_type;
        $view = isset($attributes['view']) && in_array($attributes['view'], $views, true)
            ? $attributes['view']
            : 'grid';
        $columns = isset($attributes['columns']) ? \absint($attributes['columns']) : 3;
        $columns = max(1, min(4, $columns));
        $number = isset($attributes['numberOfItems']) ? \absint($attributes['numberOfItems']) : 6;
        $number = max(1, min(50, $number));
        $order_by = isset($attributes['orderBy']) && in_array($attributes['orderBy'], $order_by_options, true)
            ? $attributes['orderBy']
            : 'name';
        $order = isset($attributes['order']) && 'desc' === strtolower($attributes['order'])
            ? 'DESC'
            : 'ASC';
        $hide_empty = ! isset($attributes['hideEmpty']) || (bool) $attributes['hideEmpty'];
        $show_icon = ! isset($attributes['showIcon']) || (bool) $attributes['showIcon'];
        $show_description = ! isset($attributes['showDescription']) || (bool) $attributes['showDescription'];
        $show_post_count = ! isset($attributes['showPostCount']) || (bool) $attributes['showPostCount'];

        if ($is_series_category) {
            $taxonomy = 'series_group';
        } else {
            $taxonomy = function_exists('ppseries_get_series_slug')
                ? \ppseries_get_series_slug()
                : 'series';
        }

        $query_args = [
            'taxonomy'          => $taxonomy,
            'hide_empty'        => $hide_empty,
            'number'            => $number,
            'orderby'           => $order_by,
            'order'             => $order,
            'ignore_term_order' => true,
        ];

        /**
         * Filter the Series List block term query.
         *
         * @param array  $query_args   Term query arguments.
         * @param array  $attributes   Block attributes.
         * @param string $taxonomy     Taxonomy queried by the block.
         * @param string $content_type Block content type.
         */
        $query_args = \apply_filters(
            'publishpress_series_list_block_query_args',
            $query_args,
            $attributes,
            $taxonomy,
            $content_type
        );
        $terms = \get_terms($query_args);

        if (\is_wp_error($terms)) {
            return '';
        }

        if (empty($terms) && self::isEditorPreview()) {
            $terms = self::sortSeriesListTerms(
                self::getSampleSeriesListTerms($content_type),
                $order_by,
                $order
            );
        }

        if (empty($terms)) {
            return '';
        }

        \wp_enqueue_style(self::STYLE_HANDLE);

        $items = '';
        foreach ($terms as $term) {
            $term_id = isset($term->term_id) ? \absint($term->term_id) : 0;
            $term_name = isset($term->name) ? (string) $term->name : '';
            $term_count = isset($term->count) ? \absint($term->count) : 0;
            $term_description = isset($term->description) ? (string) $term->description : '';
            $is_sample = ! empty($term->is_sample);
            $term_link = $term_id && ! $is_sample ? \get_term_link($term, $taxonomy) : '#';

            if (\is_wp_error($term_link)) {
                $term_link = '#';
            }

            $icon = '';
            if (! $is_series_category && $show_icon && $term_id && function_exists('get_series_icon')) {
                $icon = \get_series_icon(
                    'fit_width=640&fit_height=360&link=0&display=0&series=' . $term_id
                );
            }

            $item = '<li class="pps-series-list__item">';
            if ($icon) {
                $item .= '<div class="pps-series-list__icon"><a href="' . \esc_url($term_link) . '">';
                $item .= \wp_kses_post($icon);
                $item .= '</a></div>';
            }

            $item .= '<div class="pps-series-list__content">';
            $item .= '<h3 class="pps-series-list__title"><a href="' . \esc_url($term_link) . '">';
            $item .= \esc_html($term_name);
            $item .= '</a></h3>';

            if ($show_description && '' !== trim(\wp_strip_all_tags($term_description))) {
                $item .= '<div class="pps-series-list__description">';
                $item .= \wp_kses_post(\wpautop($term_description));
                $item .= '</div>';
            }

            if ($show_post_count) {
                if ($is_series_category) {
                    $count_label = sprintf(
                        /* translators: %s: Number of series. */
                        \_n('%s series', '%s series', $term_count, 'organize-series'),
                        \number_format_i18n($term_count)
                    );
                } else {
                    $count_label = sprintf(
                        /* translators: %s: Number of posts. */
                        \_n('%s post', '%s posts', $term_count, 'organize-series'),
                        \number_format_i18n($term_count)
                    );
                }
                $item .= '<div class="pps-series-list__count">' . \esc_html($count_label) . '</div>';
            }

            $item .= '</div></li>';
            $items .= $item;
        }

        $classes = [
            'pps-series-list',
            'pps-series-list--type-' . \sanitize_html_class($content_type),
            'pps-series-list--' . $view,
            'pps-series-list--columns-' . $columns,
        ];
        $output = '<div class="' . \esc_attr(implode(' ', $classes)) . '">';
        $output .= '<ul class="pps-series-list__items">' . $items . '</ul></div>';

        /**
         * Filter the rendered Series List block.
         *
         * @param string $output       Rendered block HTML.
         * @param array  $terms        Terms included in the block.
         * @param array  $attributes   Block attributes.
         * @param string $taxonomy     Taxonomy queried by the block.
         * @param string $content_type Block content type.
         */
        return (string) \apply_filters(
            'publishpress_series_list_block_output',
            $output,
            $terms,
            $attributes,
            $taxonomy,
            $content_type
        );
    }

    /**
     * Get in-memory terms for editor previews on empty sites.
     *
     * @param string $content_type Block content type.
     *
     * @return array
     */
    private static function getSampleSeriesListTerms($content_type = 'series')
    {
        if ('series_group' === $content_type) {
            return [
                (object) [
                    'term_id'    => 1,
                    'name'       => \__('Editorial Series', 'organize-series'),
                    'slug'       => 'editorial-series',
                    'description' => \__('A sample Series Category description.', 'organize-series'),
                    'count'      => 3,
                    'is_sample'  => true,
                ],
                (object) [
                    'term_id'    => 2,
                    'name'       => \__('Magazine Issues', 'organize-series'),
                    'slug'       => 'magazine-issues',
                    'description' => \__('A sample Series Category description.', 'organize-series'),
                    'count'      => 5,
                    'is_sample'  => true,
                ],
                (object) [
                    'term_id'    => 3,
                    'name'       => \__('Tutorial Collections', 'organize-series'),
                    'slug'       => 'tutorial-collections',
                    'description' => \__('A sample Series Category description.', 'organize-series'),
                    'count'      => 2,
                    'is_sample'  => true,
                ],
            ];
        }

        return [
            (object) [
                'term_id'    => 1,
                'name'       => \__('Alpha Series', 'organize-series'),
                'slug'       => 'alpha-series',
                'description' => \__('A sample description for this Series.', 'organize-series'),
                'count'      => 2,
                'is_sample'  => true,
            ],
            (object) [
                'term_id'    => 2,
                'name'       => \__('Bravo Series', 'organize-series'),
                'slug'       => 'bravo-series',
                'description' => \__('A sample description for this Series.', 'organize-series'),
                'count'      => 5,
                'is_sample'  => true,
            ],
            (object) [
                'term_id'    => 3,
                'name'       => \__('Charlie Series', 'organize-series'),
                'slug'       => 'charlie-series',
                'description' => \__('A sample description for this Series.', 'organize-series'),
                'count'      => 1,
                'is_sample'  => true,
            ],
        ];
    }

    /**
     * Sort in-memory Series terms for editor previews.
     *
     * @param array  $terms    Terms to sort.
     * @param string $order_by Sort field.
     * @param string $order    Sort direction.
     *
     * @return array
     */
    private static function sortSeriesListTerms($terms, $order_by, $order)
    {
        usort(
            $terms,
            static function ($left, $right) use ($order_by, $order) {
                if ('count' === $order_by || 'term_id' === $order_by) {
                    $result = \absint($left->{$order_by} ?? 0) <=> \absint($right->{$order_by} ?? 0);
                } else {
                    $result = \strnatcasecmp((string) ($left->name ?? ''), (string) ($right->name ?? ''));
                }

                if (0 === $result) {
                    $result = \absint($left->term_id ?? 0) <=> \absint($right->term_id ?? 0);
                }

                return 'DESC' === $order ? -$result : $result;
            }
        );

        return $terms;
    }

    /**
     * Render a Series feature block using its existing shortcode.
     *
     * @param string        $block_name Block name.
     * @param array         $attributes Block attributes.
     * @param \WP_Block|null $block      Block instance.
     *
     * @return string
     */
    private static function renderFeature($block_name, $attributes, $block = null)
    {
        $definitions = [
            'publishpress-series/post-list-box' => [
                'postType'      => 'pps_post_list_box',
                'defaultMethod' => ['PPS_Post_List_Box_Utilities', 'get_default_post_list_box_id'],
                'shortcode'     => 'pps_post_list_box',
                'layoutPrefix'  => 'pps_post_list_box_',
                'supportsPostId' => false,
            ],
            'publishpress-series/post-details' => [
                'postType'      => 'pps_post_details',
                'defaultMethod' => ['PPS_Series_Post_Details_Utilities', 'get_default_series_post_details_id'],
                'shortcode'     => 'pps_post_details',
                'layoutPrefix'  => 'pps_meta_box_',
                'supportsPostId' => true,
            ],
            'publishpress-series/post-navigation' => [
                'postType'      => 'pps_post_navigation',
                'defaultMethod' => ['PPS_Series_Post_Navigation_Utilities', 'get_default_post_navigation_id'],
                'shortcode'     => 'pps_post_navigation',
                'layoutPrefix'  => 'pps_nav_',
                'supportsPostId' => true,
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
        $post_id   = self::getBlockPostId($block);

        // An explicitly selected invalid series must not fall back to the current series.
        if ($series_id && '' === $series) {
            return self::isEditorPreview()
                ? self::renderFeaturePreview($block_name, $layout_id, 0)
                : '';
        }

        if (self::isEditorPreview()) {
            return self::renderFeaturePreview($block_name, $layout_id, $series_id);
        }

        if (! $series && $post_id) {
            $series = self::getPostSeriesSlug($post_id);
        }

        $shortcode = sprintf(
            '[%1$s layout="%2$s"%3$s%4$s]',
            $definition['shortcode'],
            \esc_attr($definition['layoutPrefix'] . $layout_id),
            $series ? ' series="' . \esc_attr($series) . '"' : '',
            $post_id && $definition['supportsPostId'] ? ' post_id="' . \absint($post_id) . '"' : ''
        );

        $rendered = \do_shortcode($shortcode);

        return $rendered;
    }

    /**
     * Resolve the post ID supplied through block context.
     *
     * @param \WP_Block|null $block Block instance.
     *
     * @return int
     */
    private static function getBlockPostId($block)
    {
        if (is_object($block) && isset($block->context) && is_array($block->context)) {
            $post_id = isset($block->context['postId']) ? \absint($block->context['postId']) : 0;
            if ($post_id) {
                return $post_id;
            }
        }

        // WordPress 5.5 sets up global post data for ServerSideRender requests.
        global $post;

        return $post instanceof \WP_Post ? \absint($post->ID) : 0;
    }

    /**
     * Resolve the first Series assigned to a post.
     *
     * @param int $post_id Post ID.
     *
     * @return string
     */
    private static function getPostSeriesSlug($post_id)
    {
        $taxonomy = function_exists('ppseries_get_series_slug')
            ? \ppseries_get_series_slug()
            : 'series';
        $terms = \get_the_terms($post_id, $taxonomy);

        if (! $terms || \is_wp_error($terms)) {
            return '';
        }

        $term = reset($terms);

        return isset($term->slug) ? \sanitize_title($term->slug) : '';
    }

    /**
     * Check whether this request is an authenticated block editor preview.
     *
     * @return bool
     */
    private static function isEditorPreview()
    {
        return defined('REST_REQUEST')
            && REST_REQUEST
            && \current_user_can('edit_posts');
    }

    /**
     * Render sample content with the selected layout for the block editor.
     *
     * @param string $block_name Block name.
     * @param int    $layout_id  Layout post ID.
     * @param int    $series_id  Selected Series term ID.
     *
     * @return string
     */
    private static function renderFeaturePreview($block_name, $layout_id, $series_id)
    {
        $series_term = self::getPreviewSeriesTerm($series_id);

        if ('publishpress-series/post-list-box' === $block_name) {
            if (! class_exists('PPS_Post_List_Box_Fields') || ! class_exists('PPS_Post_List_Box_Preview')) {
                return '';
            }

            $settings = \PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($layout_id);
            $posts = \PPS_Post_List_Box_Preview::get_sample_series_posts(
                isset($series_term->term_id) ? (int) $series_term->term_id : 0,
                $settings
            );

            return \PPS_Post_List_Box_Preview::render_preview_content($settings, $posts);
        }

        if ('publishpress-series/post-details' === $block_name) {
            if (! class_exists('PPS_Series_Post_Details_Utilities') || ! class_exists('SeriesPostDetailsRenderer')) {
                return '';
            }

            $settings = \PPS_Series_Post_Details_Utilities::get_post_details_settings($layout_id);
            $posts = \PPS_Series_Post_Details_Utilities::get_sample_series_posts(
                isset($series_term->term_id) ? (int) $series_term->term_id : 0
            );
            $preview = \SeriesPostDetailsRenderer::render_from_settings(
                $settings,
                [
                    'series_term' => $series_term,
                    'post'        => ! empty($posts) ? $posts[0] : null,
                    'total_posts' => count($posts),
                    'series_part' => 1,
                    'context'     => 'preview',
                ]
            );

            ob_start();
            \SeriesPostDetailsRenderer::output_dynamic_css();

            return $preview . ob_get_clean();
        }

        if ('publishpress-series/post-navigation' === $block_name) {
            if (! class_exists('PPS_Series_Post_Navigation_Utilities') || ! class_exists('PostNavigationRenderer')) {
                return '';
            }

            $settings = \PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings($layout_id);
            $posts = \PPS_Series_Post_Navigation_Utilities::get_sample_series_posts(
                isset($series_term->term_id) ? (int) $series_term->term_id : 0
            );

            if (empty($posts)) {
                $posts = self::getFallbackPreviewPosts();
            }

            $total_posts = count($posts);
            $current_index = $total_posts > 1 ? 1 : 0;
            $current_post = $total_posts ? $posts[$current_index] : null;
            $preview = \PostNavigationRenderer::render_from_settings(
                $settings,
                [
                    'series_term' => $series_term,
                    'post'        => $current_post,
                    'total_posts' => $total_posts,
                    'preview_posts' => [
                        'current'  => $current_post,
                        'previous' => $current_index + 1 < $total_posts ? $posts[$current_index + 1] : null,
                        'next'     => $current_index > 0 ? $posts[$current_index - 1] : null,
                        'first'    => $total_posts ? $posts[$total_posts - 1] : null,
                    ],
                    'context'     => 'preview',
                ]
            );

            ob_start();
            \PostNavigationRenderer::output_dynamic_css();

            return $preview . ob_get_clean();
        }

        return '';
    }

    /**
     * Get the selected Series, the first available Series, or a sample term.
     *
     * @param int $series_id Selected Series term ID.
     *
     * @return object
     */
    private static function getPreviewSeriesTerm($series_id)
    {
        $taxonomy = function_exists('ppseries_get_series_slug')
            ? \ppseries_get_series_slug()
            : 'series';

        if ($series_id) {
            $term = \get_term($series_id, $taxonomy);
            if ($term && ! \is_wp_error($term)) {
                return $term;
            }
        }

        $terms = \get_terms([
            'taxonomy'   => $taxonomy,
            'hide_empty' => false,
            'number'     => 1,
        ]);

        if (! \is_wp_error($terms) && ! empty($terms)) {
            return $terms[0];
        }

        return (object) [
            'term_id'  => 0,
            'name'     => \__('Sample Series', 'organize-series'),
            'slug'     => 'sample-series',
            'taxonomy' => $taxonomy,
        ];
    }

    /**
     * Get real posts, or in-memory sample posts, for navigation previews.
     *
     * @return array
     */
    private static function getFallbackPreviewPosts()
    {
        $posts = \get_posts([
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => 3,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => true,
        ]);

        if (! empty($posts)) {
            return $posts;
        }

        $posts = [];
        for ($index = 1; $index <= 3; $index++) {
            $posts[] = new \WP_Post((object) [
                'ID'             => 0,
                'post_author'    => \get_current_user_id(),
                'post_date'      => \current_time('mysql'),
                'post_date_gmt'  => \current_time('mysql', true),
                'post_content'   => '',
                /* translators: %d: Sample post number. */
                'post_title'     => sprintf(\__('Sample Series Post %d', 'organize-series'), $index),
                'post_excerpt'   => '',
                'post_status'    => 'publish',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_password'  => '',
                'post_name'      => 'sample-series-post-' . $index,
                'to_ping'        => '',
                'pinged'         => '',
                'post_modified'  => \current_time('mysql'),
                'post_modified_gmt' => \current_time('mysql', true),
                'post_content_filtered' => '',
                'post_parent'    => 0,
                'guid'           => '',
                'menu_order'     => 0,
                'post_type'      => 'post',
                'post_mime_type' => '',
                'comment_count'  => 0,
                'filter'         => 'raw',
            ]);
        }

        return $posts;
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

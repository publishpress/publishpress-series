<?php
/**
 * Renderer utilities for Series Post Navigation.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../includes/class-utilities.php';

class PostNavigationRenderer
{
    /**
     * Store dynamic CSS snippets keyed by layout ID.
     *
     * @var array
     */
    private static $dynamic_css = [];

    /**
     * Flag to avoid duplicate asset loads.
     *
     * @var bool
     */
    private static $assets_enqueued = false;

    /**
     * Boot front-end integration.
     */
    public static function init()
    {
        add_shortcode('pps_post_navigation', [__CLASS__, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_assets']);
        add_action('wp_footer', [__CLASS__, 'output_dynamic_css']);
    }

    /**
     * Enqueue shared front-end styles.
     */
    public static function enqueue_frontend_assets()
    {
        if (self::$assets_enqueued) {
            return;
        }

        $base_file = PPS_Series_Post_Navigation_Utilities::get_module_path('post-navigation.php');
        $style_url = plugins_url('assets/css/post-navigation-frontend.css', $base_file);

        wp_enqueue_style('pps-series-post-navigation-frontend', $style_url, [], ORG_SERIES_VERSION);
        wp_enqueue_style('dashicons');

        self::$assets_enqueued = true;
    }

    /**
     * Render the shortcode `[pps_post_navigation layout="..."]`.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    public static function render_shortcode($atts)
    {
        $atts = shortcode_atts([
            'layout'  => '',
            'series'  => '',
            'post_id' => 0,
        ], $atts, 'pps_post_navigation');

        if (empty($atts['layout'])) {
            return '<!-- Series Post Navigation: layout attribute missing -->';
        }

        $layout_id = self::normalize_layout_id($atts['layout']);
        if (! $layout_id || 'publish' !== get_post_status($layout_id)) {
            return '<!-- Series Post Navigation: invalid or unpublished layout -->';
        }

        $post = null;
        if (! empty($atts['post_id'])) {
            $post = get_post((int) $atts['post_id']);
        } elseif (is_singular()) {
            $post = get_queried_object();
        }

        $series_term = self::resolve_series_term($atts['series'], $post);
        if (! $series_term) {
            return '<!-- Series Post Navigation: unable to determine series -->';
        }

        $context = [
            'series_term' => $series_term,
            'post'        => $post,
            'context'     => 'shortcode',
        ];

        return self::render_layout_for_series($layout_id, $context);
    }

    /**
     * Render a layout for automatic nav injection.
     *
     * @param int   $layout_id Layout post ID.
     * @param array $context   Context array with `series_term`, `post`.
     *
     * @return string
     */
    public static function render_layout_for_series($layout_id, array $context)
    {
        $settings = PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings($layout_id);
        if (empty($settings)) {
            return '';
        }

        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post        = isset($context['post']) ? $context['post'] : null;

        if (! $series_term || ! isset($series_term->term_id)) {
            return '';
        }

        $series_id = (int) $series_term->term_id;
        $post_id   = ($post instanceof WP_Post) ? (int) $post->ID : 0;

        $render_context = [
            'series_term' => $series_term,
            'post'        => $post,
            'series_id'   => $series_id,
            'post_id'     => $post_id,
        ];

        $content = self::render_from_visual_controls($settings, $render_context);

        // Return empty if no content generated
        if ('' === $content || null === $content) {
            return '';
        }

        $layout_class = 'pps-post-navigation-' . $layout_id;
        self::capture_dynamic_css($layout_class, $settings);

        $layout_class_attr = esc_attr($layout_class);
        $series_id_attr = esc_attr($series_id);
        
        $content = str_replace(
            'class="pps-navigation-content"',
            sprintf('class="pps-navigation-content %s" data-series-id="%s"', $layout_class_attr, $series_id_attr),
            $content
        );

        return $content;
    }

    /**
     * Render preview markup from raw settings.
     *
     * @param array $settings Editor settings array.
     * @param array $context  Context array from preview handler.
     *
     * @return string
     */
    public static function render_from_settings(array $settings, array $context = [])
    {
        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post        = isset($context['post']) ? $context['post'] : null;
        $total_posts = isset($context['total_posts']) ? (int) $context['total_posts'] : 3;

        // Use unique class for preview dynamic CSS to avoid conflicts with wrapper
        $layout_class = 'pps-post-navigation-preview-inner';

        if (isset($context['context']) && 'preview' === $context['context'] && isset(self::$dynamic_css[$layout_class])) {
            unset(self::$dynamic_css[$layout_class]);
        }

        self::capture_dynamic_css($layout_class, $settings);

        $content = self::render_preview_from_visual_controls($settings, $context);

        $layout_class_attr = esc_attr($layout_class);
        
        $content = str_replace(
            'class="pps-navigation-content"',
            sprintf('class="pps-navigation-content pps-post-navigation-preview %s"', $layout_class_attr),
            $content
        );

        return $content;
    }

    /**
     * Render preview content from visual controls for admin preview.
     *
     * @param array $settings Layout settings array.
     * @param array $context  Preview context.
     *
     * @return string
     */
    private static function render_preview_from_visual_controls(array $settings, array $context)
    {
        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post        = isset($context['post']) ? $context['post'] : null;
        $total_posts = isset($context['total_posts']) ? (int) $context['total_posts'] : 3;

        if (!$series_term || !isset($series_term->term_id)) {
            return '';
        }

        // Check if we should hide when single post
        if (!empty($settings['hide_when_single_post']) && $total_posts <= 1) {
            return '<p style="color: #999; font-style: italic;">' . esc_html__('Navigation hidden (series has only one post)', 'publishpress-series') . '</p>';
        }

        $content_parts = [];

        // Series title
        if (!empty($settings['include_series_title'])) {
            $content_parts[] = '<h3 class="pps-nav-series-title">' . esc_html($series_term->name) . '</h3>';
        }

        // Navigation links (prev, next, first)
        $nav_links = [];

        $preview_posts = isset($context['preview_posts']) && is_array($context['preview_posts'])
            ? $context['preview_posts']
            : [];

        // Ensure we have sensible defaults for preview posts
        if (! isset($preview_posts['current']) || ! ($preview_posts['current'] instanceof WP_Post)) {
            $preview_posts['current'] = $post;
        }

        if (! isset($preview_posts['first']) || ! ($preview_posts['first'] instanceof WP_Post)) {
            $preview_posts['first'] = $series_term ? get_posts([
                'post_type'      => 'post',
                'tax_query'      => [[
                    'taxonomy' => get_option('pp_series_taxonomy_slug', 'series'),
                    'field'    => 'term_id',
                    'terms'    => $series_term->term_id,
                ]],
                'posts_per_page' => 1,
                'orderby'        => 'date',
                'order'          => 'ASC',
            ])[0] ?? null : null;
        }

        // Build links based on position
        $first_link_position = isset($settings['first_link_position']) ? $settings['first_link_position'] : 'right';
        
        // First link (left position)
        $first_type = isset($settings['first_link_type']) ? $settings['first_link_type'] : 'none';
        if ($first_type !== 'none' && $first_link_position === 'left') {
            $first_post = isset($preview_posts['first']) && $preview_posts['first'] instanceof WP_Post
                ? $preview_posts['first']
                : null;

            $label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
            if ($first_type === 'post_title' && $first_post) {
                $label = get_the_title($first_post->ID);
            }

            $href = $first_post ? get_permalink($first_post) : '#';
            $nav_links[] = self::build_nav_link($href, $label, 'first', $settings, $first_post);
        }

        // Previous link
        $prev_type = isset($settings['previous_link_type']) ? $settings['previous_link_type'] : 'custom';
        if ($prev_type !== 'none') {
            $prev_post = isset($preview_posts['previous']) && $preview_posts['previous'] instanceof WP_Post
                ? $preview_posts['previous']
                : null;

            $label = isset($settings['previous_label']) ? $settings['previous_label'] : __('Previous', 'publishpress-series');
            if ($prev_type === 'post_title' && $prev_post) {
                $label = get_the_title($prev_post->ID);
            }

            $href = $prev_post ? get_permalink($prev_post) : '#';
            $nav_links[] = self::build_nav_link($href, $label, 'previous', $settings, $prev_post);
        }

        // First link (center position)
        if ($first_type !== 'none' && $first_link_position === 'center') {
            $first_post = isset($preview_posts['first']) && $preview_posts['first'] instanceof WP_Post
                ? $preview_posts['first']
                : null;

            $label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
            if ($first_type === 'post_title' && $first_post) {
                $label = get_the_title($first_post->ID);
            }

            $href = $first_post ? get_permalink($first_post) : '#';
            $nav_links[] = self::build_nav_link($href, $label, 'first', $settings, $first_post);
        }

        // Next link
        $next_type = isset($settings['next_link_type']) ? $settings['next_link_type'] : 'custom';
        if ($next_type !== 'none') {
            $next_post = isset($preview_posts['next']) && $preview_posts['next'] instanceof WP_Post
                ? $preview_posts['next']
                : null;

            $label = isset($settings['next_label']) ? $settings['next_label'] : __('Next', 'publishpress-series');
            if ($next_type === 'post_title' && $next_post) {
                $label = get_the_title($next_post->ID);
            }

            $href = $next_post ? get_permalink($next_post) : '#';
            $nav_links[] = self::build_nav_link($href, $label, 'next', $settings, $next_post);
        }

        // First link (right position - default)
        if ($first_type !== 'none' && $first_link_position === 'right') {
            $first_post = isset($preview_posts['first']) && $preview_posts['first'] instanceof WP_Post
                ? $preview_posts['first']
                : null;

            $label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
            if ($first_type === 'post_title' && $first_post) {
                $label = get_the_title($first_post->ID);
            }

            $href = $first_post ? get_permalink($first_post) : '#';
            $nav_links[] = self::build_nav_link($href, $label, 'first', $settings, $first_post);
        }

        if (!empty($nav_links)) {
            $links_html = self::build_nav_links_html($nav_links);
            $content_parts[] = '<span class="pps-nav-links">' . $links_html . '</span>';
        }

        if (empty($content_parts)) {
            return '';
        }

        return '<div class="pps-navigation-content">' . implode(' ', $content_parts) . '</div>';
    }

    /**
     * Render content from visual controls instead of token templates.
     *
     * @param array $settings Layout settings array.
     * @param array $context  Rendering context.
     *
     * @return string
     */
    private static function render_from_visual_controls(array $settings, array $context)
    {
        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post        = isset($context['post']) ? $context['post'] : null;
        $series_id   = isset($context['series_id']) ? (int) $context['series_id'] : 0;
        $post_id     = isset($context['post_id']) ? (int) $context['post_id'] : 0;

        if (!$series_term || !isset($series_term->term_id)) {
            return '';
        }

        // Check if we should hide when single post
        if (!empty($settings['hide_when_single_post'])) {
            if (!empty($settings['hide_when_single_post'])) {
                $post_count = function_exists('wp_postlist_count') ? wp_postlist_count($series_id) : 0;
                if ($post_count <= 1) {
                    return '';
                }
            }
            if ($post_count <= 1) {
                return '';
            }
        }

        $content_parts = [];

        // Series title
        if (!empty($settings['include_series_title'])) {
            $content_parts[] = '<h3 class="pps-nav-series-title">' . esc_html($series_term->name) . '</h3>';
        }

        // Navigation links (prev, next, first)
        $nav_links = [];
        
        // Get position setting
        $first_link_position = isset($settings['first_link_position']) ? $settings['first_link_position'] : 'right';
        $first_type = isset($settings['first_link_type']) ? $settings['first_link_type'] : 'none';

        // First link (left position)
        if ($first_type !== 'none' && $post_id && $series_id && $first_link_position === 'left') {
            $first_link = wp_series_nav($series_id, 2, 'deprecated', false, false);
            if ($first_link) {
                $first_post = self::get_first_series_post($series_id);
                if (! $first_post) {
                    $first_post = self::extract_post_from_nav_link($first_link);
                }
                if ($first_type === 'custom') {
                    $first_label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
                    $first_link = self::replace_nav_link_text($first_link, esc_html($first_label));
                } elseif ($first_type === 'post_title') {
                    if ($first_post) {
                        $first_title = get_the_title($first_post->ID);
                    } else {
                        $first_title = self::normalize_nav_link_text($first_link);
                    }
                    $first_link = self::replace_nav_link_text($first_link, esc_html($first_title));
                }

                $nav_links[] = self::decorate_existing_nav_link($first_link, 'first', $settings, $first_post);
            }
        }

        // Previous link
        $prev_type = isset($settings['previous_link_type']) ? $settings['previous_link_type'] : 'custom';
        if ($prev_type !== 'none' && $post_id && $series_id) {
            $prev_link = wp_series_nav($series_id, false, 'deprecated', false, false);
            if ($prev_link) {
                $prev_post = self::get_previous_series_post($series_id, $post_id);
                if (! $prev_post) {
                    $prev_post = self::extract_post_from_nav_link($prev_link);
                }
                // Replace link text with configured label
                if ($prev_type === 'custom') {
                    $prev_label = isset($settings['previous_label']) ? $settings['previous_label'] : __('Previous', 'publishpress-series');
                    $prev_link = self::replace_nav_link_text($prev_link, esc_html($prev_label));
                } elseif ($prev_type === 'post_title') {
                    if ($prev_post) {
                        $prev_title = get_the_title($prev_post->ID);
                    } else {
                        $prev_title = self::normalize_nav_link_text($prev_link);
                    }
                    if ($prev_title !== '') {
                        $prev_link = self::replace_nav_link_text($prev_link, esc_html($prev_title));
                    }
                }

                $nav_links[] = self::decorate_existing_nav_link($prev_link, 'previous', $settings, $prev_post);
            }
        }

        // First link (center position)
        if ($first_type !== 'none' && $post_id && $series_id && $first_link_position === 'center') {
            $first_link = wp_series_nav($series_id, 2, 'deprecated', false, false);
            if ($first_link) {
                $first_post = self::get_first_series_post($series_id);
                if (! $first_post) {
                    $first_post = self::extract_post_from_nav_link($first_link);
                }
                if ($first_type === 'custom') {
                    $first_label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
                    $first_link = self::replace_nav_link_text($first_link, esc_html($first_label));
                } elseif ($first_type === 'post_title') {
                    if ($first_post) {
                        $first_title = get_the_title($first_post->ID);
                    } else {
                        $first_title = self::normalize_nav_link_text($first_link);
                    }
                    if ($first_title !== '') {
                        $first_link = self::replace_nav_link_text($first_link, esc_html($first_title));
                    }
                }

                $nav_links[] = self::decorate_existing_nav_link($first_link, 'first', $settings, $first_post);
            }
        }

        // Next link
        $next_type = isset($settings['next_link_type']) ? $settings['next_link_type'] : 'custom';
        if ($next_type !== 'none' && $post_id && $series_id) {
            $next_link = wp_series_nav($series_id, true, 'deprecated', false, false);
            if ($next_link) {
                $next_post = self::get_next_series_post($series_id, $post_id);
                if (! $next_post) {
                    $next_post = self::extract_post_from_nav_link($next_link);
                }
                // Replace link text with configured label
                if ($next_type === 'custom') {
                    $next_label = isset($settings['next_label']) ? $settings['next_label'] : __('Next', 'publishpress-series');
                    $next_link = self::replace_nav_link_text($next_link, esc_html($next_label));
                } elseif ($next_type === 'post_title') {
                    if ($next_post) {
                        $next_title = get_the_title($next_post->ID);
                    } else {
                        $next_title = self::normalize_nav_link_text($next_link);
                    }
                    if ($next_title !== '') {
                        $next_link = self::replace_nav_link_text($next_link, esc_html($next_title));
                    }
                }

                $nav_links[] = self::decorate_existing_nav_link($next_link, 'next', $settings, $next_post);
            }
        }

        // First link (right position - default)
        if ($first_type !== 'none' && $post_id && $series_id && $first_link_position === 'right') {
            $first_link = wp_series_nav($series_id, 2, 'deprecated', false, false);
            if ($first_link) {
                $first_post = self::get_first_series_post($series_id);
                if (! $first_post) {
                    $first_post = self::extract_post_from_nav_link($first_link);
                }
                if ($first_type === 'custom') {
                    $first_label = isset($settings['first_label']) ? $settings['first_label'] : __('Series Home', 'publishpress-series');
                    $first_link = self::replace_nav_link_text($first_link, esc_html($first_label));
                } elseif ($first_type === 'post_title') {
                    if ($first_post) {
                        $first_title = get_the_title($first_post->ID);
                    } else {
                        $first_title = self::normalize_nav_link_text($first_link);
                    }
                    if ($first_title !== '') {
                        $first_link = self::replace_nav_link_text($first_link, esc_html($first_title));
                    }
                }

                $nav_links[] = self::decorate_existing_nav_link($first_link, 'first', $settings, $first_post);
            }
        }

        if (empty($nav_links)) {
            return '';
        }

        $links_html = self::build_nav_links_html($nav_links);
        $content_parts[] = '<span class="pps-nav-links">' . $links_html . '</span>';

        return '<div class="pps-navigation-content">' . implode(' ', $content_parts) . '</div>';
    }

    /**
     * Build navigation link markup for preview rendering.
     */
    private static function build_nav_link($href, $label, $position, array $settings, $post = null)
    {
        $classes = self::get_link_classes($position, $settings);
        $attributes = sprintf(
            ' class="%s" href="%s"',
            esc_attr(implode(' ', $classes)),
            esc_url($href ? $href : '#')
        );

        // Build content parts
        $parts = [];
        
        // Get arrow HTML
        $arrow_html = self::get_arrow_html($position, $settings);
        $arrow_position = isset($settings[$position . '_arrow_position']) ? $settings[$position . '_arrow_position'] : 'left';
        
        // Get featured image HTML
        $show_image_key = $position . '_show_featured_image';
        $show_image = !empty($settings[$show_image_key]);
        $image_html = '';
        
        if ($show_image && $post instanceof WP_Post) {
            $image_html = self::get_featured_image_html($post, $position, $settings);
            $image_position = isset($settings[$position . '_image_position']) ? $settings[$position . '_image_position'] : 'left';
        }
        
        // Assemble content in correct order
        $text_content = '<span class="pps-nav-link-text">' . esc_html($label) . '</span>';
        
        // Add arrow on left if needed
        if ($arrow_html && $arrow_position === 'left') {
            $parts[] = $arrow_html;
        }
        
        // Add image on left if needed
        if ($image_html && isset($image_position) && $image_position === 'left') {
            $parts[] = $image_html;
        }
        
        // Add text
        $parts[] = $text_content;
        
        // Add image on right if needed
        if ($image_html && isset($image_position) && $image_position === 'right') {
            $parts[] = $image_html;
        }
        
        // Add arrow on right if needed
        if ($arrow_html && $arrow_position === 'right') {
            $parts[] = $arrow_html;
        }
        
        $link_content = implode('', $parts);

        return sprintf('<a%s>%s</a>', $attributes, $link_content);
    }

    /**
     * Get featured image HTML for a post.
     *
     * @param WP_Post $post Post object.
     * @param string $position Link position (previous, next, first).
     * @param array $settings Layout settings.
     *
     * @return string
     */
    private static function get_featured_image_html($post, $position, array $settings)
    {
        if (!has_post_thumbnail($post->ID)) {
            return '';
        }

        $width = isset($settings[$position . '_image_width']) ? (int) $settings[$position . '_image_width'] : 80;
        $height = isset($settings[$position . '_image_height']) ? (int) $settings[$position . '_image_height'] : 80;

        $image_url = get_the_post_thumbnail_url($post->ID, 'full');
        if (!$image_url) {
            return '';
        }

        return sprintf(
            '<img src="%s" alt="%s" class="pps-nav-featured-image" style="width: %dpx; height: %dpx; object-fit: cover;" />',
            esc_url($image_url),
            esc_attr(get_the_title($post->ID)),
            $width,
            $height
        );
    }


    /**
     * Get arrow HTML based on settings.
     *
     * @param string $position Link position (previous, next, first).
     * @param array $settings Layout settings.
     *
     * @return string
     */
    private static function get_arrow_html($position, array $settings)
    {
        $show_arrow_key = $position . '_show_arrow';
        if (empty($settings[$show_arrow_key])) {
            return '';
        }

        $arrow_type = isset($settings[$position . '_arrow_type']) ? $settings[$position . '_arrow_type'] : 'chevron_left';
        $arrow_size = isset($settings[$position . '_arrow_size']) ? (int) $settings[$position . '_arrow_size'] : 16;
        
        // Ensure arrow size is within reasonable bounds
        if ($arrow_size < 8) {
            $arrow_size = 8;
        } elseif ($arrow_size > 64) {
            $arrow_size = 64;
        }
        
        // If custom image is selected
        if ($arrow_type === 'custom') {
            $attachment_id = isset($settings[$position . '_custom_arrow_image']) ? (int) $settings[$position . '_custom_arrow_image'] : 0;
            if ($attachment_id > 0) {
                $image_url = wp_get_attachment_image_url($attachment_id, 'full');
                if ($image_url) {
                    return sprintf(
                        '<img src="%s" alt="arrow" class="pps-nav-arrow pps-nav-arrow-custom" style="width: %dpx; height: %dpx; display: inline-block; vertical-align: middle;" />',
                        esc_url($image_url),
                        $arrow_size,
                        $arrow_size
                    );
                }
            }
            return '';
        }

        // SVG icons for predefined arrow types
        $svg_icons = [
            'chevron_left' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>',
            'arrow_left' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
            'double_left' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"></polyline><polyline points="18 17 13 12 18 7"></polyline></svg>',
            'triangle_left' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="currentColor"><polygon points="15,18 9,12 15,6"></polygon></svg>',
            'chevron_right' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>',
            'arrow_right' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>',
            'double_right' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 17 18 12 13 7"></polyline><polyline points="6 17 11 12 6 7"></polyline></svg>',
            'triangle_right' => '<svg class="pps-nav-arrow" width="%d" height="%d" viewBox="0 0 24 24" fill="currentColor"><polygon points="9,18 15,12 9,6"></polygon></svg>',
        ];

        if (isset($svg_icons[$arrow_type])) {
            return sprintf($svg_icons[$arrow_type], $arrow_size, $arrow_size);
        }
        
        return '';
    }

    /**
     * Ensure existing navigation links gain the appropriate classes and featured images.
     */
    private static function decorate_existing_nav_link($link_html, $position, array $settings, $post = null)
    {
        $classes = implode(' ', self::get_link_classes($position, $settings));

        // Add classes to the link
        $link_html = preg_replace_callback(
            '/<a\b([^>]*)>/i',
            function ($matches) use ($classes) {
                $attributes = $matches[1];

                if (preg_match('/class\s*=\s*(["\'])(.*?)\1/i', $attributes, $class_match)) {
                    $quote = $class_match[1];
                    $existing = $class_match[2];
                    $replacement = 'class=' . $quote . esc_attr(trim($existing . ' ' . $classes)) . $quote;
                    $attributes = str_replace($class_match[0], $replacement, $attributes);
                } else {
                    $attributes .= ' class="' . esc_attr($classes) . '"';
                }

                return '<a' . $attributes . '>';
            },
            $link_html,
            1
        );

        // Get arrow HTML
        $arrow_html = self::get_arrow_html($position, $settings);
        $arrow_position = isset($settings[$position . '_arrow_position']) ? $settings[$position . '_arrow_position'] : 'left';
        
        // Get featured image HTML
        $show_image_key = $position . '_show_featured_image';
        $show_image = !empty($settings[$show_image_key]);
        $image_html = '';
        $image_position = 'left';
        
        if ($show_image && $post instanceof WP_Post) {
            $image_html = self::get_featured_image_html($post, $position, $settings);
            $image_position = isset($settings[$position . '_image_position']) ? $settings[$position . '_image_position'] : 'left';
        }
        
        // Add arrows and images if needed
        if ($arrow_html || $image_html) {
            $link_html = preg_replace_callback(
                '/<a\b([^>]*)>(.*?)<\/a>/is',
                function ($matches) use ($arrow_html, $arrow_position, $image_html, $image_position) {
                    $attributes = $matches[1];
                    $link_text = $matches[2];
                    $parts = [];

                    // Add arrow on left if needed
                    if ($arrow_html && $arrow_position === 'left') {
                        $parts[] = $arrow_html;
                    }

                    // Add image on left if needed
                    if ($image_html && $image_position === 'left') {
                        $parts[] = $image_html;
                    }

                    // Add text (wrapped)
                    $parts[] = '<span class="pps-nav-link-text">' . $link_text . '</span>';

                    // Add image on right if needed
                    if ($image_html && $image_position === 'right') {
                        $parts[] = $image_html;
                    }

                    // Add arrow on right if needed
                    if ($arrow_html && $arrow_position === 'right') {
                        $parts[] = $arrow_html;
                    }

                    $new_content = implode('', $parts);
                    return '<a' . $attributes . '>' . $new_content . '</a>';
                },
                $link_html,
                1
            );
        }

        return $link_html;
    }

    /**
     * Replace the inner text of a navigation link.
     *
     * @param string $link_html Existing link HTML.
     * @param string $replacement Replacement text (already escaped).
     *
     * @return string
     */
    private static function replace_nav_link_text($link_html, $replacement)
    {
        return preg_replace('/(<a\b[^>]*>)(.*?)(<\/a>)/is', '$1' . $replacement . '$3', $link_html, 1);
    }

    /**
     * Normalize text content extracted from legacy navigation links.
     *
     * @param string $link_html Link HTML to normalize.
     *
     * @return string
     */
    private static function normalize_nav_link_text($link_html)
    {
        if (! class_exists('DOMDocument')) {
            $text = trim(strip_tags($link_html));
            // remove arrowlike unicode (double angle quotes etc.)
            return trim(preg_replace('/[«»<>←→⇐⇒]+/', '', $text));
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $link_html);
        libxml_clear_errors();

        $anchor = $dom->getElementsByTagName('a')->item(0);
        if (! $anchor) {
            return '';
        }

        $text = $anchor->textContent;
        return trim(preg_replace('/[«»<>←→⇐⇒]+/', '', $text));
    }

    /**
     * Attempt to extract the linked post from legacy navigation markup.
     *
     * @param string $link_html Link HTML to inspect.
     *
     * @return WP_Post|null
     */
    private static function extract_post_from_nav_link($link_html)
    {
        $href = '';

        if (class_exists('DOMDocument')) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $link_html);
            libxml_clear_errors();

            $anchor = $dom->getElementsByTagName('a')->item(0);
            if ($anchor && $anchor->hasAttribute('href')) {
                $href = $anchor->getAttribute('href');
            }
        }

        if (empty($href) && preg_match('/href\s*=\s*(\"|\')(.*?)\\1/i', $link_html, $match)) {
            $href = html_entity_decode($match[2]);
        }

        if (empty($href)) {
            return null;
        }

        $post_id = url_to_postid($href);
        if ($post_id) {
            $post = get_post($post_id);
            if ($post instanceof WP_Post) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Assemble CSS class list for navigation links.
     */
    private static function get_link_classes($position, array $settings)
    {
        $classes = ['pps-nav-link', 'pps-nav-' . sanitize_html_class($position)];
        return $classes;
    }

    /**
     * Build navigation links HTML with proper grouping for space-between layout.
     * Groups all links except the last one together, so the last link (usually Next) 
     * appears on the right side.
     *
     * @param array  $nav_links Array of link HTML strings.
     *
     * @return string
     */
    private static function build_nav_links_html(array $nav_links)
    {
        if (empty($nav_links)) {
            return '';
        }

        $count = count($nav_links);
        
        // If only one link, return it as-is
        if ($count === 1) {
            return $nav_links[0];
        }

        // Group all links except the last one
        $left_group = [];
        for ($i = 0; $i < $count - 1; $i++) {
            $left_group[] = $nav_links[$i];
        }
        
        $last_link = $nav_links[$count - 1];
        
        // Build left group without separators
        $left_html = implode(' ', $left_group);
        
        // Wrap left group in a span and add the last link separately
        return '<span class="pps-nav-left-group">' . $left_html . '</span> ' . $last_link;
    }

    /**
     * Get the first post in a series.
     *
     * @param int $series_id Series term ID.
     *
     * @return WP_Post|null
     */
    private static function get_first_series_post($series_id)
    {
        if (! function_exists('ppseries_get_series_slug') || ! function_exists('get_series_order')) {
            return null;
        }

        $series_posts = get_objects_in_term($series_id, ppseries_get_series_slug());
        if (empty($series_posts)) {
            return null;
        }

        $ordered_posts = get_series_order($series_posts, 0, $series_id);
        if (empty($ordered_posts) || ! is_array($ordered_posts)) {
            return null;
        }

        $first_entry = reset($ordered_posts);
        if (! isset($first_entry['id'])) {
            return null;
        }

        return get_post((int) $first_entry['id']);
    }

    /**
     * Get the previous post in a series.
     *
     * @param int $series_id Series term ID.
     * @param int $current_post_id Current post ID.
     *
     * @return WP_Post|null
     */
    private static function get_previous_series_post($series_id, $current_post_id)
    {
        if (! function_exists('ppseries_get_series_slug') || ! function_exists('get_series_order')) {
            return null;
        }

        $series_posts = get_objects_in_term($series_id, ppseries_get_series_slug());
        if (empty($series_posts)) {
            return null;
        }

        $ordered_posts = get_series_order($series_posts, $current_post_id, $series_id);
        if (empty($ordered_posts) || ! is_array($ordered_posts)) {
            return null;
        }

        // Find current post index
        $current_index = null;
        foreach ($ordered_posts as $index => $post_data) {
            if (isset($post_data['id']) && (int) $post_data['id'] === (int) $current_post_id) {
                $current_index = $index;
                break;
            }
        }

        if ($current_index === null || $current_index === 0) {
            return null;
        }

        $prev_entry = $ordered_posts[$current_index - 1];
        if (! isset($prev_entry['id'])) {
            return null;
        }

        return get_post((int) $prev_entry['id']);
    }

    /**
     * Get the next post in a series.
     *
     * @param int $series_id Series term ID.
     * @param int $current_post_id Current post ID.
     *
     * @return WP_Post|null
     */
    private static function get_next_series_post($series_id, $current_post_id)
    {
        if (! function_exists('ppseries_get_series_slug') || ! function_exists('get_series_order')) {
            return null;
        }

        $series_posts = get_objects_in_term($series_id, ppseries_get_series_slug());
        if (empty($series_posts)) {
            return null;
        }

        $ordered_posts = get_series_order($series_posts, $current_post_id, $series_id);
        if (empty($ordered_posts) || ! is_array($ordered_posts)) {
            return null;
        }

        // Find current post index
        $current_index = null;
        foreach ($ordered_posts as $index => $post_data) {
            if (isset($post_data['id']) && (int) $post_data['id'] === (int) $current_post_id) {
                $current_index = $index;
                break;
            }
        }

        if ($current_index === null || $current_index >= count($ordered_posts) - 1) {
            return null;
        }

        $next_entry = $ordered_posts[$current_index + 1];
        if (! isset($next_entry['id'])) {
            return null;
        }

        return get_post((int) $next_entry['id']);
    }

    /**
     * Capture dynamic CSS for a specific layout class.
     *
     * @param string $layout_class CSS class name.
     * @param array  $settings     Layout settings.
     */
    private static function capture_dynamic_css($layout_class, array $settings)
    {
        if (isset(self::$dynamic_css[$layout_class])) {
            return;
        }

        $css = [];

        // Navigation content styles (now the main wrapper)
        $content_styles = [];
        $nav_links_styles = [];
        $title_styles = [];

        // Base layout styles
        $content_styles[] = 'display: flex;';
        $content_styles[] = 'flex-direction: column;';
        $content_styles[] = 'align-items: stretch;';
        $content_styles[] = 'gap: 12px;';
        $content_styles[] = 'width: 100%;';

        // Margin
        $margin = isset($settings['margin']) ? (int) $settings['margin'] : 0;
        if ($margin > 0) {
            $content_styles[] = sprintf('margin: %dpx;', $margin);
        }

        // Container background color
        if (! empty($settings['container_background_color']) && $settings['container_background_color'] !== 'transparent') {
            $content_styles[] = sprintf('background-color: %s;', esc_attr($settings['container_background_color']));
        }

        // Container border
        $container_border_width = isset($settings['container_border_width']) ? (int) $settings['container_border_width'] : 0;
        if ($container_border_width > 0) {
            $container_border_color = ! empty($settings['container_border_color']) ? $settings['container_border_color'] : '#dddddd';
            $content_styles[] = sprintf('border: %dpx solid %s;', $container_border_width, esc_attr($container_border_color));
        }

        // Container border radius
        $container_border_radius = isset($settings['container_border_radius']) ? (int) $settings['container_border_radius'] : 0;
        if ($container_border_radius > 0) {
            $content_styles[] = sprintf('border-radius: %dpx;', $container_border_radius);
        }

        // Container padding
        $container_padding = isset($settings['container_padding']) ? (int) $settings['container_padding'] : 0;
        if ($container_padding > 0) {
            $content_styles[] = sprintf('padding: %dpx;', $container_padding);
        }

        $nav_links_styles[] = 'display: flex;';
        $nav_links_styles[] = 'flex-direction: row;';
        $nav_links_styles[] = 'flex-wrap: wrap;';
        $nav_links_styles[] = 'align-items: center;';
        $nav_links_styles[] = 'width: 100%;';
        $nav_links_styles[] = 'justify-content: space-between;';

        // Series title alignment (independent from nav links alignment)
        $series_title_alignment = isset($settings['series_title_alignment']) ? $settings['series_title_alignment'] : 'center';
        $title_align_self = 'center';
        if ($series_title_alignment === 'left') {
            $title_align_self = 'flex-start';
        } elseif ($series_title_alignment === 'right') {
            $title_align_self = 'flex-end';
        }
        $title_styles[] = 'align-self: ' . $title_align_self . ';';
        $title_styles[] = 'text-align: ' . $series_title_alignment . ';';
        
        // Series title color
        if (! empty($settings['series_title_color'])) {
            $title_styles[] = 'color: ' . esc_attr($settings['series_title_color']) . ';';
        }

        if (! empty($content_styles)) {
            $css[] = sprintf('.%1$s { %2$s }', esc_attr($layout_class), implode(' ', $content_styles));
        }

        if (! empty($nav_links_styles)) {
            $css[] = sprintf('.%1$s .pps-nav-links { %2$s }', esc_attr($layout_class), implode(' ', $nav_links_styles));
        }

        if (! empty($title_styles)) {
            $css[] = sprintf('.%1$s .pps-nav-series-title { %2$s }', esc_attr($layout_class), implode(' ', $title_styles));
        }

        // Link styles
        $link_styles = [];
        if (! empty($settings['link_color'])) {
            $link_styles[] = 'color: ' . esc_attr($settings['link_color']) . ';';
        }

        if (! empty($settings['link_background_color']) && $settings['link_background_color'] !== 'transparent') {
            $link_styles[] = 'background-color: ' . esc_attr($settings['link_background_color']) . ';';
            $link_styles[] = 'display: inline-flex;';
            $link_styles[] = 'align-items: center;';
        }

        // Border styles
        $border_width = isset($settings['border_width']) ? (int) $settings['border_width'] : 0;
        if ($border_width > 0) {
            $border_color = ! empty($settings['border_color']) ? $settings['border_color'] : '#dddddd';
            $link_styles[] = sprintf('border: %dpx solid %s;', $border_width, esc_attr($border_color));
            if (! in_array('display: inline-flex;', $link_styles, true)) {
                $link_styles[] = 'display: inline-flex;';
            }
            if (! in_array('align-items: center;', $link_styles, true)) {
                $link_styles[] = 'align-items: center;';
            }
        }

        // Border radius
        $border_radius = isset($settings['border_radius']) ? (int) $settings['border_radius'] : 0;
        if ($border_radius > 0) {
            $link_styles[] = sprintf('border-radius: %dpx;', $border_radius);
        }

        // Padding for links
        $padding = isset($settings['padding']) ? (int) $settings['padding'] : 0;
        if ($padding > 0) {
            $link_styles[] = sprintf('padding: %dpx;', $padding);
        }

        if (! empty($link_styles)) {
            $css[] = sprintf('.%1$s .pps-nav-links a { %2$s }', esc_attr($layout_class), implode(' ', $link_styles));
        }

        if (! empty($css)) {
            self::$dynamic_css[$layout_class] = implode("\n", $css);
        }
    }

    /**
     * Output collected dynamic CSS in the footer.
     */
    public static function output_dynamic_css()
    {
        if (empty(self::$dynamic_css)) {
            return;
        }

        echo '<style type="text/css" id="pps-series-post-navigation-dynamic-css">' . implode("\n", self::$dynamic_css) . '</style>';
    }

    /**
     * Normalize layout attribute to a post ID.
     *
     * @param string $raw Raw attribute value.
     *
     * @return int
     */
    public static function normalize_layout_id($raw)
    {
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        if (is_string($raw) && 0 === strpos($raw, 'pps_nav_')) {
            return (int) str_replace('pps_nav_', '', $raw);
        }

        return 0;
    }

    /**
     * Determine the series term to render for.
     *
     * @param string $series_attr Series attribute value.
     * @param WP_Post|null $post Post context.
     *
     * @return WP_Term|false
     */
    public static function resolve_series_term($series_attr, $post)
    {
        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');

        if (! empty($series_attr)) {
            if (is_numeric($series_attr)) {
                $term = get_term((int) $series_attr, $taxonomy_slug);
                if ($term && ! is_wp_error($term)) {
                    return $term;
                }
            } else {
                $term = get_term_by('slug', $series_attr, $taxonomy_slug);
                if ($term && ! is_wp_error($term)) {
                    return $term;
                }
            }
            return false;
        }

        if ($post instanceof WP_Post) {
            $terms = get_the_terms($post->ID, $taxonomy_slug);
            if (! empty($terms) && ! is_wp_error($terms)) {
                return reset($terms);
            }
        }

        if (is_tax($taxonomy_slug)) {
            $term = get_queried_object();
            if ($term && isset($term->term_id)) {
                return $term;
            }
        }

        return false;
    }
}

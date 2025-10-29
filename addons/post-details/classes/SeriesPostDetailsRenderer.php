<?php
/**
 * Renderer utilities for Series Post Details.
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/../includes/class-utilities.php';

class SeriesPostDetailsRenderer
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
        add_shortcode('pps_post_details', [__CLASS__, 'render_shortcode']);
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

        $base_file = PPS_Series_Post_Details_Utilities::get_module_path('post-details.php');
        $style_url = plugins_url('assets/css/series-post-details-frontend.css', $base_file);
        
        wp_enqueue_style('pps-series-post-details-frontend', $style_url, [], ORG_SERIES_VERSION);

        self::$assets_enqueued = true;
    }

    /**
     * Render the shortcode `[pps_post_details layout="..."]`.
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
        ], $atts, 'pps_post_details');

        if (empty($atts['layout'])) {
            return '<!-- Series Post Details: layout attribute missing -->';
        }

        $layout_id = self::normalize_layout_id($atts['layout']);
        if (! $layout_id || 'publish' !== get_post_status($layout_id)) {
            return '<!-- Series Post Details: invalid or unpublished layout -->';
        }

        $post = null;
        if (! empty($atts['post_id'])) {
            $post = get_post((int) $atts['post_id']);
        } elseif (is_singular()) {
            $post = get_queried_object();
        }

        $series_term = self::resolve_series_term($atts['series'], $post);
        if (! $series_term) {
            return '<!-- Series Post Details: unable to determine series -->';
        }

        $context = [
            'series_term' => $series_term,
            'post'        => $post,
            'context'     => 'shortcode',
        ];

        return self::render_layout_for_series($layout_id, $context, false);
    }

    /**
     * Render a layout for automatic meta injection.
     *
     * @param int   $layout_id Layout post ID.
     * @param array $context   Context array with `series_term`, `post` and optional flags.
     * @param bool  $for_excerpt Whether to use the excerpt template.
     *
     * @return string
     */
    public static function render_layout_for_series($layout_id, array $context, $for_excerpt)
    {
        $settings = PPS_Series_Post_Details_Utilities::get_post_details_settings($layout_id);
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

        // Check if we have legacy template fields or new visual controls
        $template_key = $for_excerpt ? 'meta_excerpt_template' : 'meta_template';
        $template     = isset($settings[$template_key]) ? $settings[$template_key] : '';

        $render_context = [
            'series_term' => $series_term,
            'post'        => $post,
            'series_id'   => $series_id,
            'post_id'     => $post_id,
            'excerpt'     => $for_excerpt,
        ];

        // Use visual controls if no legacy template is set
        if ('' === trim($template)) {
            $content = self::render_from_visual_controls($settings, $render_context);
        } else {
            // Fallback to legacy token template
            $content = self::render_template_with_tokens($template, $render_context);
        }
        if ('' === $content) {
            return '';
        }

        $layout_class = 'pps-series-post-details-' . $layout_id;
        self::capture_dynamic_css($layout_class, $settings);

        $variant = isset($settings['layout_variant']) ? sanitize_html_class($settings['layout_variant']) : 'classic';
        $wrapper_classes = [
            'pps-series-post-details',
            'pps-series-post-details-variant-' . $variant,
            $layout_class,
        ];

        if ($for_excerpt) {
            $wrapper_classes[] = 'pps-series-meta-excerpt';
        }

        $wrapper_attrs = [
            'class' => implode(' ', array_filter($wrapper_classes)),
            'data-series-id' => $series_id,
        ];

        $attributes = '';
        foreach ($wrapper_attrs as $name => $value) {
            $attributes .= sprintf(' %s="%s"', esc_attr($name), esc_attr($value));
        }

        // Wrap the meta box content (without %postcontent% inside)
        $output = sprintf('<div%s>%s</div>', $attributes, $content);
        
        $is_shortcode = isset($context['context']) && 'shortcode' === $context['context'];
        if (!$is_shortcode && false === strpos($output, '%postcontent%')) {
            $output .= '%postcontent%';
        }

        /**
         * Filter the rendered Series Post Details output.
         *
         * @param string $output    Rendered HTML.
         * @param int    $layout_id Layout post ID.
         * @param array  $settings  Layout settings array.
         * @param array  $context   Rendering context.
         * @param bool   $for_excerpt Whether rendering for excerpt.
         */
        return apply_filters('pps_series_post_details_render_layout', $output, $layout_id, $settings, $context, $for_excerpt);
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
        $series_part = isset($context['series_part']) ? (int) $context['series_part'] : 1;
        $variant     = isset($settings['layout_variant']) ? sanitize_html_class($settings['layout_variant']) : 'classic';

        // Use unique class for preview dynamic CSS to avoid conflicts with wrapper
        $layout_class = 'pps-series-post-details-preview-inner';
        self::capture_dynamic_css($layout_class, $settings);

        $template = isset($settings['meta_template']) ? $settings['meta_template'] : '';

        // Use visual controls if no legacy template is set
        if ('' === trim($template)) {
            $content = self::render_preview_from_visual_controls($settings, $context);
        } else {
            // Fallback to legacy token template
            $tokens = self::build_preview_tokens($series_term, $post, $series_part, $total_posts);
            $content = strtr($template, $tokens);
        }

        $wrapper_classes = [
            'pps-series-post-details',
            'pps-series-meta-preview',
            'pps-series-meta-variant-' . $variant,
            $layout_class,
        ];

        $attributes = sprintf(' class="%s"', esc_attr(implode(' ', $wrapper_classes)));

        // Wrap the meta box content - preview doesn't need %postcontent% placeholder
        return sprintf('<div%s>%s</div>', $attributes, $content);
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
        $series_part = isset($context['series_part']) ? (int) $context['series_part'] : 1;

        if (!$series_term || !isset($series_term->term_id)) {
            return '';
        }

        $content_parts = [];

        // Build the text content
        $text_before = isset($settings['text_before']) ? $settings['text_before'] : __('This entry is', 'organize-series');
        $text_after = isset($settings['text_after']) ? $settings['text_after'] : __('in the series', 'organize-series');
        $show_part = !empty($settings['show_part_number']);
        
        $text_content = esc_html($text_before) . ' ';
        
        if ($show_part) {
            $text_content .= sprintf(
                esc_html__('part %1$s of %2$s', 'organize-series'),
                $series_part,
                $total_posts
            ) . ' ';
        }
        
        $text_content .= esc_html($text_after) . ' ';
        $text_content .= '<a href="#">' . esc_html($series_term->name) . '</a>';
        
        $content_parts[] = '<div class="pps-series-meta-text">' . $text_content . '</div>';

        if (empty($content_parts)) {
            return '';
        }

        return '<div class="pps-series-meta-content">' . implode('', $content_parts) . '</div>';
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
        $for_excerpt = isset($context['excerpt']) ? $context['excerpt'] : false;

        if (!$series_term || !isset($series_term->term_id)) {
            return '';
        }

        $content_parts = [];

        // Build the text content
        $text_before = isset($settings['text_before']) ? $settings['text_before'] : __('This entry is', 'organize-series');
        $text_after = isset($settings['text_after']) ? $settings['text_after'] : __('in the series', 'organize-series');
        $show_part = !empty($settings['show_part_number']);
        
        $text_content = esc_html($text_before) . ' ';
        
        if ($show_part && $post_id && $series_id) {
            $series_part = '';
            $total_posts = '';
            
            if (function_exists('wp_series_part')) {
                $series_part = wp_series_part($post_id, $series_id);
            }
            
            if (function_exists('wp_postlist_count')) {
                $total_posts = wp_postlist_count($series_id);
            }
            
            if ($series_part && $total_posts) {
                $text_content .= sprintf(
                    esc_html__('part %1$s of %2$s', 'organize-series'),
                    $series_part,
                    $total_posts
                ) . ' ';
            }
        }
        
        $text_content .= esc_html($text_after) . ' ';
        $text_content .= sprintf(
            '<a href="%s">%s</a>',
            esc_url(get_term_link($series_term)),
            esc_html($series_term->name)
        );
        
        $content_parts[] = '<div class="pps-series-meta-text">' . $text_content . '</div>';

        if (empty($content_parts)) {
            return '';
        }

        return '<div class="pps-series-meta-content">' . implode('', $content_parts) . '</div>';
    }

    /**
     * Generate content via token_replace when available.
     *
     * @param string $template Template string.
     * @param array  $context  Context array.
     *
     * @return string
     */
    private static function render_template_with_tokens($template, array $context)
    {
        $series_id = isset($context['series_id']) ? (int) $context['series_id'] : 0;
        $post_id   = isset($context['post_id']) ? (int) $context['post_id'] : 0;
        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post = isset($context['post']) ? $context['post'] : null;

        // Strip slashes first
        $template = stripslashes($template);

        // Try token_replace function first
        if (function_exists('token_replace')) {
            $rendered = token_replace($template, 'other', $post_id, $series_id);

            // Check if token_replace actually did something (not just returned the original template)
            if ($rendered !== $template || !self::has_unreplaced_tokens($rendered)) {
                return $rendered;
            }
        }

        // Fallback: implement our own token replacement
        return self::replace_tokens_manually($template, $context);
    }

    /**
     * Check if template still contains unreplaced tokens
     *
     * @param string $content Content to check
     * @return bool
     */
    private static function has_unreplaced_tokens($content)
    {
        $common_tokens = [
            '%series_title%', '%series_title_linked%', '%post_title%', '%post_title_linked%',
            '%series_part%', '%total_posts_in_series%', '%series_description%',
            '%next_post%', '%previous_post%', '%series_icon%', '%series_icon_linked%'
        ];

        foreach ($common_tokens as $token) {
            if (strpos($content, $token) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Manual token replacement as fallback
     *
     * @param string $template Template string
     * @param array $context Context array
     * @return string
     */
    private static function replace_tokens_manually($template, array $context)
    {
        $series_term = isset($context['series_term']) ? $context['series_term'] : null;
        $post = isset($context['post']) ? $context['post'] : null;
        $series_id = isset($context['series_id']) ? (int) $context['series_id'] : 0;
        $post_id = isset($context['post_id']) ? (int) $context['post_id'] : 0;

        // Use current post if no specific post provided
        if (!$post && is_singular()) {
            $post = get_queried_object();
            $post_id = $post ? $post->ID : 0;
        }

        // Build token replacements
        $tokens = [];

        // Series tokens
        if ($series_term) {
            $tokens['%series_title%'] = esc_html($series_term->name);
            $tokens['%series_title_linked%'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(get_term_link($series_term)),
                esc_html($series_term->name)
            );
            $tokens['%series_description%'] = $series_term->description ? esc_html($series_term->description) : '';
        }

        // Post tokens
        if ($post) {
            $tokens['%post_title%'] = esc_html(get_the_title($post));
            $tokens['%post_title_linked%'] = sprintf(
                '<a href="%s">%s</a>',
                esc_url(get_permalink($post)),
                esc_html(get_the_title($post))
            );
        }

        // Series part and navigation
        if ($post_id && $series_id) {
            // Get series part
            if (function_exists('wp_series_part')) {
                $series_part = wp_series_part($post_id, $series_id);
                $tokens['%series_part%'] = $series_part ? esc_html($series_part) : '';
            }

            // Get navigation
            if (function_exists('wp_series_nav')) {
                $tokens['%next_post%'] = wp_series_nav($post_id, true);
                $tokens['%previous_post%'] = wp_series_nav($post_id, false);
            }
        }

        // Total posts in series
        if ($series_id && function_exists('wp_postlist_count')) {
            $tokens['%total_posts_in_series%'] = (string) wp_postlist_count($series_id);
        }

        // Series icon
        if ($series_id && function_exists('get_series_icon')) {
            global $orgseries;
            $settings = $orgseries ? $orgseries->settings : [];
            $icon_width = isset($settings['series_icon_width_series_page']) ? $settings['series_icon_width_series_page'] : 60;

            $tokens['%series_icon%'] = get_series_icon("fit_width={$icon_width}&link=0&series={$series_id}&display=0");
            $tokens['%series_icon_linked%'] = get_series_icon("fit_width={$icon_width}&series={$series_id}&display=0");
        }

        // Apply token replacements
        return strtr($template, $tokens);
    }

    /**
     * Prepare preview tokens for sample output.
     *
     * @param WP_Term|null $series_term Series term sample.
     * @param WP_Post|object|null $post Sample post.
     * @param int $series_part Current part sample.
     * @param int $total_posts Total posts sample.
     *
     * @return array
     */
    private static function build_preview_tokens($series_term, $post, $series_part, $total_posts)
    {
        $series_name = $series_term ? $series_term->name : __('Sample Series', 'organize-series');
        $series_link = '<a href="#">' . esc_html($series_name) . '</a>';

        $post_title = $post && isset($post->post_title) ? $post->post_title : __('Sample Post Title', 'organize-series');
        $post_link  = '<a href="#">' . esc_html($post_title) . '</a>';
        $post_excerpt = $post && isset($post->post_excerpt) ? $post->post_excerpt : __('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'organize-series');

        return [
            '%series_title%'                => esc_html($series_name),
            '%series_title_linked%'         => $series_link,
            '%series_icon%'                 => '<span class="pps-series-meta-icon">★</span>',
            '%series_icon_linked%'          => '<span class="pps-series-meta-icon">★</span>',
            '%post_title%'                  => esc_html($post_title),
            '%post_title_linked%'           => $post_link,
            '%post_excerpt%'                => esc_html($post_excerpt),
            '%postcontent%'                 => '%postcontent%',
            '%series_part%'                 => (string) $series_part,
            '%total_posts_in_series%'       => (string) $total_posts,
            '%previous_post%'               => '<span class="pps-series-meta-nav">' . esc_html__('Previous post link', 'organize-series') . '</span>',
            '%next_post%'                   => '<span class="pps-series-meta-nav">' . esc_html__('Next post link', 'organize-series') . '</span>',
            '%series_description%'          => esc_html__('Sample series description.', 'organize-series'),
            '%series_post_list%'            => '',
            '%series_table_of_contents%'    => '',
            '%list_of_series_posts%'        => '',
            '%series_post_navigation%'      => '',
        ];
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

        // Outer container styles (border, background, margin)
        $outer_parts = [];

        // Margin (on outer container) - uniform on all sides
        $margin = isset($settings['margin']) ? (int) $settings['margin'] : 0;
        if ($margin > 0) {
            $outer_parts[] = sprintf('margin: %dpx;', $margin);
        }

        // Background color (on outer container)
        if (! empty($settings['background_color'])) {
            $outer_parts[] = 'background-color: ' . esc_attr($settings['background_color']) . ';';
        }

        // Border (on outer container)
        $border_width = isset($settings['border_width']) ? (int) $settings['border_width'] : 0;
        if ($border_width > 0) {
            $border_color = ! empty($settings['border_color']) ? $settings['border_color'] : '#d8dee9';
            $outer_parts[] = sprintf('border: %dpx solid %s;', $border_width, esc_attr($border_color));
        }

        // Border radius (on outer container)
        if (! empty($settings['border_radius'])) {
            $outer_parts[] = 'border-radius: ' . (int) $settings['border_radius'] . 'px;';
        }

        // Inner content styles (padding, text color, text size)
        $inner_parts = [];

        // Padding (on inner content) - uniform on all sides
        $padding = isset($settings['padding']) ? (int) $settings['padding'] : 0;
        if ($padding > 0) {
            $inner_parts[] = sprintf('padding: %dpx;', $padding);
        }

        // Text color (on inner content)
        if (! empty($settings['text_color'])) {
            $inner_parts[] = 'color: ' . esc_attr($settings['text_color']) . ';';
        }
        
        // Text size (on inner content)
        if (! empty($settings['text_size'])) {
            $inner_parts[] = 'font-size: ' . (int) $settings['text_size'] . 'px;';
        }

        $css = [];
        
        // Apply outer styles to the meta box container
        if (! empty($outer_parts)) {
            $css[] = sprintf('.%1$s { %2$s }', esc_attr($layout_class), implode(' ', $outer_parts));
        }
        
        // Apply inner styles to the content wrapper
        if (! empty($inner_parts)) {
            $css[] = sprintf('.%1$s .pps-series-meta-content { %2$s }', esc_attr($layout_class), implode(' ', $inner_parts));
        }

        if (! empty($settings['link_color'])) {
            $link_color = esc_attr($settings['link_color']);
            $css[] = sprintf('.%1$s a, .%1$s a:visited { color: %2$s; }', esc_attr($layout_class), $link_color);
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

        echo '<style type="text/css" id="pps-series-post-details-dynamic-css">' . implode("\n", self::$dynamic_css) . '</style>';
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

        if (is_string($raw) && 0 === strpos($raw, 'pps_meta_box_')) {
            return (int) str_replace('pps_meta_box_', '', $raw);
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

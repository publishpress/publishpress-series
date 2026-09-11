<?php

/**
 * Verify editor requests use the styled preview when shortcode content exists.
 */

define('ABSPATH', '/wordpress/');
define('REST_REQUEST', true);

class WP_Post
{
}

class PPS_Post_List_Box_Fields
{
    public static function get_post_list_box_layout_meta_values($layout_id)
    {
        return ['thumbnail_width' => 80];
    }
}

class PPS_Post_List_Box_Preview
{
    public static function get_sample_series_posts($series_id, $settings)
    {
        return [];
    }

    public static function render_preview_content($settings, $posts)
    {
        return '<div class="styled-editor-preview" style="width: 80px;">Preview</div>';
    }
}

class PostListBoxRenderer
{
    public static $assets_enqueued = false;

    public static function enqueue_frontend_styles()
    {
        self::$assets_enqueued = true;
    }
}

class SeriesPostDetailsRenderer
{
    public static $assets_enqueued = false;

    public static function enqueue_frontend_assets()
    {
        self::$assets_enqueued = true;
    }
}

class PostNavigationRenderer
{
    public static $assets_enqueued = false;

    public static function enqueue_frontend_assets()
    {
        self::$assets_enqueued = true;
    }
}

function absint($value)
{
    return abs((int) $value);
}

function current_user_can($capability)
{
    return true;
}

function do_shortcode($shortcode)
{
    return '<div class="frontend-shortcode"><img src="large.jpg"></div>';
}

function esc_attr($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function get_post($post_id)
{
    return (object) [
        'ID'          => $post_id,
        'post_type'   => 'pps_post_list_box',
        'post_status' => 'publish',
    ];
}

function get_terms($args)
{
    return [];
}

function is_wp_error($value)
{
    return false;
}

function shortcode_exists($shortcode)
{
    return true;
}

function wp_enqueue_script($handle)
{
}

function wp_enqueue_style($handle)
{
}

function __($text)
{
    return $text;
}

require_once dirname(__DIR__, 2) . '/includes-core/class-blocks.php';

$html = \PublishPress\Series\Blocks::renderPostListBox(['layoutId' => 123]);

if (false === strpos($html, 'styled-editor-preview')) {
    fwrite(STDERR, "Editor rendered the unstyled shortcode output instead of its styled preview.\n");
    exit(1);
}

\PublishPress\Series\Blocks::enqueueEditorAssets();

if (! PostListBoxRenderer::$assets_enqueued
    || ! SeriesPostDetailsRenderer::$assets_enqueued
    || ! PostNavigationRenderer::$assets_enqueued
) {
    fwrite(STDERR, "Editor did not enqueue all feature frontend styles.\n");
    exit(1);
}

echo "Editor rendered the styled feature preview.\n";

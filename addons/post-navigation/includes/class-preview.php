<?php
/**
 * Preview rendering helpers for Series Post Navigation
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Navigation_Preview
{
    /**
     * Render preview metabox content
     */
    public static function render_preview(WP_Post $post)
    {
        $settings = PPS_Series_Post_Navigation_Utilities::get_post_navigation_settings(
            $post->ID,
            $post->post_status === 'auto-draft'
        );

        $series_term = PPS_Series_Post_Navigation_Utilities::ensure_sample_series_term();

        if (! $series_term) {
            echo '<p>' . esc_html__('Create a series to preview the navigation.', 'publishpress-series') . '</p>';
            return;
        }

        $sample_posts = PPS_Series_Post_Navigation_Utilities::get_sample_series_posts($series_term->term_id);
        $total_posts = count($sample_posts);

        if ($total_posts === 0) {
            echo '<p>' . esc_html__('Create posts in a series to preview the navigation.', 'publishpress-series') . '</p>';
            return;
        }

        $current_index = $total_posts > 1 ? 1 : 0;
        $current_post = $sample_posts[$current_index];

        $previous_post = ($current_index + 1 < $total_posts) ? $sample_posts[$current_index + 1] : null;
        $next_post     = ($current_index - 1) >= 0 ? $sample_posts[$current_index - 1] : null;
        $first_post    = $sample_posts[$total_posts - 1];

        if (! $current_post) {
            echo '<p>' . esc_html__('Create posts in a series to preview the navigation.', 'publishpress-series') . '</p>';
            return;
        }

        echo '<div class="pps-series-post-navigation-preview">';
        echo PostNavigationRenderer::render_from_settings(
            $settings,
            [
                'series_term' => $series_term,
                'post'        => $current_post,
                'total_posts' => $total_posts,
                'preview_posts' => [
                    'current'  => $current_post,
                    'previous' => $previous_post,
                    'next'     => $next_post,
                    'first'    => $first_post,
                ],
                'context'     => 'preview',
            ]
        );
        PostNavigationRenderer::output_dynamic_css();
        echo '</div>';
    }
}

<?php
/**
 * Preview rendering helpers for Series Post Details
 */

if (! defined('ABSPATH')) {
    exit;
}

class PPS_Series_Post_Details_Preview
{
    /**
     * Render preview metabox content
     */
    public static function render_preview(WP_Post $post)
    {
        $settings = PPS_Series_Post_Details_Utilities::get_post_details_settings(
            $post->ID,
            $post->post_status === 'auto-draft'
        );

        $series_term = PPS_Series_Post_Details_Utilities::ensure_sample_series_term();

        if (! $series_term) {
            echo '<p>' . esc_html__('Create a series to preview the post details.', 'organize-series') . '</p>';
            return;
        }

        $sample_posts = PPS_Series_Post_Details_Utilities::get_sample_series_posts($series_term->term_id);
        $current_post = $sample_posts ? $sample_posts[0] : null;

        echo '<div class="pps-series-post-details-preview">';
        echo SeriesPostDetailsRenderer::render_from_settings(
            $settings,
            [
                'series_term' => $series_term,
                'post'        => $current_post,
                'total_posts' => count($sample_posts),
                'series_part' => 1,
                'context'     => 'preview',
            ]
        );
        SeriesPostDetailsRenderer::output_dynamic_css();
        echo '</div>';
    }
}

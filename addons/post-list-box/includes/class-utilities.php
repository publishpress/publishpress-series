<?php
/**
 * Utility Functions for Post List Box
 */

class PPS_Post_List_Box_Utilities {

    const POST_TYPE_BOXES = 'pps_post_list_box';

    /**
     * Get module URL
     *
     * @param string $file
     * @return string
     */
    public static function get_module_url($file)
    {
        return plugins_url('', $file);
    }

    /**
     * Get Post List Boxes
     *
     * @param boolean $ids_only
     * @return array
     */
    public static function get_post_list_boxes($ids_only = false)
    {
        $post_args = [
            'post_type'         => self::POST_TYPE_BOXES,
            'posts_per_page'    => -1,
            'post_status'       => 'publish',
            'orderby'           => 'menu_order',
            'order'             => 'ASC'
        ];

        if ($ids_only) {
            $post_args['fields'] = 'ids';
            return get_posts($post_args);
        }

        $posts = get_posts($post_args);

        $post_list_boxes = [];

        if (! empty($posts)) {
            foreach ($posts as $post) {
                $post_list_boxes[self::POST_TYPE_BOXES . '_' . $post->ID] = $post->post_title;
            }
        }

        return $post_list_boxes;
    }

    /**
     * Get current post highlighting data
     *
     * @param array $settings
     * @param object $post
     * @param int $index
     * @param int|null $current_post_id
     * @return array Array with 'is_current' boolean and 'styles' array
     */
    public static function get_current_post_highlighting($settings, $post, $index, $current_post_id = null)
    {
        $is_current_post = false;
        $post_item_styles = [];
        
        if (!empty($settings['highlight_current_post'])) {
            if ($current_post_id && $current_post_id == $post->ID) {
                // Real current post detection
                $is_current_post = true;
            } elseif (!$current_post_id && $index === 0) {
                // Preview mode: highlight first post when no current post detected
                $is_current_post = true;
            }
            
            if ($is_current_post) {
                // Apply custom colors if set
                if (!empty($settings['current_post_bg_color'])) {
                    $post_item_styles[] = 'background-color: ' . esc_attr($settings['current_post_bg_color']) . ';';
                }
                if (!empty($settings['current_post_border_color'])) {
                    $post_item_styles[] = 'border-color: ' . esc_attr($settings['current_post_border_color']) . ';';
                }
            }
        }
        
        return [
            'is_current' => $is_current_post,
            'styles' => $post_item_styles
        ];
    }

    /**
     * Get the title text based on title type setting
     *
     * @param array $settings Layout settings
     * @param array $posts Posts array (used to get series info)
     * @return string
     */
    public static function get_title_text($settings, $posts)
    {
        $title_type = isset($settings['title_type']) ? $settings['title_type'] : 'series';
        
        if ($title_type === 'custom') {
            // Use custom title text
            return isset($settings['title_text']) ? $settings['title_text'] : '';
        }
        
        // Use series title (default behavior)
        if (!empty($posts)) {
            // Get series from the first post
            $first_post = $posts[0];
            $series_terms = get_the_terms($first_post->ID, 'series');
            
            if ($series_terms && !is_wp_error($series_terms)) {
                return $series_terms[0]->name;
            }
        }
        
        // Fallback to custom title if series title not found
        return isset($settings['title_text']) ? $settings['title_text'] : __('Series Posts', 'organize-series');
    }

    /**
     * Get the default Post List Box ID
     *
     * @return int|null The ID of the default "Default List Box" or null if not found
     */
    public static function get_default_post_list_box_id()
    {
        $default_box = get_page_by_path('default-list-box', OBJECT, self::POST_TYPE_BOXES);
        return $default_box ? (int) $default_box->ID : null;
    }

    /**
     * Build a safe, trimmed excerpt from a post or preview object.
     *
     * - Clamps length to 10–500 words
     * - Uses raw DB fields for real posts to avoid filters/recursion
     * - Supports preview/sample objects without numeric IDs
     * - Strips Gutenberg blocks (when available), shortcodes, and HTML tags
     * - Returns plain text; caller should apply escaping/formatting
     * 
     * I can't use get_the_excerpt() because it applies the 'the_excerpt' filter, which causes recursion loop and memory leak.
     *
     * @param int|WP_Post|object $post_or_id Post ID, WP_Post, or sample stdClass
     * @param int $length Desired word length
     * @return string Trimmed plain-text excerpt
     */
    public static function build_safe_excerpt($post_or_id, $length = 55)
    {
        $length = intval($length);
        $length = max(10, min(500, $length));

        $raw_text = '';

        // Real post by ID
        if (is_numeric($post_or_id)) {
            $post_id = intval($post_or_id);
            $raw_text = get_post_field('post_excerpt', $post_id, 'raw');
            if ($raw_text === '' || $raw_text === null) {
                $raw_text = get_post_field('post_content', $post_id, 'raw');
            }
        } elseif (is_object($post_or_id)) {
            // Real post object with numeric ID
            if (isset($post_or_id->ID) && is_numeric($post_or_id->ID)) {
                $post_id = intval($post_or_id->ID);
                $raw_text = get_post_field('post_excerpt', $post_id, 'raw');
                if ($raw_text === '' || $raw_text === null) {
                    $raw_text = get_post_field('post_content', $post_id, 'raw');
                }
            }

            // Fallback for sample/preview objects
            if ($raw_text === '' || $raw_text === null) {
                if (isset($post_or_id->post_excerpt) && $post_or_id->post_excerpt !== '') {
                    $raw_text = (string) $post_or_id->post_excerpt;
                } elseif (isset($post_or_id->post_content)) {
                    $raw_text = (string) $post_or_id->post_content;
                }
            }
        }

        if (!is_string($raw_text)) {
            $raw_text = '';
        }

        // Remove blocks when available, then shortcodes and tags
        if (function_exists('excerpt_remove_blocks')) {
            $raw_text = excerpt_remove_blocks($raw_text);
        }
        $raw_text = strip_shortcodes($raw_text);
        $raw_text = wp_strip_all_tags($raw_text);

        // Trim to target word count
        return wp_trim_words($raw_text, $length);
    }
}
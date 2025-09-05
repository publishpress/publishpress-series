<?php
/**
 * Preview System for Post List Box
 */

if (!class_exists('PPS_Post_List_Box_Utilities')) {
    require_once __DIR__ . '/class-utilities.php';
}

class PPS_Post_List_Box_Preview {

    /**
     * Get sample posts for a series
     *
     * @param int $series_id
     * @return array
     */
    public static function get_sample_series_posts($series_id)
    {
        $posts = get_posts([
            'post_type' => 'post',
            'tax_query' => [
                [
                    'taxonomy' => 'series',
                    'field' => 'term_id',
                    'terms' => $series_id,
                ],
            ],
            'posts_per_page' => 4,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

        // If no posts found, create sample posts
        if (empty($posts)) {
            return self::get_sample_posts();
        }

        return $posts;
    }

    /**
     * Get sample posts for preview
     *
     * @return array
     */
    public static function get_sample_posts()
    {
        // Create sample post objects for preview
        $sample_posts = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $post = new stdClass();
            $post->ID = 'sample_' . $i;
            $post->post_title = sprintf(__('Sample Post %d', 'organize-series'), $i);
            $post->post_content = sprintf(__('This is sample content for post %d in the series. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'organize-series'), $i);
            $post->post_excerpt = sprintf(__('This is a sample excerpt for post %d in the series.', 'organize-series'), $i);
            $post->post_author = get_current_user_id() ?: 1;
            $post->post_date = date('Y-m-d H:i:s', strtotime('-' . $i . ' days'));
            $post->post_status = 'publish';
            $post->post_type = 'post';
            $post->post_name = 'sample-post-' . $i;
            
            // Mock featured image
            $post->thumbnail_id = 0;
            
            $sample_posts[] = $post;
        }
        
        return $sample_posts;
    }

    /**
     * Build inline style attribute for wrapper (background, border, radius, padding)
     * Mirrors frontend renderer behavior for admin preview.
     *
     * @param array $settings
     * @return string style attribute or empty string
     */
    public static function get_inline_styles($settings)
    {
        $styles = [];

        if (!empty($settings['background_color'])) {
            $styles[] = 'background-color: ' . esc_attr($settings['background_color']) . ';';
        }

        if (!empty($settings['border_width']) && intval($settings['border_width']) > 0) {
            $border_width = intval($settings['border_width']) . 'px';
            $border_color = !empty($settings['border_color']) ? $settings['border_color'] : '#e5e5e5';
            $styles[] = 'border: ' . $border_width . ' solid ' . esc_attr($border_color) . ';';
        }

        if (!empty($settings['border_radius'])) {
            $styles[] = 'border-radius: ' . intval($settings['border_radius']) . 'px;';
        }

        if (!empty($settings['padding'])) {
            $styles[] = 'padding: ' . intval($settings['padding']) . 'px;';
        }

        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Title style (color, font-size)
     */
    public static function get_title_styles($settings)
    {
        $styles = [];
        if (!empty($settings['title_color'])) {
            $styles[] = 'color: ' . esc_attr($settings['title_color']) . ';';
        }
        if (!empty($settings['title_font_size'])) {
            $styles[] = 'font-size: ' . intval($settings['title_font_size']) . 'px;';
        }
        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Post title style (color, font-size)
     */
    public static function get_post_title_styles($settings)
    {
        $styles = [];
        if (!empty($settings['post_title_color'])) {
            $styles[] = 'color: ' . esc_attr($settings['post_title_color']) . ';';
        }
        if (!empty($settings['post_title_font_size'])) {
            $styles[] = 'font-size: ' . intval($settings['post_title_font_size']) . 'px;';
        }
        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Excerpt style (color)
     */
    public static function get_excerpt_styles($settings)
    {
        $styles = [];
        if (!empty($settings['excerpt_color'])) {
            $styles[] = 'color: ' . esc_attr($settings['excerpt_color']) . ';';
        }
        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Generate container styles including gap for preview
     *
     * @param array $settings Layout settings
     * @return string
     */
    public static function get_container_styles($settings)
    {
        $styles = [];

        $layout_style = isset($settings['layout_style']) ? $settings['layout_style'] : 'list';
        $gap = isset($settings['gap_between_items']) ? intval($settings['gap_between_items']) : 10;

        if ($layout_style === 'grid') {
            $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
            $styles[] = 'display: grid;';
            $styles[] = 'grid-template-columns: repeat(' . $columns . ', 1fr);';
            $styles[] = 'gap: ' . $gap . 'px;';
        } else {
            $styles[] = 'display: flex;';
            $styles[] = 'flex-direction: column;';
            $styles[] = 'gap: ' . $gap . 'px;';
        }

        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Generate dynamic CSS for item styling
     *
     * @param array $settings Layout settings
     * @return string
     */
    public static function get_item_styles($settings)
    {
        $styles = [];

        // Post list item background color
        if (!empty($settings['post_list_background_color'])) {
            $styles[] = 'background-color: ' . esc_attr($settings['post_list_background_color']) . ';';
        }

        // Item padding
        if (!empty($settings['item_padding'])) {
            $styles[] = 'padding: ' . intval($settings['item_padding']) . 'px;';
        }

        // Item border
        if (!empty($settings['item_border_width']) && $settings['item_border_width'] > 0) {
            $border_width = intval($settings['item_border_width']) . 'px';
            $border_color = !empty($settings['item_border_color']) ? $settings['item_border_color'] : '#e5e5e5';
            $styles[] = 'border: ' . $border_width . ' solid ' . esc_attr($border_color) . ';';
        }

        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Generate thumbnail styles for preview
     *
     * @param array $settings Layout settings
     * @return string
     */
    public static function get_thumbnail_styles($settings)
    {
        $styles = [];

        $thumbnail_width = isset($settings['thumbnail_width']) ? intval($settings['thumbnail_width']) : 150;
        $thumbnail_height = isset($settings['thumbnail_height']) ? intval($settings['thumbnail_height']) : 150;

        $styles[] = 'width: ' . $thumbnail_width . 'px;';
        $styles[] = 'height: ' . $thumbnail_height . 'px;';
        $styles[] = 'object-fit: cover;';

        return empty($styles) ? '' : ' style="' . implode(' ', $styles) . '"';
    }

    /**
     * Render preview content based on settings
     *
     * @param array $settings
     * @param array $posts
     * @return string
     */
    public static function render_preview_content($settings, $posts = [])
    {
        ob_start();

        $layout_style = isset($settings['layout_style']) ? $settings['layout_style'] : 'list';


        $wrapper_classes = [
            'pps-post-list-box',
            'pps-layout-' . $layout_style,
        ];
        $inline_styles = self::get_inline_styles($settings);

        echo '<div class="' . esc_attr(implode(' ', array_filter($wrapper_classes))) . '"' . $inline_styles . '>';

        // Determine posts to render first
        $posts_to_render = [];
        if (!empty($posts)) {
            $posts_to_render = $posts;
        } elseif (!empty($settings['post_ids'])) {
            $post_ids = explode(',', $settings['post_ids']);
            $posts_to_render = get_posts(['post__in' => $post_ids, 'post_type' => 'any', 'orderby' => 'post__in']);
        } elseif (empty($posts) && (isset($_GET['action']) && $_GET['action'] === 'elementor')) {
            $posts_to_render = self::get_sample_posts();
        }

        // Title
        if (!empty($settings['title_show'])) {
            $title_text = PPS_Post_List_Box_Utilities::get_title_text($settings, $posts_to_render);
            if (!empty($title_text)) {
                $title_html_tag = isset($settings['title_html_tag']) ? $settings['title_html_tag'] : 'h3';
                $title_styles = self::get_title_styles($settings);
                echo '<' . esc_attr($title_html_tag) . ' class="pps-post-list-title"' . $title_styles . '>' . esc_html($title_text) . '</' . esc_attr($title_html_tag) . '>';
            }
        }

        // Posts container
        $container_styles = self::get_container_styles($settings);
        echo '<div class="pps-post-list ' . esc_attr($layout_style) . '"' . $container_styles . '>';

        foreach ($posts_to_render as $index => $post) {
            self::render_preview_post_item($settings, $post, $index);
        }

        echo '</div>'; // .pps-post-list

        // Custom CSS
        if (!empty($settings['custom_css'])) {
            echo '<style type="text/css">' . wp_strip_all_tags($settings['custom_css']) . '</style>';
        }

        echo '</div>'; // .pps-post-list-box

        return ob_get_clean();
    }

    /**
     * Render individual post item for preview
     *
     * @param array $settings
     * @param object $post
     * @param int $index
     */
    private static function render_preview_post_item($settings, $post, $index = 0)
    {
        $item_classes = ['pps-post-list-item'];
        
        // Get highlighting data using the centralized helper
        $highlighting = PPS_Post_List_Box_Utilities::get_current_post_highlighting($settings, $post, $index, null);
        
        if ($highlighting['is_current']) {
            $item_classes[] = 'current-post';
        }
        
        // Combine item styles with highlighting styles
        $item_styles = self::get_item_styles($settings);
        $item_style_array = [];
        if (!empty($item_styles)) {
            $item_style_array[] = trim(str_replace(['style="', '"'], '', $item_styles));
        }
        if (!empty($highlighting['styles'])) {
            $item_style_array = array_merge($item_style_array, $highlighting['styles']);
        }
        
        $item_style_attr = !empty($item_style_array) ? ' style="' . implode(' ', $item_style_array) . '"' : '';
        echo '<div class="' . esc_attr(implode(' ', $item_classes)) . '"' . $item_style_attr . '>';

        // Thumbnail
        if (!empty($settings['show_post_thumbnail'])) {
            echo '<div class="pps-post-thumbnail">';
            if (is_object($post) && isset($post->ID) && is_numeric($post->ID) && has_post_thumbnail($post->ID)) {
                $thumbnail_styles = self::get_thumbnail_styles($settings);
                echo '<a href="' . esc_url(get_permalink($post->ID)) . '">';
                echo get_the_post_thumbnail($post->ID, 'thumbnail', ['style' => trim(str_replace(['style="', '"'], '', $thumbnail_styles))]);
                echo '</a>';
            } else {
                $thumbnail_styles = self::get_thumbnail_styles($settings);

                $placeholder_url = plugin_dir_url(__FILE__) . '../assets/images/placeholder.svg';
                echo '<img src="' . esc_url($placeholder_url) . '" alt="' . esc_attr(isset($post->post_title) ? $post->post_title : '') . '" class="pps-post-thumbnail-img" style="' . trim(str_replace(['style="', '"'], '', $thumbnail_styles)) . '" />';
            }
            echo '</div>';
        }

        echo '<div class="pps-post-content">';

        if (!empty($settings['show_post_titles'])) {
            $post_title_styles = self::get_post_title_styles($settings);
            $is_current_post = in_array('current-post', $item_classes);
            
            if ($is_current_post && !empty($settings['current_post_text_color'])) {
                $current_title_color = 'color: ' . esc_attr($settings['current_post_text_color']) . ';';
                $post_title_styles = !empty($post_title_styles) ? str_replace('style="', 'style="' . $current_title_color, $post_title_styles) : ' style="' . $current_title_color . '"';
            }
            
            echo '<h4 class="pps-post-title"' . $post_title_styles . '>' . esc_html(isset($post->post_title) ? $post->post_title : '') . '</h4>';
        }

        // Excerpt
        if (!empty($settings['show_post_excerpt'])) {
            $excerpt_length = isset($settings['excerpt_length']) ? intval($settings['excerpt_length']) : 55;
            $excerpt_text = PPS_Post_List_Box_Utilities::build_safe_excerpt($post, $excerpt_length);
            $excerpt_styles = self::get_excerpt_styles($settings);
            echo '<div class="pps-post-excerpt"' . $excerpt_styles . '>' . wpautop(esc_html($excerpt_text)) . '</div>';
        }

        // Data and author
        if (!empty($settings['show_post_author']) || !empty($settings['show_post_date'])) {
            echo '<div class="pps-post-meta">';
            if (!empty($settings['show_post_author'])) {
                $author_name = (isset($post->post_author) && get_userdata($post->post_author)) ? get_userdata($post->post_author)->display_name : __('Author', 'organize-series');
                echo '<span class="pps-post-author">' . esc_html($author_name) . '</span>';
            }
            if (!empty($settings['show_post_date'])) {
                $post_date = isset($post->post_date) && is_string($post->post_date) ? $post->post_date : date('Y-m-d H:i:s');
                echo '<span class="pps-post-date">' . esc_html(date('F j, Y', strtotime($post_date))) . '</span>';
            }
            echo '</div>';
        }

        echo '</div>'; // .pps-post-content
        echo '</div>'; // .pps-post-list-item
    }
}
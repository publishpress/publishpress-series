<?php
/**
 * Post List Box Renderer class
 * Handles rendering of post list boxes
 */

// Include the necessary classes
if (!class_exists('PPS_Post_List_Box_Fields')) {
    require_once __DIR__ . '/../includes/class-fields.php';
}
if (!class_exists('PPS_Post_List_Box_Utilities')) {
    require_once __DIR__ . '/../includes/class-utilities.php';
}

class PostListBoxRenderer
{
    /**
     * Initialize the renderer
     */
    public static function init()
    {
        add_shortcode('pps_post_list_box', [__CLASS__, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_styles']);
        add_action('wp_footer', [__CLASS__, 'output_dynamic_css']);
    }

    /**
     * Enqueue frontend styles
     */
    public static function enqueue_frontend_styles()
    {
        $css_url = plugins_url('../assets/css/post-list-box-frontend.css', __FILE__);
        
        wp_enqueue_style(
            'pps-post-list-box-frontend',
            $css_url,
            [],
            ORG_SERIES_VERSION
        );
    }

    /**
     * Render the post list box shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public static function render_shortcode($atts)
    {
        
        $atts = shortcode_atts([
            'layout' => '',
            'series' => '',
            'posts_per_page' => -1,
            'class' => '',
        ], $atts, 'pps_post_list_box');

        if (empty($atts['layout'])) {
            return '<!-- Post List Box: No layout specified -->';
        }

        // Extract post ID from layout slug
        $layout_slug = $atts['layout'];
        $post_id = str_replace('pps_post_list_box_', '', $layout_slug);

        if (!is_numeric($post_id)) {
            return '<!-- Post List Box: Invalid post ID format -->';
        }

        $post_id = intval($post_id);
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'pps_post_list_box') {
            return '<!-- Post List Box: Invalid post or wrong post type -->';
        }

        // Get layout settings
        $settings = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post_id);

        $taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');

        // Get series ID
        $series_id = null;
        
        // If series parameter is provided, use it
        if (!empty($atts['series'])) {
            $series = get_term_by('slug', $atts['series'], $taxonomy_slug);
            if ($series) {
                $series_id = $series->term_id;
            }
        } else {
            // Try to get current series from context
            $queried_object = get_queried_object();
            if ($queried_object && isset($queried_object->term_id) && $queried_object->taxonomy === $taxonomy_slug) {
                $series_id = $queried_object->term_id;
            } else {
                // Automatically detect series from the current post
                global $post;
                if ($post && is_singular()) {
                    $series = get_the_terms($post->ID, $taxonomy_slug);
                    if ($series && !is_wp_error($series)) {
                        $series_id = $series[0]->term_id;
                    }
                }
            }
        }

        if (!$series_id) {
            return '';
        }

        // Get posts from series
        $posts = self::get_series_posts($series_id, $settings);

        if (empty($posts)) {
            return '';
        }

        return self::render_html($posts, $settings, $atts['class'], $post_id);
    }

    /**
     * Get posts from series
     *
     * @param int $series_id Series ID
     * @param array $settings Layout settings
     * @return array
     */
    private static function get_series_posts($series_id, $settings)
    {
        $orderby = isset($settings['orderby']) ? $settings['orderby'] : 'series_order';
        $order = isset($settings['order']) ? $settings['order'] : 'ASC';

        $maximum_items = isset($settings['maximum_items']) && $settings['maximum_items'] !== '' ? (int) $settings['maximum_items'] : -1;
        
        if ($orderby === 'series_order') {

            $args = [
                'post_type' => 'post',
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => $taxonomy_slug,
                        'field' => 'term_id',
                        'terms' => $series_id,
                    ],
                ],
                'posts_per_page' => $maximum_items,
                'orderby' => 'date', 
                'order' => 'DESC'
            ];

            $posts = get_posts($args);

            if (!empty($posts)) {
                
                $post_ids = array_map(function($post) {
                    return $post->ID;
                }, $posts);

                $series_posts = get_series_order($post_ids, 0, $series_id, false, true);

                $sorted_posts = [];
                foreach ($series_posts as $series_post) {
                    $post = get_post($series_post['id']);
                    if ($post) {
                        $sorted_posts[] = $post;
                    }
                }

                if ($order === 'DESC') {
                    $sorted_posts = array_reverse($sorted_posts);
                }

                return $sorted_posts;
            }

            return [];
        }

        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => $taxonomy_slug,
                    'field' => 'term_id',
                    'terms' => $series_id,
                ],
            ],
            'orderby' => $orderby,
            'order' => $order,
            'posts_per_page' => $maximum_items,
        ];

        return get_posts($args);
    }

    /**
     * Render the HTML
     *
     * @param array $posts Posts to display
     * @param array $settings Layout settings
     * @param string $additional_class Additional CSS classes
     * @return string
     */
    private static function render_html($posts, $settings, $additional_class = '', $post_id = null)
    {
        ob_start();

        // Generate dynamic CSS class
        $css_class = '';
        if ($post_id) {
            $css_class = self::generate_dynamic_css($post_id, $settings);
        }

        // Ensure layout_style is properly set
        $layout_style = !empty($settings['layout_style']) ? $settings['layout_style'] : 'list';
        $wrapper_class = 'pps-post-list-box pps-layout-' . esc_attr($layout_style);
        if (!empty($css_class)) {
            $wrapper_class .= ' ' . $css_class;
        }
        if (!empty($additional_class)) {
            $wrapper_class .= ' ' . esc_attr($additional_class);
        }

        ?>
        <div class="<?php echo esc_attr($wrapper_class); ?>">
            <?php if (!empty($settings['title_show'])) : 
                $title_text = PPS_Post_List_Box_Utilities::get_title_text($settings, $posts);
                if (!empty($title_text)) :
            ?>
                <<?php echo esc_html($settings['title_html_tag'] ?: 'h3'); ?> class="pps-post-list-title">
                    <?php echo esc_html($title_text); ?>
                </<?php echo esc_html($settings['title_html_tag'] ?: 'h3'); ?>>
            <?php endif; endif; ?>

            <div class="pps-post-list <?php echo esc_attr($layout_style); ?>">
                <?php 
                // Get current post ID for highlighting
                $current_post_id = get_queried_object_id();
                if (!$current_post_id && is_singular()) {
                    global $post;
                    if ($post) {
                        $current_post_id = $post->ID;
                    }
                }
                
                foreach ($posts as $index => $post) : setup_postdata($post); 

                    $highlighting = PPS_Post_List_Box_Utilities::get_current_post_highlighting($settings, $post, $index, $current_post_id);
                    
                    $post_item_class = 'pps-post-item';
                    if ($highlighting['is_current']) {
                        $post_item_class .= ' current-post';
                    }
                ?>
                    <div class="<?php echo esc_attr($post_item_class); ?>">
                        <?php if (!empty($settings['show_post_thumbnail'])) : ?>
                            <div class="pps-post-thumbnail">
                                <?php if (has_post_thumbnail($post->ID)) : ?>
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                        <?php echo get_the_post_thumbnail($post->ID, 'large'); ?>
                                    </a>
                                <?php else : ?>
                                    <?php 
                                        $fallback_image_id = !empty($settings['fallback_featured_image']) ? intval($settings['fallback_featured_image']) : 0;
                                        if ($fallback_image_id > 0) {
                                            $fallback_image = wp_get_attachment_image_src($fallback_image_id, 'large');
                                            $fallback_url = $fallback_image ? $fallback_image[0] : plugin_dir_url(__FILE__) . '../assets/images/placeholder.svg';
                                        } else {
                                            $fallback_url = plugin_dir_url(__FILE__) . '../assets/images/placeholder.svg';
                                        }
                                    ?>
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                        <img src="<?php echo esc_url($fallback_url); ?>" alt="<?php echo esc_attr(get_the_title($post->ID)); ?>" />
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="pps-post-content">
                            <?php if (!empty($settings['show_post_titles'])) : ?>
                                <h4 class="pps-post-title">
                                    <a href="<?php echo esc_url(get_permalink($post->ID)); ?>">
                                        <?php echo esc_html(get_the_title($post->ID)); ?>
                                    </a>
                                </h4>
                            <?php endif; ?>

                            <?php if (!empty($settings['show_post_excerpt'])) : ?>
                                <div class="pps-post-excerpt">
                                    <?php 
                                    $excerpt_length = isset($settings['excerpt_length']) ? intval($settings['excerpt_length']) : 55;
                                    $excerpt_text = PPS_Post_List_Box_Utilities::build_safe_excerpt($post, $excerpt_length);
                                    echo wpautop(esc_html($excerpt_text));
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($settings['show_post_author']) || !empty($settings['show_post_date'])) : ?>
                                <div class="pps-post-meta">
                                    <?php if (!empty($settings['show_post_author'])) : ?>
                                        <span class="pps-post-author">
                                            <?php _e('By', 'organize-series'); ?> <?php the_author_meta('display_name', $post->post_author); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if (!empty($settings['show_post_date'])) : ?>
                                        <span class="pps-post-date">
                                            <?php echo get_the_date('', $post->ID); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; wp_reset_postdata(); ?>
            </div>
        </div>

        <?php
        return ob_get_clean();
    }

    /**
     * Store dynamic CSS for output
     * @var array
     */
    private static $dynamic_css = [];

    /**
     * Output dynamic CSS in footer
     */
    public static function output_dynamic_css()
    {
        if (empty(self::$dynamic_css)) {
            return;
        }

        $css = implode("\n", self::$dynamic_css);
        echo '<style type="text/css" id="pps-post-list-box-dynamic-css">' . $css . '</style>';
    }

    /**
     * Generate dynamic CSS for a post list box instance
     *
     * @param int $post_id Post ID
     * @param array $settings Layout settings
     * @return string CSS class name
     */
    private static function generate_dynamic_css($post_id, $settings)
    {
        $css_class = 'pps-post-list-box-' . $post_id;
        
        $css_parts = [];

        // Container styles
        $container_styles = [];
        if (!empty($settings['background_color'])) {
            $container_styles[] = 'background-color: ' . esc_attr($settings['background_color']) . ';';
        }
        if (!empty($settings['border_width']) && $settings['border_width'] > 0) {
            $border_width = intval($settings['border_width']) . 'px';
            $border_color = !empty($settings['border_color']) ? $settings['border_color'] : '#e5e5e5';
            $container_styles[] = 'border: ' . $border_width . ' solid ' . esc_attr($border_color) . ';';
        }
        if (!empty($settings['border_radius'])) {
            $container_styles[] = 'border-radius: ' . intval($settings['border_radius']) . 'px;';
        }
        if (!empty($settings['padding'])) {
            $container_styles[] = 'padding: ' . intval($settings['padding']) . 'px;';
        }

        if (!empty($container_styles)) {
            $css_parts[] = '.' . $css_class . '{ ' . implode(' ', $container_styles) . ' }';
        }

        // Layout styles to ensure they override properly
        $gap = isset($settings['gap_between_items']) ? intval($settings['gap_between_items']) : 10;
        $css_parts[] = '.' . $css_class . ' .pps-post-list{ display: flex; flex-direction: column; gap: ' . $gap . 'px; }';
        
        $layout_style = !empty($settings['layout_style']) ? $settings['layout_style'] : 'list';
        if ($layout_style === 'grid') {
            $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
            $css_parts[] = '.' . $css_class . ' .pps-post-list.grid{ display: grid; grid-template-columns: repeat(' . $columns . ', 1fr); gap: ' . $gap . 'px; }';
        } else {
            $css_parts[] = '.' . $css_class . ' .pps-post-list.grid{ display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: ' . $gap . 'px; }';
        }
        
        $css_parts[] = '@media (max-width: 768px) { .' . $css_class . ' .pps-post-list.grid{ grid-template-columns: 1fr; } }';
        $css_parts[] = '@media (max-width: 768px) { .' . $css_class . ' .pps-post-list.list .pps-post-item{ flex-direction: column; align-items: stretch; } }';
        $css_parts[] = '@media (max-width: 768px) { .' . $css_class . ' .pps-post-list.list .pps-post-thumbnail img{ width: 100%; height: 200px; } }';

        // Title styles
        $title_styles = [];
        if (!empty($settings['title_color'])) {
            $title_styles[] = 'color: ' . esc_attr($settings['title_color']) . ';';
        }
        if (!empty($settings['title_font_size'])) {
            $title_styles[] = 'font-size: ' . intval($settings['title_font_size']) . 'px;';
        }

        if (!empty($title_styles)) {
            $css_parts[] = '.' . $css_class . ' .pps-post-list-title { ' . implode(' ', $title_styles) . ' }';
        }

        // Post title styles
        $post_title_styles = [];
        if (!empty($settings['post_title_color'])) {
            $post_title_styles[] = 'color: ' . esc_attr($settings['post_title_color']) . ';';
        }
        if (!empty($settings['post_title_font_size'])) {
            $post_title_styles[] = 'font-size: ' . intval($settings['post_title_font_size']) . 'px;';
        }

        if (!empty($post_title_styles)) {
            $css_parts[] = '.' . $css_class . ' .pps-post-title { ' . implode(' ', $post_title_styles) . ' }';
            $css_parts[] = '.' . $css_class . ' .pps-post-title a { ' . implode(' ', $post_title_styles) . ' }';
        }

        // Excerpt styles
        if (!empty($settings['excerpt_color'])) {
            $css_parts[] = '.' . $css_class . ' .pps-post-excerpt { color: ' . esc_attr($settings['excerpt_color']) . '; }';
        }

        // Thumbnail dimensions
        $thumbnail_width = isset($settings['thumbnail_width']) ? intval($settings['thumbnail_width']) : 150;
        $thumbnail_height = isset($settings['thumbnail_height']) ? intval($settings['thumbnail_height']) : 150;
        
        
        $css_parts[] = '.' . $css_class . ' .pps-post-thumbnail img { width: ' . $thumbnail_width . 'px; height: ' . $thumbnail_height . 'px; object-fit: cover; object-position: center; }';
        $css_parts[] = '.' . $css_class . ' .pps-post-list.grid .pps-post-thumbnail img { width: 100%; max-width: ' . $thumbnail_width . 'px; height: ' . $thumbnail_height . 'px; }';
        
        // Responsive thumbnail styles
        $css_parts[] = '@media (max-width: 768px) { .' . $css_class . ' .pps-post-list.grid .pps-post-thumbnail img { width: 100%; max-width: none; height: ' . $thumbnail_height . 'px; } }';
        $css_parts[] = '@media (max-width: 768px) { .' . $css_class . ' .pps-post-list.list .pps-post-thumbnail img { width: 100%; height: ' . $thumbnail_height . 'px; } }';

        // Item styles
        $item_styles = [];
        if (!empty($settings['post_list_background_color'])) {
            $item_styles[] = 'background-color: ' . esc_attr($settings['post_list_background_color']) . ';';
        }
        if (isset($settings['item_padding']) && $settings['item_padding'] !== '') {
            $item_styles[] = 'padding: ' . intval($settings['item_padding']) . 'px;';
        }
        if (isset($settings['item_border_width']) && $settings['item_border_width'] !== '' && $settings['item_border_width'] >= 0) {
            $border_width = intval($settings['item_border_width']) . 'px';
            $border_color = !empty($settings['item_border_color']) ? $settings['item_border_color'] : '#e5e5e5';
            $item_styles[] = 'border: ' . $border_width . ' solid ' . esc_attr($border_color) . ';';
        }

        if (!empty($item_styles)) {
            $css_parts[] = '.' . $css_class . ' .pps-post-item { ' . implode(' ', $item_styles) . ' }';
        }

        // Current post highlighting
        if (!empty($settings['highlight_current_post'])) {
            if (!empty($settings['current_post_bg_color'])) {
                $css_parts[] = '.' . $css_class . ' .pps-post-item.current-post { background-color: ' . esc_attr($settings['current_post_bg_color']) . '; }';
            }
            if (!empty($settings['current_post_border_color'])) {
                $css_parts[] = '.' . $css_class . ' .pps-post-item.current-post { border-color: ' . esc_attr($settings['current_post_border_color']) . '; }';
            }
            if (!empty($settings['current_post_text_color'])) {
                $css_parts[] = '.' . $css_class . ' .pps-post-item.current-post .pps-post-title { color: ' . esc_attr($settings['current_post_text_color']) . '; }';
                $css_parts[] = '.' . $css_class . ' .pps-post-item.current-post .pps-post-title a { color: ' . esc_attr($settings['current_post_text_color']) . '; }';
                $css_parts[] = '.' . $css_class . ' .pps-post-item.current-post .pps-post-meta { color: ' . esc_attr($settings['current_post_text_color']) . '; }';
            }
        }

        // Custom CSS
        if (!empty($settings['custom_css'])) {
            // Replace .pps-post-list-box with specific class
            $custom_css = str_replace('.pps-post-list-box', '.' . $css_class, $settings['custom_css']);
            $css_parts[] = wp_strip_all_tags($custom_css);
        }

        if (!empty($css_parts)) {
            self::$dynamic_css[] = implode("\n", $css_parts);
        }

        return $css_class;
    }

    /**
     * Initialize the renderer
     */
    public static function register()
    {
        static::init();
    }
}

// Initialize the renderer
PostListBoxRenderer::register();
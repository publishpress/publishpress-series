<?php
/**
 * The template for series group taxonomy
 *
 * This template has been set up to work with the WordPress default theme right out of the box.  You can customize this to match the theme you are using by making a copy of this file and place it into your current active theme/child-theme's directory
 * @package PublishPress
 * @subpackage Series_Grouping
 * @since PublishPress Series 2.7.1
 */

if (pp_series_locate_template(['header.php'])) {
    get_header();
} elseif (pp_series_is_block_theme()) {
    pp_series_format_block_theme_header();
}

$group = get_queried_object();
$group_name = (!empty($group) && isset($group->name)) ? $group->name : '';
$group_term_id = (!empty($group) && isset($group->term_id)) ? (int) $group->term_id : 0;
$series_ids = ($group_term_id > 0) ? get_series_in_group($group_term_id) : [];
$series_options = get_option('org_series_options', []);
$show_image = isset($series_options['series_group_plugin_show_image']) ? (int) $series_options['series_group_plugin_show_image'] === 1 : true;
$show_description = isset($series_options['series_group_plugin_show_description']) ? (int) $series_options['series_group_plugin_show_description'] === 1 : true;
$show_series_items = isset($series_options['series_group_plugin_show_series_items']) ? (int) $series_options['series_group_plugin_show_series_items'] === 1 : false;
$columns = isset($series_options['series_group_plugin_columns']) ? (int) $series_options['series_group_plugin_columns'] : 3;
$columns = min(4, max(1, $columns));
?>
<main id="series-group-primary" class="site-content pp-series-group-archive" role="main">
    <div id="series-group-content" class="pp-series-group-archive__content pps-columns-<?php echo esc_attr($columns); ?>">
        <header class="page-header">
            <h1 id="group-title-<?php echo esc_attr($group_term_id); ?>" class="page-title">
                <?php echo esc_html($group_name); ?>
            </h1>
        </header>
        <div id="series-post" class="pp-series-group-archive__entry series-entry-content" data-group-id="<?php echo esc_attr($group_term_id); ?>">
            <?php if (!empty($series_ids)) : ?>
                <div class="pp-series-group-grid" aria-label="<?php esc_attr_e('Series in this category', 'organize-series'); ?>">
                    <?php foreach ((array) $series_ids as $series_id) : ?>
                        <?php
                        $series_id = (int) $series_id;
                        if ($series_id < 1) {
                            continue;
                        }
                        $series_term = get_term($series_id, ppseries_get_series_slug());
                        if (!$series_term || is_wp_error($series_term)) {
                            continue;
                        }
                        $series_link = get_term_link($series_id, ppseries_get_series_slug());
                        if (is_wp_error($series_link)) {
                            continue;
                        }
                        $series_icon = $show_image ? get_series_icon('series=' . $series_id . '&fit_width=640&fit_height=360&display=0&link=0') : '';
                        $series_description = term_description($series_id, ppseries_get_series_slug());
                        ?>
                        <article class="pp-series-group-card">
                            <?php if ($show_image && !empty($series_icon)) : ?>
                                <a class="pp-series-group-card__thumb" href="<?php echo esc_url($series_link); ?>">
                                    <?php echo $series_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </a>
                            <?php endif; ?>
                            <div class="pp-series-group-card__body">
                                <h2 class="pp-series-group-card__title">
                                    <a href="<?php echo esc_url($series_link); ?>">
                                        <?php echo esc_html($series_term->name); ?>
                                    </a>
                                </h2>
                                <?php if ($show_description && !empty(trim(wp_strip_all_tags($series_description)))) : ?>
                                    <div class="pp-series-group-card__description">
                                        <?php echo wp_kses_post(wpautop($series_description)); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($show_series_items) : ?>
                                    <?php
                                    $series_posts = [];
                                    $series_object_ids = get_objects_in_term($series_id, ppseries_get_series_slug());
                                    if (!empty($series_object_ids)) {
                                        if (function_exists('get_series_order')) {
                                            $ordered_series_posts = get_series_order($series_object_ids, 0, $series_id, false, false);
                                            foreach ((array) $ordered_series_posts as $ordered_post) {
                                                $post_id = isset($ordered_post['id']) ? (int) $ordered_post['id'] : 0;
                                                if ($post_id > 0) {
                                                    $series_posts[] = $post_id;
                                                }
                                            }
                                        } else {
                                            $series_posts = array_map('intval', (array) $series_object_ids);
                                        }
                                    }
                                    ?>
                                    <?php if (!empty($series_posts)) : ?>
                                        <ul class="pp-series-group-card__posts">
                                            <?php foreach ($series_posts as $series_post_id) : ?>
                                                <?php
                                                if (get_post_status($series_post_id) !== 'publish') {
                                                    continue;
                                                }
                                                $series_post_link = get_permalink($series_post_id);
                                                $series_post_title = get_the_title($series_post_id);
                                                if (empty($series_post_link) || empty($series_post_title)) {
                                                    continue;
                                                }
                                                ?>
                                                <li>
                                                    <a href="<?php echo esc_url($series_post_link); ?>">
                                                        <?php echo esc_html($series_post_title); ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('Sorry, no series were found in this category.', 'organize-series'); ?></p>
            <?php endif; ?>
			</div>
    </div>
</main>
<?php
if(pp_series_locate_template( array( 'sidebar.php' ) )){
    get_sidebar();
}
if (pp_series_locate_template(['footer.php'])) {
    get_footer();
} elseif (pp_series_is_block_theme()) {
    pp_series_format_block_theme_footer();
}
?>

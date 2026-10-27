<?php
/**
 * The template for displaying Series Table of Contents pages (a listing of all the series you've written
 *
 * This template has been set up to work with the WordPress Twenty Twelve theme right out of the box.  You can customize this to match the theme you are using by following these steps:
 * 1. Make a copy of this file and place it into your current active theme's directory (..themes/[active theme name]/seriestoc.php)
 * 2. Go to the series options page and change the Series Table of Contents Listings template code to match how you want the series listings on the page to be layed out (using the tokens provided).
 * 3. Not all WordPress themes are created equal, so default look and feel might look kind of wierd on your setup.  If that is the case, try looking at your theme's "index.php" and replace anything between and including <?php if (have_posts()); ?> AND <?php endif; ?>  WITH <?php wp_serieslist_display(); ?>
 * 4. Modify the .css in your active theme/orgSeries.css to your liking.
 * 5. That's it!
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */

defined('ABSPATH') || exit;

// Only claim the main landmark when the plugin prints the whole page (block theme
// without header.php). Classic themes usually print their own <main>.
$pps_prints_page = false;
if (pp_series_locate_template(['header.php'])) {
    get_header();
} elseif (pp_series_is_block_theme()) {
    pp_series_format_block_theme_header();
    $pps_prints_page = true;
}

// Hidden page heading, unless the listing template already prints an <h1>.
$pps_toc_options  = get_option('org_series_options');
$pps_toc_box_tpl  = isset($pps_toc_options['series_table_of_contents_box_template']) ? (string) $pps_toc_options['series_table_of_contents_box_template'] : '';
$pps_toc_title    = isset($pps_toc_options['series_toc_title']) ? trim(wp_strip_all_tags((string) $pps_toc_options['series_toc_title'])) : '';
if ('' === $pps_toc_title) {
    $pps_toc_title = __('Series Table of Contents', 'organize-series');
}
?>

        <section id="series-primary" class="site-content">
            <div id="series-content"<?php echo $pps_prints_page ? ' role="main"' : ''; ?>>

            <?php if (false === stripos($pps_toc_box_tpl, '<h1')) : ?>
                <h1 class="screen-reader-text" style="<?php echo esc_attr(ppseries_sr_only_style()); ?>"><?php echo esc_html($pps_toc_title); ?></h1>
            <?php endif; ?>

            <?php wp_serieslist_display(); ?>

            <div class="stocpagination"> <?php series_toc_paginate(); ?> </div>
            </div><!-- #content -->
        </section><!-- #primary -->

        <?php
        if (pp_series_locate_template(array( 'sidebar.php' ))) {
            get_sidebar();
        }
        if (pp_series_locate_template(['footer.php'])) {
            get_footer();
        } elseif (pp_series_is_block_theme()) {
            pp_series_format_block_theme_footer();
        }
        ?>

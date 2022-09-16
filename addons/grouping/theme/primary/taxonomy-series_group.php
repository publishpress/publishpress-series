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
?>
<section id="series-primary" class="site-content">
    <div id="series-content" role="main">
        <?php $group = get_queried_object(); ?>
        <header class="page-header">
            <h1 id="group-title-<?php echo $group->term_id; ?>" class="page-title">
                <?php echo $group->name ?>
            </h1>
        </header>
        <div id="series-post" <?php post_class(); ?>>
            <div class="series-entry-content" id="group_<?php echo $group->term_id; ?>">
                <?php
				$get_series = get_series_in_group($group->term_id); //gets the series data for the displayed group.
				$series_args = array(
					'include' => $get_series,
                    'number' => '',
					'order' => 'ASC',
                    'hide_empty' => true
				);
				wp_serieslist_display(false, $series_args); //this is an organize-series template tag. List of series is output according to the serieslist_display template in series options.
				?>
			</div>
	    </div>
    </div>
</section>
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
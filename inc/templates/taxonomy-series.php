<?php
/**
 * The template for series taxonomy
 *
 * You can customize this template by havinga  copy of this file in root child theme's folder
 *
 * @package PublishPress
 */

if (pp_series_locate_template(['header.php'])) {
    get_header(); 
} elseif (pp_series_is_block_theme()) {
    pp_series_format_block_theme_header();
}
$description = get_the_archive_description();

// CSS wrapper class for the design
$series_options 	  = get_option('org_series_options');
$series_layout_class  = '';
$series_layout_class .= isset($series_options['series_overview_page_layout']) ? 'pps-layout-' . $series_options['series_overview_page_layout'] : 'pps-layout-default';
$series_layout_class .= isset($series_options['series_overview_page_columns']) ? ' pps-columns-' . $series_options['series_overview_page_columns'] : '';
?>
<section id="series-primary" class="site-content">
    <div id="series-content" role="main">

		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php if ( $description ) : ?>
				<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			<?php endif; ?>
		</header><!-- .page-header -->

		<?php if ( have_posts() ) : ?>

			<div id="pps-taxonomy-series-overview">
				<div class="<?php echo esc_attr($series_layout_class); ?>">
					<?php while ( have_posts() ) : ?>
						<?php the_post(); ?>
							<article class="pps-taxonomy-series__post<?php echo !has_post_thumbnail() ? ' pps-taxonomy-series__no-image' : '' ?>">

								<?php if ( has_post_thumbnail() ) { ?>
									<div class="pps-taxonomy-series__thumbnail">
										<?php the_post_thumbnail(); ?>
									</div><!-- .pps-taxonomy-series__thumbnail -->
								<?php } ?>

								<div class="pps-taxonomy-series__wrapper">
									<?php
									the_title(
										sprintf( '<h2 class="pps-taxonomy-series__title"><a href="%s">', esc_url( get_permalink() ) ),
										'</a></h2>'
									);
									?>
									<div class="pps-taxonomy-series__content">
										<?php the_excerpt(); ?>
									</div>
								</div><!-- .pps-taxonomy-series__content -->

							</article><!-- .pps-taxonomy-series__post -->
					<?php endwhile; ?>
				</div>

				<div class="pps-taxonomy-series__pagination">
					<?php the_posts_pagination(); ?>
				</div><!-- .pps-taxonomy-series__pagination -->

			</div><!-- #pps-taxonomy-series-overview -->

		<?php else : ?>
			<p><?php esc_html_e( 'Sorry, no results found.', 'organize-series' ); ?></p>
		<?php endif; ?>

	</div><!-- #content -->
</section><!-- #primary -->

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

<?php
/**
 * The template for series taxonomy
 *
 * You can customize this template by havinga  copy of this file in root child theme's folder
 *
 * @package PublishPress
 */

get_header();

$description = get_the_archive_description();
?>
<section id="primary" class="site-content">
    <div id="content" role="main">

		<header class="page-header">
			<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
			<?php if ( $description ) : ?>
				<div class="archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			<?php endif; ?>
		</header><!-- .page-header -->

		<?php if ( have_posts() ) : ?>

			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

						<header class="entry-header">
							<?php
							the_title( sprintf( '<h2 class="entry-title"><a href="%s">', esc_url( get_permalink() ) ), '</a></h2>' );
							?>
						</header><!-- .entry-header -->

						<div class="entry-content">
							<div class="post-thumbnail">
								<?php the_post_thumbnail(); ?>
							</div>
							<?php the_excerpt(); ?>
						</div><!-- .entry-content -->

					</article><!-- #post-${ID} -->
			<?php endwhile; ?>

		<?php else : ?>
			<p><?php esc_html_e( 'Sorry, no results found.', 'organize-series' ); ?></p>
		<?php endif; ?>

	</div><!-- #content -->
</section><!-- #primary -->

<?php
if( is_active_sidebar('sidebar') ) {
	get_sidebar();
}

get_footer();
?>

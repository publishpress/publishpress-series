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

get_header(); ?>

		<section id="primary" class="site-content">
			<div id="content" role="main">
			
			<?php wp_serieslist_display(); ?>

			<div class="stocpagination"> <?php series_toc_paginate(); ?> </div>
			</div><!-- #content -->
		</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
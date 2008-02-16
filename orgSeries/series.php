<?php
######################################
/* EXAMPLE SERIES-TEMPLATE FOR SERIES DISPLAY PAGES
This is an example template file that WordPress would load when a reader clicks the link for a series.  By default the orgSeries plugin will load the default archives template (either archive.php or index.php - depending on what theme is activated) when a series link is clicked.  If you want to use this file instead:
1. Modify it so that it matches the layout of your current theme (take a look at your category.php or archive.php or index.php file in your theme template folder and mirror it in this file - when finished the only difference between the two files will be that instead of the category template tags you'll use the series template tags).
2. Copy the new series.php file into your theme directory on the server your WordPress is hosted.
3. That's it!  */
########################################

get_header(); ?>

	<div id="content" class="narrowcolumn">
<?php is_tag(); ?>
		<?php if (have_posts()) : ?>
 	    <?php /* If this is a series archive*/  if (is_series()) { ?>
		<h2 class="pagetitle">Archive for the &#8216;<?php single_series_title(); ?>&#8217; Series</h2>
		<p><?php echo series_description(); ?></p>
 	   	  <?php } ?>


		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

		<?php while (have_posts()) : the_post(); ?>
		<div class="post">
				<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
				<small><?php the_time('l, F jS, Y') ?></small>

				<div class="entry">
					<?php the_content() ?>
				</div>

				<p class="postmetadata"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>

			</div>

		<?php endwhile; ?>

		<div class="navigation">
			<div class="alignleft"><?php next_posts_link('&laquo; Older Entries') ?></div>
			<div class="alignright"><?php previous_posts_link('Newer Entries &raquo;') ?></div>
		</div>

	<?php else : ?>

		<h2 class="center">Not Found</h2>
		<?php include (TEMPLATEPATH . '/searchform.php'); ?>

	<?php endif; ?>

	</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>

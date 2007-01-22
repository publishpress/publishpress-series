<?php
###########################################
##    CUSTOM CATEGORY-TEMPLATE EXAMPLE   ##
## Rename the file from category-xx.php  ##
## and replace the "xx" with the category##
## id of your main series category (in   ##
## which all your various series         ##
## (sub-categories) are contained.       ##
## Then place this file in the theme     ##
## folder for the template you are       ##
## currently using.  In most cases no    ##
## changes will be needed, but if it     ##
## doesn't "look right" you may have to  ##
## compare this code to the code for the ##
## wp-loop (within "<?php				 ##
## "if(have_posts))" tag) and make any   ##
## necessary changes.  					 ##
## The "<?php wp_serieslist_display(); "##
## tag MUST be included within the loop  ##
## for it to work properly.              ##
###########################################

get_header(); ?>

<?php if (have_posts()) : ?>
	<div class="page-title">
			<h2 class="page-title-border"><?php _e('Archive for'); ?> <?php echo single_cat_title(); ?></h2>
	</div>
	<div class="post">
		<?php echo category_description(); ?>
	</div>
	<?php while (have_posts()) : the_post(); ?>
	<div class="post" >
		<?php wp_serieslist_display(); ?>
	</div>
<?php endwhile; endif; ?>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
<?php
###########################################
/*    CUSTOM CATEGORY-TEMPLATE EXAMPLE   
Rename the file from category-xx.php and replace the "xx" with the category id of your main series category (in  which all your various series (sub-categories) are contained.  Then place this file in the theme folder for the template you are currently using.  In most cases no changes will be needed, but if it  doesn't "look right" you may have to compare this code to the code to your template file for categories			(usually in category.php or index.php or archives.php  The "<?php wp_serieslist_display(); ?>" tag MUST NOT be included within the loop for it to work properly.  */     
###########################################

get_header(); ?>

<div class="page-title">
			<h2 class="page-title-border"><?php _e('Archive for'); ?> <?php echo single_cat_title(); ?></h2>
	</div>
	<div class="post">
		<?php echo category_description(); ?>
	</div>
	<div class="post" >
		<?php wp_serieslist_display(); ?>
	</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
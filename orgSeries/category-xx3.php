<?php 
	###########################################
/*    CUSTOM CATEGORY-TEMPLATE EXAMPLE FOR THREE COLUMN THEME
Rename the file from category-xx3.php and replace the "xx3" with the category id of your main series category (in  which all your various series (sub-categories) are contained.  Then place this file in the theme folder for the template you are currently using.  In most cases no changes will be needed, but if it  doesn't "look right" you may have to compare this code to the code to your template file for categories			(usually in category.php or index.php or archives.php  The "<?php wp_serieslist_display(); ?>" tag MUST NOT be included within the loop for it to work properly.  */     
###########################################

get_header(); 
	include (TEMPLATEPATH . '/sidebar.php');
	if (get_option('mandigo_1024') && get_option('mandigo_3columns')) {
		include (TEMPLATEPATH . '/sidebar2.php');
	}
?>

	<div id="content" class="narrowcolumn">

		<h2 class="pagetitle"><?php printf(__('Archive for the %s Category','mandigo'),single_cat_title('',false)); ?></h2>

 	  	<div class="post">
 	  		<?php echo category_description(); ?>
 	  	</div>
 	  	<div class="post">
 	  		<?php wp_serieslist_display(); ?>
 	  	</div>
                                
	</div>

<?php get_footer(); ?>
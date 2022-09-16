<?php
/**
 * Template Name: PublishPress Series Group Template
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 * 
 * This is an example template demonstrating hierarchy in series groups.  Note that this particular template is only demonstrating one level.
 */

if (pp_series_locate_template(['header.php'])) {
    get_header(); 
} elseif (pp_series_is_block_theme()) {
    pp_series_format_block_theme_header();
}

global $wp_query;
$save_query = $wp_query;
$content = ''; ?>

		<div id="container">
			<div id="content" role="main">

				<div id="post" <?php post_class(); ?>>
				<?php
					//set up groups data for display
					
					//series groups args
					$args = array( 
						'order' => 'DESC',
						'orderby' => 'term_id',
						'hide_empty' => false,
						'hierarchical' => true
						); 
						
					//series listing args	
					$get_series_args = array(
						'hide_empty' => 1,
					);
			
				$groups = get_series_groups( $args ); //get series groups
				
				foreach ( $groups as $group ) {
			
				//does the group have children?  If so, then let's get cracking...
				
				$c_groups = _get_term_children($group->term_id, $groups, 'series_group');
			
				if ( $c_groups ) {
				?>
					<div class="entry-content" id="group_<?php echo $group->term_id; ?>">
						<h3 id="group-title-<?php echo $group->term_id; ?>" class="group-title"><?php echo $group->name ?></h3>

					<?php foreach ( $c_groups as $c_group ) { ?>
			
						<div class="subgroup" id="subgroup_<?php echo $c_group->term_id ?>">
							<h4 id="subgroup-title-<?php echo $c_group->term_id; ?>" class="subgroup-title"><?php echo $c_group->name; ?></h4>
						
						<?php 
						$get_series = get_series_in_group($c_group->term_id, $get_series_args); //gets the series data for the displayed group.
						
						$series_args = array(
							'include' => $get_series,
							'order' => 'DESC'
						);
						wp_serieslist_display(false, $series_args); //this is an organize-series template tag. List of series is output according to the serieslist_display template in series options.
						?>
						</div> <!-- end .subgroup -->
					<?php } ?>
						
					</div><!-- .entry-content -->
				<?php }
				}
				
				?>
				</div><!-- #post-## -->

			</div><!-- #content -->
		</div><!-- #container -->

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
<?php

/**
 * This file contains the code to display the organize-series plugin feed from unfoldingneurons.com
 *
 * @package WordPress
 * @since 2.3
 */
global $org_domain;
require_once(ABSPATH .'/wp-load.php');
require_once( ABSPATH . 'wp-admin/admin.php' );
require_once( ABSPATH . WPINC . '/feed.php' );

$cache_time = 1800;
$size = 5;
$date = false;
//let's set the cache time for simple pie
add_filter('wp_feed_cache_transient_lifetime', create_function( '$a', "return $cache_time;" ) );
$rss = fetch_feed('http://www.organizeseries.com/category/blog/feed/');
?>
		<p><?php _e('The following will keep you updated with all the recent Organize Series Plugin related news by <a href="http://unfoldingneurons.com" title="Visit Darren Ethier\'s blog">Darren Ethier</a>.', $org_domain); ?></p>
<?php
if ( !is_wp_error( $rss ) ) {
		$maxitems = $rss->get_item_quantity($size);
		$rss_items = $rss->get_items(0, $maxitems);
		
		//store total number of items in the feed
		$i = 0;
		$total_entries = count($rss_items);
		
		//output html
		?>
	
	<?php
	foreach ($rss_items as $item ) {
		$i++;
		//set item data we need;
		$title = $item->get_title();
		$link = $item->get_permalink();
		$desc = $item->get_description();
		$date_posted = $item->get_date();
	?>
	<h4><a href='<?php echo $link; ?>'><?php echo $title; ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($date_posted, time() ) ) ); ?></h4>
	<?php
		}
	?>
	<p class="readmore"><a href="http://www.organizeseries.com/category/blog"><?php _e('Read more', $org_domain); ?> &raquo;</a></p>
	<?php
} else {
	?>
	<h4>An error occurred with getting the feed from <a href="http://organizeseries.com">OrganizeSeries.com</a>.  There may be temporary maintenance occuring.  Feel free to follow our <a href="http://twitter.com/organizeseries">Twitter Stream</a> to keep up with what's happening.</h4>
	<?php
}
?>
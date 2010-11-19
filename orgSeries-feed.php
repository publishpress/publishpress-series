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
require_once( ABSPATH . WPINC . '/rss.php' );

$rss = @fetch_rss('http://www.organizeseries.com/category/blog/feed/');
if ( isset($rss->items) && 0 != count($rss->items) ) {
	?>
<h5><?php _e('Organize Series News', $org_domain); ?></h5>
	<p><?php _e('The following will keep you updated with all the recent Organize Series Plugin related news by <a href="http://unfoldingneurons.com" title="Visit Darren Ethier\'s blog">Darren Ethier</a>.', $org_domain); ?></p>
<?php
$rss->items = array_slice($rss->items, 0, 5);
foreach ($rss->items as $item ) {
?>
<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo esc_html($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
<?php
	}
?>
<p class="readmore"><a href="http://www.unfoldingneurons.com/category/blog"><?php _e('Read more', $org_domain); ?> &raquo;</a></p>
<?php
}
?>
<?php

/**
 * This file contains the code to display the organize-series plugin feed from unfoldingneurons.com
 *
 * @package WordPress
 * @since 2.3
 */

$root = dirname(dirname(dirname(dirname(__FILE__))));
if (file_exists($root.'/wp-load.php')) {
	// WP 2.6
	require_once($root.'/wp-load.php');
} else {
	// Before 2.6
	require_once($root.'/wp-config.php');
}

require_once( ABSPATH . 'wp-admin/admin.php' );
require_once( ABSPATH . WPINC . '/rss.php' );

$rss = @fetch_rss('http://www.unfoldingneurons.com/category/released-code/organize-series/feed/'); 
if ( isset($rss->items) && 0 != count($rss->items) ) {
	?>
<fieldset id="orgSeriesupdatefeed" class="dbx-box">
	<h3 class="dbx-handle"><?php _e('Organize Series News'); ?></h3>
	<div class="dbx-content">
<p>The following will keep you updated with all the recent Organize Series Plugin related news by <a href="http://unfoldingneurons.com" title="Visit Darren Ethier's blog">Darren Ethier</a>.</p>
<?php
$rss->items = array_slice($rss->items, 0, 5);
foreach ($rss->items as $item ) {
?>
<h4><a href='<?php echo wp_filter_kses($item['link']); ?>'><?php echo wp_specialchars($item['title']); ?></a> &#8212; <?php printf(__('%s ago'), human_time_diff(strtotime($item['pubdate'], time() ) ) ); ?></h4>
<?php
	}
?>
<p class="readmore"><a href="http://www.unfoldingneurons.com/category/released-code/organize-series"><?php _e('Read more'); ?> &raquo;</a></p>
</div>
</fieldset>
<?php
}
?>
<?php

function org_series_init($reset = false) {
	global $org_domain;
	$oldversion = get_option('org_series_oldversion');
	
	if (!$reset)  { 
		$settings = get_option('org_series_options');
	}
	
	if (!($is_initialized=get_option('org_series_is_initialized')) || empty ($settings) || $reset || '1.6' == $oldversion) {
		$url = parse_url(get_bloginfo('siteurl'));
		$init_settings = array( //options for the orgSeries plugin
		//main settings
			'custom_css' => 1, 
			'kill_on_delete' => 0, //determines if all series information (including series-icon tables) will be deleted when the plugin is deleted using the delete link on the plugins page.
			'auto_tag_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series.
			'auto_tag_nav_toggle' => 1, //sets the auto-tag insertions for the series navigation strip.
			'auto_tag_seriesmeta_toggle' => 1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
			'series_toc_url' => $url['path'] . '/series/',
			'series_custom_base' => 'series',
			'series_toc_title' => __('Series Table of Contents',$org_domain),
		//new template options
			'series_post_list_template' => '<div class="seriesbox"><div class="center">%series_icon_linked%<br />%series_title_linked%</div><ul class="serieslist-ul">%post_title_list%</ul></div>%postcontent%',
			'series_post_list_post_template' => '<li class="serieslist-li">%post_title_linked%</li>',
			'series_post_list_currentpost_template' => '<li class="serieslist-li-current">%post_title%</li>',
			'series_meta_template' => '<div class="seriesmeta">' . _c('This entry is part %series_part% of %total_posts_in_series% in the series |leave the %tokens% as is when translating',$org_domain) . '%series_title_linked%</div>%postcontent%',
			'series_meta_excerpt_template' => '<div class="seriesmeta">' ._c('This entry is part %series_part% of %total_posts_in_series% in the series |leave the %tokens% as is when translating',$org_domain) . '%series_title_linked%</div>%postcontent%',
			'series_table_of_contents_box_template' => '<div class="serieslist-box"><div class="imgset">%series_icon_linked%</div><div class="serieslist-content"><h2>%series_title_linked%</h2><p>%series_description%</p></div><hr style="clear: left; border: none" /></div>',
			'latest_series_before_template' => '<div class="latest-series"><ul>',
			'latest_series_inner_template' => '<li>%series_title_linked%</li>',
			'latest_series_after_template' => '</ul></div>',
			'series_post_nav_template' => '%postcontent%<fieldset><legend>'. __('Series Navigation',$org_domain) .'</legend><span class="series-nav-left">%previous_post%</span><span class="series-nav-right">%next_post%</span></fieldset>',
			'series_nextpost_nav_custom_text' => $series_nextpost_nav_custom_text,
			'series_prevpost_nav_custom_text' => $series_prevpost_nav_custom_text,
		//series_icon related settings
		'series_icon_width_series_page' => 200,
		'series_icon_width_post_page' =>100,
		'series_icon_width_latest_series' =>100,
		//series posts order options
		'series_posts_orderby' => 'meta_value',
		'series_posts_order' => 'ASC');
		
			
		if (!empty ($settings)) {
			$newSettings = array_merge($init_settings, $settings);
		} else {
			$newSettings = $init_settings;
		}
		
		$newSettings['last_modified'] = gmdate("D, d M Y H:i:s", time());
		
		update_option('org_series_is_initialized', 1, __('Organize Series Plugin has been initialized',$org_domain));
		update_option('org_series_options', $newSettings, __('Array of options for the Organize Series plugin',$org_domain));
		
		if ($is_initialized=get_option('org_series_is_initialized') ) { ?>
			<div class="updated"><p><strong><?php _e('Organize Series Plugin has been initialized.',$org_domain); ?></strong></p></div>
			<?php
			return;
		}
		return;
		}
	return;
}

function org_series_option_update() {
	global $wpdb, $org_domain;
	check_admin_referer('update_series_options');
	$url = parse_url(get_bloginfo('siteurl'));
	//toggles and paging info
	$settings['auto_tag_toggle'] = isset($_POST['auto_tag_toggle']) ? 1 : 0;
	$settings['auto_tag_nav_toggle'] = isset($_POST['auto_tag_nav_toggle']) ? 1 : 0;
	$settings['auto_tag_seriesmeta_toggle'] = isset($_POST['auto_tag_seriesmeta_toggle']) ? 1 : 0;
	$settings['custom_css'] = isset($_POST['custom_css']) ? 1 : 0;
	$settings['kill_on_delete'] = isset($_POST['kill_on_delete']) ? 1 : 0;
	if ( isset($_POST['series_toc_url']) ) $settings['series_toc_url'] = $url['path'] . '/' . $_POST['series_toc_url'];
	if ( isset($_POST['series_custom_base']) ) $settings['series_custom_base'] = preg_replace('#/+#', '/', '/' . $_POST['series_custom_base']);
	if (!ereg('.*/$', $settings['series_toc_url'])) $settings['series_toc_url'] .= '/';
	if (strlen($_POST['series_toc_url']) <=0) $settings['series_toc_url'] = FALSE;
	if ( isset($_POST['series_toc_title']) ) $settings['series_toc_title'] = trim(stripslashes($_POST['series_toc_title']));
		
	//template options
	if ( isset($_POST['series_post_list_template']) ) $settings['series_post_list_template'] = trim(stripslashes($_POST['series_post_list_template']));
	if ( isset($_POST['series_post_list_post_template']) ) $settings['series_post_list_post_template'] = trim(stripslashes($_POST['series_post_list_post_template']));
	if ( isset($_POST['series_post_list_currentpost_template']) ) $settings['series_post_list_currentpost_template'] = trim(stripslashes($_POST['series_post_list_currentpost_template']));
	if ( isset($_POST['series_meta_template']) ) $settings['series_meta_template'] = trim(stripslashes($_POST['series_meta_template']));
	if ( isset($_POST['series_meta_excerpt_template']) ) $settings['series_meta_excerpt_template'] = trim(stripslashes($_POST['series_meta_excerpt_template']));
	if ( isset($_POST['series_table_of_contents_box_template']) ) $settings['series_table_of_contents_box_template'] = trim(stripslashes($_POST['series_table_of_contents_box_template']));
	if ( isset($_POST['series_post_nav_template']) ) $settings['series_post_nav_template'] = trim(stripslashes($_POST['series_post_nav_template']));
	if ( isset($_POST['series_nextpost_nav_custom_text']) ) $settings['series_nextpost_nav_custom_text'] = trim(stripslashes($_POST['series_nextpost_nav_custom_text']));
	if ( isset($_POST['series_prevpost_nav_custom_text']) ) $settings['series_prevpost_nav_custom_text'] = trim(stripslashes($_POST['series_prevpost_nav_custom_text']) );
	if ( isset($_POST['series_posts_orderby']) ) $settings['series_posts_orderby'] = trim(stripslashes($_POST['series_posts_orderby']) );
	if ( isset($_POST['series_posts_order']) ) $settings['series_posts_order'] = trim(stripslashes($_POST['series_posts_order']) );
	if ( isset($_POST['latest_series_before_template']) ) $settings['latest_series_before_template'] = trim(stripslashes($_POST['latest_series_before_template']));
	if ( isset($_POST['latest_series_inner_template']) ) $settings['latest_series_inner_template'] = trim(stripslashes($_POST['latest_series_inner_template']));
	if ( isset($_POST['latest_series_after_template']) ) $settings['latest_series_after_template'] = trim(stripslashes($_POST['latest_series_after_template']));
	
	//series-icon related settings
	if ( isset($_POST['series_icon_width_series_page']) ) $settings['series_icon_width_series_page'] = $_POST['series_icon_width_series_page'];
	if ( isset($_POST['series_icon_width_post_page']) ) $settings['series_icon_width_post_page'] = $_POST['series_icon_width_post_page'];
	if ( isset($_POST['series_icon_width_latest_series']) ) $settings['series_icon_width_latest_series'] = $_POST['series_icon_width_latest_series'];
		
	$settings['last_modified'] = gmdate("D, d M Y H:i:s", time());
	update_option('org_series_options', $settings);
}

function org_series_admin_page() {
	global $wp_version, $wpdb, $org_domain;
	?>
	<div class="wrap">
		<h2><?php _e('Organize Series Plugin Options',$org_domain); ?></h2>
	<?php
	
	if (isset($_POST['submit_option'])) {
		if (isset($_POST['reset_option'])) {
			org_series_init(true);
		} else {
			org_series_option_update(); ?>
			<div class="updated"><p><?php _e('Organize Series Plugin Options have been updated',$org_domain); ?></p></div>
			<?php
		}
	}
	
	org_series_init();
	$oldversion = get_option('org_series_oldversion');
	$settings = get_option('org_series_options');

	?>
	 <div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable">
	 <script type="text/javascript">
		var WPHC_AFF_ID = '14322';
		var WPHC_WP_VERSION = '<?php global $wp_version; echo $wp_version; ?>';
		</script>
		<script type="text/javascript"
			src="http://cloud.wphelpcenter.com/wp-admin/0002/deliver.js">
		</script>
	<div id="plugin-info-div" class="postbox">
		<h3 class="hndle"><span><?php _e('Plugin Info', $org_domain) ?></span></h3>
		<div class="inside">
		<p><?php _e('Plugin information can be found <a href="http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin" title="The Organize Series Plugin page at unfoldingneurons.com">here</a>',$org_domain); ?></p>
			<p><?php _e('<a href="http://unfoldingneurons.com/series/organize-series-usage-tips" title="usage tips">For usage help check out this series!</a>',$org_domain); ?></p>
			<p><?php _e('<a href="http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin" title="Plugin Support">Plugin Support Forums</a>',$org_domain); ?></p>
		<p><?php _e('If you\'d like to donate to <a href="http://www.unfoldingneurons.com" title="Darren Ethier\'s (author) Blog">me</a> as an expression of thanks for the release of this plugin feel free to do so - and thanks!', $org_domain); ?></p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick" />
			<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
			<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
" />
		</form>
		</div>
	</div>
	<div id="token-legend" class="postbox">
	<h3 class="handle"><span><?php _e('Token legend', $org_domain); ?></span></h3>
		<div class="inside">
		<p><small><?php _e('The following is a legend of the tokens that are available for use in the custom template fields. These will be replaced with the appropriate values when the plugin runs.', $org_domain); ?></small></p>
		<strong>%series_icon%</strong><br />
			<em><?php _e('This will be replaced with the series icon for a series.', $org_domain); ?></em><br /><br />
		<strong>%series_icon_linked%</strong><br />
			<em><?php _e('Same as %series_icon% except that the series icon will be linked to the series page',$org_domain); ?></em><br /><br />
		<strong>%series_list%</strong><br />
			<em><?php _e('This token is for use with the orgSeries widget only - it references where you want the list of series titles to be inserted and requires that the template for each series title be also set.', $org_domain); ?></em><br /><br />
		<strong>%series_title%</strong><br />
			<em><?php _e('This will be replaced with the title of a series', $org_domain); ?></em><br /><br />
		<strong>%series_title_linked%</strong><br />
			<em><?php _e('Same as %series_title% except that it will also be linked to the series page', $org_domain); ?></em><br /><br />
		<strong>%post_title_list%</strong><br />
			<em><?php _e('Is the location token for where the contents of the post list post templates will appear.', $org_domain); ?></em><br /><br />
		<strong>%post_title%</strong><br />
			<em><?php _e('Will be replaced with the post title of a post in the series', $org_domain); ?></em><br /><br />
		<strong>%post_title_linked%</strong><br />
			<em><?php _e('Will be replaced with the post title of a post in the series linked to the page view of that post.', $org_domain); ?></em><br /><br />
		<strong>%previous_post%</strong><br />
			<em><?php _e('Will be replaced by the navigation link for the previous post in a series. The text will be the title of the post.', $org_domain); ?></em><br /><br />
		<strong>%previous_post_custom%</strong><br />
			<em><?php _e('Same as %previous_post% except the text will be what you specify in the "Custom Previous Post Navigation Text" field.', $org_domain); ?></em><br /><br />
		<strong>%next_post%</strong><br />
			<em><?php _e('Will be replaced by the navigation link for the next post in a series. The text will be the title of the post.', $org_domain); ?></em><br /><br />
		<strong>%next_post_custom%</strong><br />
			<em><?php _e('Same as %next_post% except the text will be what you specify in the "Custom Next Post Navigation Text" field.', $org_domain); ?></em><br /><br />
		<strong>%postcontent%</strong><br />
			<em><?php _e('Use this tag either before or after the rest of the template code.  It will indicate where you want the content of a post to display.', $org_domain); ?></em><br /><br />
		<strong>%series_part%</strong><br />
			<em><?php _e('Will display what part of a series the post is', $org_domain); ?></em><br /><br />
		<strong>%total_posts_in_series%</strong><br />
			<em><?php _e('Will display the total number of posts in a series', $org_domain); ?></em><br /><br />
		<strong>%series_description%</strong><br />
			<em><?php _e('Will display the description for the series', $org_domain); ?></em>
		</div>
	</div>
	<?php if (file_exists(ABSPATH . WPINC . '/rss.php')) { ?>	
	<div id="organize-series-feed" class="postbox">	
		<div class="inside">
		<div id="orgseriesnews">
			<?php include(WP_CONTENT_DIR.'/plugins/' . SERIES_DIR .'/organize-series-feed.php'); ?>
		</div> <?php /*rss feed related */ ?>
		</div>
	</div>
		<?php } ?>
	</div>
</div>

<div id="post-body" class="has-sidebar">	
	<div id="post-body-content" class="has-sidebar-content">
	<form action="" method="post">
	<input type="hidden" name="submit_option" value="1" />
	<table class="widefat seriesmanage">
	<tbody id="the-list">
	<?php wp_nonce_field('update_series_options'); ?>	
<?php	
	org_series_echo_fieldset_mainsettings($settings);
	org_series_echo_series_templates($settings);
	org_series_echo_series_icon($settings);
?>
	</tbody>
	</table>
	<br />
		<?php $submit_text = __('Do you really want to reset to default options (all your custom changes will be lost)?', $org_domain); 
			$script_text = "javascript:return confirm('".$submit_text."')"?>
		<span class="submit">
			<input type="submit" name="update_orgseries" value="<?php _e('Update Options', $org_domain); ?>" />
			<input type="submit" onclick="<?php echo $script_text; ?>;" name="reset_option" value="<?php _e('Reset options to default', $org_domain); ?>" />
		</span>
	</form>
</div>
</div>
<br class="clear" />
</div>
</div>

<?php
}
				
function org_series_echo_fieldset_mainsettings($settings) {
	global $org_domain;
	$url = parse_url(get_bloginfo('siteurl'));
	$url = $url['path'] . '/';
?>
	<tr>
		<th scope="row" valign="top" class="seriesdescr" class="seriesdescr"><?php _e('Automation Settings', $org_domain); ?><br />
			<small><?php _e('Choose from the following options for turning on or off automatic insertion of template tags for Organize Series into your blog.  If you wish to have more control over the location of the template tags (you power user you) then deselect as needed.', $org_domain); ?></small>
		</th>
			<td>
				<label for="auto_tag_toggle">
					<input name="auto_tag_toggle" id="auto_tag_toggle" type="checkbox" value="<?php echo $settings['auto_tag_toggle']; ?>" <?php checked('1', $settings['auto_tag_toggle']); ?> /> <?php _e('Display list of series on post pages?', $org_domain); ?>
				</label>
				<br />
				<small><em><?php _e('Selecting this will indicate that you would like the plugin to automatically insert the code into your theme for the listing of posts in a series when a post is displayed that is part of a series.  [default=selected]', $org_domain); ?></em></small>
				<br />
				<label for="auto_tag_nav_toggle">
					<input name="auto_tag_nav_toggle" id="auto_tag_nav_toggle" type="checkbox" value="<?php echo $settings['auto_tag_nav_toggle']; ?>" <?php checked('1', $settings['auto_tag_nav_toggle']); ?> /> <?php _e('Display series navigation links?', $org_domain); ?>
				</label>
				<br />
				<small><em><?php _e('Selecting this will indicate that you would like the plugin to automatically insert the code into your theme for the displaying the series navigation links.  [default=selected]', $org_domain); ?></em></small>
				<br />
				<label for="auto_tag_seriesmeta_toggle">
					<input name="auto_tag_seriesmeta_toggle" id="auto_tag_seriesmeta_toggle" type="checkbox" value="<?php echo $setttings['auto_tag_seriesmeta_toggle']; ?>" <?php checked('1', $settings['auto_tag_seriesmeta_toggle']); ?> /> <?php _e('Display series meta information with posts?', $org_domain); ?>
				</label>
				<br/>
				<small><em><?php _e('Series meta will include whatever is listed in the Template tag options for the series meta tag (see settings on this page). [default = selected]', $org_domain); ?></em></small>
				<br />
				<label for="custom_css">
					<input name="custom_css" id="custom_css" type="checkbox" value="<?php echo $setttings['custom_css']; ?>" <?php checked('1', $settings['custom_css']); ?> /> <?php _e('Use custom .css?', $org_domain); ?>
				</label>
				<br />
				<small><em><?php _e('Leaving this box checked will make the plugin use the included .css file.  If you uncheck it you will need to add styling for the plugin in your themes "style.css" file. [default = checked]', $org_domain); ?></em></small>
				<br />
				<br />
					<strong><?php _e('Series Table of Contents URL:', $org_domain); ?></strong><br />
					<?php bloginfo('home') ?>/<input type="text" name="series_toc_url" value="<?php echo substr($settings['series_toc_url'], strlen($url)) ?>" /><br />
				<small><em><?php _e('Enter the path where you want the Series Table of Contents to be shown', $org_domain); ?></em></small><br /><br />
					<strong><?php _e('Series Custom Base:', $org_domain); ?></strong><br />
					<input type="text" name="series_custom_base" value="<?php echo htmlspecialchars($settings['series_custom_base']); ?>" /><br/>
					<small><em><?php _e('Set what you want to use as the base for referring to your series structure in permalinks series archive pages', $org_domain); ?></em></small><br /><br />
					<strong><?php _e('Series Table of Contents Title:', $org_domain); ?></strong><input type="text" name="series_toc_title" value="<?php echo htmlspecialchars($settings['series_toc_title']); ?>" /><br />
				<small><em><?php _e('Enter what you want to appear in the browser title when readers are viewing the series table of contents page.', $org_domain); ?></em></small><br /> <br />
				<label for="series_posts_orderby_part">
					<input name="series_posts_orderby" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', $settings['series_posts_orderby']); ?> /><?php _e('order by series part', $org_domain); ?>
				</label>
				<label for="series_posts_orderby_date">
					<input name="series_posts_orderby" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', $settings['series_posts_orderby']); ?> /><?php _e('Order by date', $org_domain); ?>
				</label>
				<br />
				<label for="series_posts_order_ASC">
					<input name="series_posts_order" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', $settings['series_posts_order']); ?> /><?php _e('Ascending', $org_domain); ?>
				</label>
				<label for="series_posts_order_DESC">
					<input name="series_posts_order" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', $settings['series_posts_order']); ?> /><?php _e('Descending', $org_domain); ?>
				</label>
				<br />
				<small><em><?php _e('You can choose what order you want the posts on a series archive page to be displayed.  Default is by date, descending.', $org_domain); ?></em></small>
				<br />
				<br />
				<span style="background-color:#ff3366; padding: 5px; padding-bottom: 8px;">
				<label for="kill_on_delete">
					<input name="kill_on_delete" id="kill_on_delete" type="checkbox" value="<?php echo $setttings['kill_on_delete']; ?>" <?php checked('1', $settings['kill_on_delete']); ?> /> <?php _e('Delete all Organize Series related data from the database when deleting this plugin?  (BE CAREFUL!)', $org_domain); ?>
				</label>
				</span>
			</td>
		</tr>
	<?php
}	

function org_series_echo_series_templates($settings) {
	global $org_domain;
	?>
	<tr>
		<th scope="row" valign="top" class="seriesdescr"><?php _e('Template Tag options', $org_domain); ?><br />
			<small><?php _e('This section is where you tell the plugin how you would like to format the various displays of the series information.  Only play with this if you are familiar with html/css.  Use the "template tokens" to indicate where various series related data should go and/or where the template tag should be inserted (if auto-tag is enabled).', $org_domain); ?></small>
		</th>
		<td>
			<strong><?php _e('Series Post List Template:', $org_domain); ?></strong><br />
			<small><?php _e('This affects the list of series in a post on the page of a post belonging to a series [template tag -> wp_postlist_display()]', $org_domain); ?></small><br />
			<textarea name="series_post_list_template" id="series_post_list_template" rows="4" class="template"><?php echo htmlspecialchars($settings['series_post_list_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Series Post List Post Title Template:', $org_domain); ?></strong><br />
			<small><?php _e('Use this to indicate what html tags will surround the post title in the series post list.', $org_domain); ?></small><br/>
			<textarea name="series_post_list_post_template" id="series_post_list_post_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_post_list_post_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Series Post List Current Post Title Template:', $org_domain); ?></strong><br />
			<small><?php _e('Use this to style how you want the post title in the post list that is the same as the current post to be displayed.', $org_domain); ?></small><br />
			<textarea name="series_post_list_currentpost_template" id="series_post_list_currentpost_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_post_list_currentpost_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Series Post Navigation Template:', $org_domain); ?></strong><br />
			<small><?php _e('Use this to style the Next/Previous post navigation strip on posts that are part of a series. (Don\'t forget to use the %postcontent% token to indicate where you want the navigation to show).', $org_domain); ?></small><br />
			<textarea name="series_post_nav_template" id="series_post_nav_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_post_nav_template']);?></textarea><br />
			<br />
			<input name="series_nextpost_nav_custom_text" id="series_nextpost_nav_custom_text" type="text" value="<?php echo $settings['series_nextpost_nav_custom_text']; ?>" size="40" /> <?php _e('Custom next post navigation text.', $org_domain); ?><br />
			<input name="series_prevpost_nav_custom_text" id="series_prevpost_nav_custom_text" type="text" value="<?php echo $settings['series_prevpost_nav_custom_text']; ?>" size="40" /> <?php _e('Custom previous post navigation text.', $org_domain); ?><br />
			<br />
			<strong><?php _e('Series Table of Contents Listings:', $org_domain); ?></strong><br />
			<small><?php _e('This will affect how each series is listed on the Series Table of Contents Page (created at plugin init) [template tag -> wp_serieslist_display()]', $org_domain); ?></small><br />
			<textarea name="series_table_of_contents_box_template" id="series_table_of_contents_box_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_table_of_contents_box_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Series Meta:', $org_domain); ?></strong><br />
			<small><?php _e('This will control how and what series meta information is displayed with posts that are part of a series. [template tag -> wp_seriesmeta_write()]', $org_domain); ?></small><br />
			<textarea name="series_meta_template" id="series_meta_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_meta_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Series Meta (with excerpts):', $org_domain); ?></strong><br />
			<small><?php _e('This will control how and what series meta information is displayed with posts that are part of a series when the_excerpt is called. [template tag -> wp_seriesmeta_write(true)]', $org_domain); ?></small><br />
			<textarea name="series_meta_excerpt_template" id="series_meta_excerpt_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['series_meta_excerpt_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Latest Series (tags before):', $org_domain); ?></strong><br />
			<small><?php _e('Put here any html you want before latest series information NOTE: series template tokens WILL NOT be converted here.', $org_domain); ?></small><br />
			<textarea name="latest_series_before_template" id="latest_series_before_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['latest_series_before_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Latest Series (inner tags):', $org_domain); ?></strong><br />
			<small><?php _e('This will control the layout/style and contents that will be returned with the latest_series() template tag (both via widget and/or manual calls).  NOTE: Organize Series %tokens% can be used in this field.', $org_domain); ?></small><br />
			<textarea name="latest_series_inner_template" id="latest_series_inner_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['latest_series_inner_template']); ?></textarea><br />
			<br />
			<strong><?php _e('Latest Series (tags after):', $org_domain); ?></strong><br />
			<small><?php _e('Put here any html you want after latest series information NOTE: series template tokens WILL NOT be converted here.', $org_domain); ?></small><br />
			<textarea name="latest_series_after_template" id="latest_series_after_template" rows="4"  class="template"><?php echo htmlspecialchars($settings['latest_series_after_template']); ?></textarea><br />
		</td>
	</tr>
	<?php
}

function org_series_echo_series_icon($settings) {
	global $org_domain;
?>
	<tr>
		<th scope="row" valign="top" class="seriesdescr"><?php _e('Series Icon Options', $org_domain); ?><br />
			<small><?php _e('This section is for setting the series icon options (note if you do not include one of the %tokens% for series icon in the template settings section then series-icons will not be displayed. All images for series-icons will upload into your default wordpress upload directory.', $org_domain); ?></small>
		</th>
		<td>
			<input name="series_icon_width_series_page" id="series_icon_width_series_page" type="text" value="<?php echo $settings['series_icon_width_series_page']; ?>" size="10" /> <?php _e('Width for icon on series table of contents page (in pixels).', $org_domain); ?>
			<br />
			<input name="series_icon_width_post_page" id="series_icon_width_post_page" type="text" value="<?php echo $settings['series_icon_width_post_page']; ?>" size="10" /> <?php _e('Width for icon on a post page (in pixels).', $org_domain); ?>
			<br />	
			<input name="series_icon_width_latest_series" id="series_icon_width_latest_series" type="text" value="<?php echo $settings['series_icon_width_latest_series']; ?>" size="10" /> <?php _e('Width for icon if displayed via the latest series template (in pixels).', $org_domain); ?>
			</td>
		</tr>
<?php
}

org_series_admin_page();
?>
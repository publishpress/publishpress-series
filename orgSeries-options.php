<?php

function org_series_import() {
	//this will contain the actual query and code for importing from version 1.6 -> 2.0+.  It is called from if conditions for it being called are met.
	global $wpdb;
	$import_series = isset($_POST['import_series']) ? 1 : 0;
	$delete_series = isset($_POST['delete_series']) ? 1 : 0;
	$import_cat_icons = isset($_POST['import_cat_icons']) ? 1 : 0;
	$import_options = isset($_POST['import_options']) ? 1 : 0;
	$do_nothing = isset($_POST['do_nothing']) ? 1 : 0;
	
	$oldsettings = get_option('org_series_options');
	$series_cats = $oldsettings['series_cats'];
	$message = '<div class="updated"><p>The following imports have been completed successfully:</p>';  
	
	if ($do_nothing) {
		update_option('org_series_oldversion', '0'); //will prevent import form from being called again.
		return $message = '<p>You selected nothing to be done and if there are no other messages then that\'s exactly what happened!</p>';
	}
	
	if ( $import_cat_icons  && !(function_exists('ig_caticons_get_icons')) ) {
		return $message = '<div class="updated"><p><strong>You indicated your desire to import category icons.  However, the category-icons plugin is not installed and is necessary for the import to continue.  Please activate the category-icons plugin before doing the import or don\'t select to import the icons.</strong></p></div>';
		}
	
	if ( empty( $series_cats ) || $series_cats == 0 )  { 
		return $message = '<div class="updated"><p><strong>Something went wrong with the import...are you sure you had a previous version of OrgSeries installed?</strong></p></div>';
	}
	
	if ( $import_series ) {
		$old_series_cats_args = array('child_of' => $series_cats, 'hide_empty' => false);
		$old_series_cats = get_terms('category', $old_series_cats_args);
		$message .= '<p>Series from the old category structure:</p><ul>';
				
		foreach ( $old_series_cats as $old_series ) {
			$oldseries_id = $old_series->term_id;
			$series_slug = $old_series->slug;
			$series_name = $old_series->name;
			$series_description = $old_series->description;
			$seriesposts = get_objects_in_term($oldseries_id, 'category');
			$series_id = wp_insert_series( array('series_name' => $series_name, 'series_nicename' => $series_slug, 'series_description' => $series_description) );
			$message .='<li>' . $series_name . ' added (';
			$postcount = 0;
			
			foreach ($seriesposts as $seriespost) {
				$seriespostid = $seriespost;
				$message .= 'postid = ' . $seriespostid . '...';
				wp_set_post_series($seriespostid, $series_id);
				$postcount++;				
			}
			
			( $postcount == 1 ) ? $word = 'post' : $word = 'posts';
			$message .= $postcount . ' ' . $word . ' in the series).<br />'; 
			
			if ( $delete_series )  { 
				wp_delete_category($oldseries_id);
				$message .= 'Old series category deleted.<br />';
			}
			
			if ( $import_cat_icons ) {
				list($icon, $small_icon) = ig_caticons_get_icons($oldseries_id);
				$write = seriesicons_write($series_id,$icon);
				if ( $write ) {
					$message .='Series Icon imported';
					
					if ( $delete_series ) {
						$cat = $wpdb->escape($oldseries_id);
						$wpdb->query("DELETE FROM $wpdb->ig_caticons WHERE cat_id='$cat'");
					}
				} else { 
					$message .='Something went wrong with the series icon import!';
				}
			}
			
			$message .= '</li>';
		}
		if ( $delete_series )  { 
				wp_delete_category($series_cats);
				$message .= 'Old series meta-category deleted.<br />';
			}
	
		$message .= '</ul>';
	}
	
	if ( $import_options ) {
			$settings = get_option('org_series_options');
			extract($settings, EXTR_SKIP);
			
		//build variables for the new options array
			$custom_css = $custom_css;
			$auto_tag_toggle = $auto_tag_toggle;
			$auto_tag_seriesmeta_toggle = $auto_tag_seriesmeta_toggle;
			$url = parse_url(get_bloginfo('siteurl'));
			$series_toc_url = $url['path'] . '/series/';
						
			//build series-post-list-template
			$series_post_list_template = $beforelistbox_post_page;
			$series_post_list_template .= $series_intro_text_post_page;
			if (1 == $cat_icon_chk_post_page ) $series_post_list_template .= '<div class="center">%series_icon_linked%</div>';
			if (1 == $text_chk_post_page ) {
				if (1 == $cat_title_chk_post_page ) $series_post_list_template .= $before_series_title_post_page . '%series_title_linked%' . $after_series_title_post_page;
				if (1 == $cat_description_cat_post_page ) $series_post_list_template .= $before_description_post_page . '%series_description%' . $after_description_post_page;
			}
			$series_post_list_template .= $before_post_title_list_post_page . '%post_title_list%' . $after_post_title_list_post_page;
			$series_post_list_template .= $afterlistbox_post_page . '%postcontent%';
			$series_post_list_template = trim(stripslashes($series_post_list_template));
			
			$series_post_list_post_template = $before_title_post_page . '%post_title_linked%' . $after_title_post_page;
			$series_post_list_post_template = trim(stripslashes($series_post_list_post_template));
			
			$series_post_list_currentpost_template = '<li class="serieslist-li-current">%post_title%</li>';
			
			//build series-meta-template
			$series_meta_template = $before_series_meta . 'This' . $series_meta_word . 'is part %series_part% of %total_posts_in_series% in the series %series_title_linked%' . $after_series_meta . '%postcontent%';
			$series_meta_template = trim(stripslashes($series_meta_template));
			
			//build series-table-of-contents-box-template
			$series_table_of_contents_box_template = $beforedisplay_cat_page;
			if (1 == $cat_icon_chk_cat_page ) {
				$series_table_of_contents_box_template .= $before_cat_icon_cat_page . '%series_icon_linked%' . $after_cat_icon_cat_page;
			}
			if ( 1 == $text_chk_cat_page ) {
				$series_table_of_contents_box_template .= $before_catlist-content_cat_page;
				if ( 1 == $cat_title_chk_cat_page ) {
					$series_table_of_contents_box_template .= $beforetitle_cat_page . '%series_title_linked%' . $aftertitle_cat_page;
				}
				if ( 1 == $cat_description_cat_page ) {
					$series_table_of_contents_box_template .= $beforedescript_cat_page . '%series_description%' . $afterdescript_cat_page;
				}
				$series_table_of_contents_box_template .= $after_catlist-content_cat_page;
			}
			$series_table_of_contents_box_template .= $afterdisplay_cat_page;
			$series_table_of_contents_box_template = trim(stripslashes($series_table_of_contents_box_template));
			
		//add new next/previous post template
			$series_post_nav_template = '%postcontent%<fieldset><legend>Series Navigation</legend><span class="series-nav-left">%previous_post%</span><span class="series-nav-right">%next_post%</span></fieldset>';
			$series_nextpost_nav_custom_text = 'Next Post in Series';
			$series_prevpost_nav_custom_text = 'Previous Post in Series';
			
		//sorting options
			$series_posts_orderby = 'meta_value';
			$series_posts_order = 'ASC';
			
		//latest series options
			$series_icon_width_latest_series = '100';
			$latest_series_template = '<div class="latest-series"><div style="text-align: center;">%series_icon_linked%</div></div>';
			
		//build new options array
		$new_options = array(
			'custom_css' => $custom_css,
			'auto_tag_toggle' => $auto_tag_toggle,
			'auto_tag_seriesmeta_toggle' => $auto_tag_seriesmeta_toggle,
			'series_toc_url' => $series_toc_url,
			'series_post_list_template' => $series_post_list_template,
			'series_post_list_post_template' => $series_post_list_post_template,
			'series_post_list_currentpost_template' => $series_post_list_currentpost_template,
			'series_meta_template' => $series_meta_template,
			'series_table_of_contents_box_template' => $series_table_of_contents_box_template,
			'series_icon_width_series_page' => $series_icon_width_series_page,
			'series_icon_width_post_page' => $series_icon_width_post_page,
			'series_post_nav_template' => $series_post_nav_template,
			'series_nextpost_nav_custom_text' => $series_nextpost_nav_custom_text,
			'series_prevpost_nav_custom_text' => $series_prevpost_nav_custom_text,
			'series_posts_orderby' => $series_posts_orderby,
			'series_posts_order' => $series_posts_order,
			'latest_series_template' => $latest_series_template,
			'series_icon_width_latest_series' => $series_icon_width_latest_series);
		
		delete_option('org_series_options');
		add_option('org_series_options', $new_options, 'Array of options for the Organize Series plugin');
		$message .= '<p>Option settings have been imported and old option/value pairs deleted.</p>';
	}
		
	update_option('org_series_oldversion', '0'); //this will prevent the import form from being called again?
	$message .= '</div>';
	return $message;
}				

function org_series_init($reset = false) {
	$oldversion = get_option('org_series_oldversion');
	
	if (!$reset)  { 
		$settings = get_option('org_series_options');
	}
	
	if (!($is_initialized=get_option('org_series_is_initialized')) || empty ($settings) || $reset || '1.6' == $oldversion) {
		$url = parse_url(get_bloginfo('siteurl'));
		$init_settings = array( //options for the orgSeries plugin
		//main settings
			'custom_css' => 1, 
			'auto_tag_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series.
			'auto_tag_seriesmeta_toggle' => 1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
			'series_toc_url' => $url['path'] . '/series/',
		//new template options
			'series_post_list_template' => '<div class="seriesbox"><div class="center">%series_icon_linked%<br />%series_title_linked%</div><ul class="serieslist-ul">%post_title_list%</ul></div>%postcontent%',
			'series_post_list_post_template' => '<li class="serieslist-li">%post_title_linked%</li>',
			'series_post_list_currentpost_template' => '<li class="serieslist-li-current">%post_title%</li>',
			'series_meta_template' => '<div class="seriesmeta">This entry is part %series_part% of %total_posts_in_series% in the series %series_title_linked%</div>%postcontent%',
			'series_table_of_contents_box_template' => '<div class="serieslist-box"><div class="imgset">%series_icon_linked%</div><div class="serieslist-content"><h2>%series_title_linked%</h2><p>%series_description%</p></div><hr style="clear: left; border: none" /></div>',
			'latest_series_template' => '<div class="latest-series"><div style="text-align: center;">%series_icon_linked%</div></div>',
			'series_post_nav_template' => '%postcontent%<fieldset><legend>Series Navigation</legend><span class="series-nav-left">%previous_post%</span><span class="series-nav-right">%next_post%</span></fieldset>',
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
		
		update_option('org_series_is_initialized', 1, 'Organize Series Plugin has been initialized');
		update_option('org_series_options', $newSettings, 'Array of options for the Organize Series plugin');
		
		if ( ($is_initialized=get_option('org_series_is_initialized') ) && ($oldversion == '1.6') ) { ?>
			<div class="updated"><p><strong>Organize Series Plugin has been initialized. However it has been detected that you have had a previous version of the plugin installed.  If you want you can import your existing series schema and old series options you had set up by selecting from the following intial options.</strong></p></div>
			<?php
			return;
		}
		
		if ($is_initialized=get_option('org_series_is_initialized') ) { ?>
			<div class="updated"><p><strong>Organize Series Plugin has been initialized.</strong></p></div>
			<?php
			return;
		}
		return;
		}
	return;
}

function org_series_option_update() {
	global $wpdb;
	
	$url = parse_url(get_bloginfo('siteurl'));
	//toggles and paging info
	$settings['auto_tag_toggle'] = isset($_POST['auto_tag_toggle']) ? 1 : 0;
	$settings['auto_tag_seriesmeta_toggle'] = isset($_POST['auto_tag_seriesmeta_toggle']) ? 1 : 0;
	$settings['custom_css'] = isset($_POST['custom_css']) ? 1 : 0;
	if ( isset($_POST['series_toc_url']) ) $settings['series_toc_url'] = $url['path'] . '/' . $_POST['series_toc_url'];
	if (!ereg('.*/$', $settings['series_toc_url'])) $settings['series_toc_url'] .= '/';
	if (strlen($_POST['series_toc_url']) <=0) $settings['series_toc_url'] = FALSE;
		
	//template options
	if ( isset($_POST['series_post_list_template']) ) $settings['series_post_list_template'] = trim(stripslashes($_POST['series_post_list_template']));
	if ( isset($_POST['series_post_list_post_template']) ) $settings['series_post_list_post_template'] = trim(stripslashes($_POST['series_post_list_post_template']));
	if ( isset($_POST['series_post_list_currentpost_template']) ) $settings['series_post_list_currentpost_template'] = trim(stripslashes($_POST['series_post_list_currentpost_template']));
	if ( isset($_POST['series_meta_template']) ) $settings['series_meta_template'] = trim(stripslashes($_POST['series_meta_template']));
	if ( isset($_POST['series_table_of_contents_box_template']) ) $settings['series_table_of_contents_box_template'] = trim(stripslashes($_POST['series_table_of_contents_box_template']));
	if ( isset($_POST['series_post_nav_template']) ) $settings['series_post_nav_template'] = trim(stripslashes($_POST['series_post_nav_template']));
	if ( isset($_POST['series_nextpost_nav_custom_text']) ) $settings['series_nextpost_nav_custom_text'] = trim(stripslashes($_POST['series_nextpost_nav_custom_text']));
	if ( isset($_POST['series_prevpost_nav_custom_text']) ) $settings['series_prevpost_nav_custom_text'] = trim(stripslashes($_POST['series_prevpost_nav_custom_text']) );
	if ( isset($_POST['series_posts_orderby']) ) $settings['series_posts_orderby'] = trim(stripslashes($_POST['series_posts_orderby']) );
	if ( isset($_POST['series_posts_order']) ) $settings['series_posts_order'] = trim(stripslashes($_POST['series_posts_order']) );
	if ( isset($_POST['latest_series_template']) ) $settings['latest_series_template'] = trim(stripslashes($_POST['latest_series_template']));
	
	//series-icon related settings
	if ( isset($_POST['series_icon_width_series_page']) ) $settings['series_icon_width_series_page'] = $_POST['series_icon_width_series_page'];
	if ( isset($_POST['series_icon_width_post_page']) ) $settings['series_icon_width_post_page'] = $_POST['series_icon_width_post_page'];
	if ( isset($_POST['series_icon_width_latest_series']) ) $settings['series_icon_width_latest_series'] = $_POST['series_icon_width_latest_series'];
		
	$settings['last_modified'] = gmdate("D, d M Y H:i:s", time());
	update_option('org_series_options', $settings);
}

function org_series_admin_page() {
	global $wp_version;
	?>
	<div class="wrap" id ="orgSeries_div">
		<h2><?php _e('Organize Series Plugin Options'); ?></h2>
	<?php
	
	if (isset($_POST['import_option'])) {
		$message = org_series_import();
		echo $message;
	}
	
	if (isset($_POST['submit_option'])) {
		if (isset($_POST['reset_option'])) {
			org_series_init(true);
		} else {
			org_series_option_update(); ?>
			<div class="updated"><p>Organize Series Plugin Options have been updated</p></div>
			<?php
		}
	}
	
	org_series_init();
	$oldversion = get_option('org_series_oldversion');
	$settings = get_option('org_series_options');
		
	if ( '1.6' == $oldversion ) org_series_import_form();	
		
		?>
	<div id="poststuff">
	<div id="moremeta">
	<div id="grabit" class="dbx-group">
	<fieldset id="pluginmetadiv" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Plugin Info') ?></h3>
		<div class="dbx-content">
			<p>Plugin information can be found <a href="http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin" title="The Organize Series Plugin page at unfoldingneurons.com">here</a></p>
			<p><a href="http://unfoldingneurons.com/series/organize-series-usage-tips" title="usage tips">For usage help check out this series!</a></p>
			<p><a href="http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin" title="Plugin Support">Plugin Support Forums</a></p>
		<p>If you'd like to donate to <a href="http://www.unfoldingneurons.com" title="Darren Ethier's (author) Blog">me</a> as an expression of thanks for the release of this plugin feel free to do so - and thanks!</p>
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick" />
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" />
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
" />
</form>
		</div>
	</fieldset>
	
	<fieldset id="templatetokenslegend" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Token legend'); ?></h3>
		<div class="dbx-content">
			<p>The following is a legend of the tokens that are available for use in the custom template fields. These will be replaced with the appropriate values when the plugin runs.</p>
			<dl>
				<dt>%series_icon%</dt>
					<dd>This will be replaced with the series icon for a series.</dd>
				<dt>%series_icon_linked%</dt>
					<dd>Same as %series_icon% except that the series icon will be linked to the series page</dd>
				<dt>%series_list%</dt>
					<dd>This token is for use with the orgSeries widget only - it references where you want the list of series titles to be inserted and requires that the template for each series title be also set.</dd>
				<dt>%series_title%</dt>
					<dd>This will be replaced with the title of a series</dd>
				<dt>%series_title_linked%</dt>
					<dd>Same as %series_title% except that it will also be linked to the series page</dd>
				<dt>%post_title_list%</dt>
					<dd>Is the location token for where the contents of the post list post templates will appear.</dd>
				<dt>%post_title%</dt>
					<dd>Will be replaced with the post title of a post in the series</dd>
				<dt>%post_title_linked%</dt>
					<dd>Will be replaced with the post title of a post in the series linked to the page view of that post.</dd>
				<dt>%previous_post%</dt>
					<dd>Will be replaced by the navigation link for the previous post in a series. The text will be the title of the post.</dd>
				<dt>%previous_post_custom%</dt>
					<dd>Same as %previous_post% except the text will be what you specify in the "Custom Previous Post Navigation Text" field.</dd>
				<dt>%next_post%</dt>
					<dd>Will be replaced by the navigation link for the next post in a series. The text will be the title of the post.</dd>
				<dt>%next_post_custom%</dt>
					<dd>Same as %next_post% except the text will be what you specify in the "Custom Next Post Navigation Text" field.</dd>
				<dt>%postcontent%</dt>
					<dd>Use this tag either before or after the rest of the template code.  It will indicate where you want the content of a post to display.</dd>
				<dt>%series_part%</dt>
					<dd>Will display what part of a series the post is</dd>
				<dt>%total_posts_in_series%</dt>
					<dd>Will display the total number of posts in a series</dd>
				<dt>%series_description%</dt>
					<dd>Will display the description for the series</dd>
			</dl>
		</div>
	</fieldset>
			
	<?php if (file_exists(ABSPATH . WPINC . '/rss.php')) { ?>	
		<div id="orgseriesnews">
			<?php include(ABSPATH . 'wp-content/plugins/' . SERIES_DIR .'/organize-series-feed.php'); ?>
		</div> <?php /*rss feed related */ ?>
		<?php } else { ?>
		<fieldset id="orgSeriesupdatefeed" class="dbx-box">
		<h3 class="dbx-handle"><?php _e('Organize Series News'); ?></h3>
		<div class="dbx-content">
		<p>Upgrade to Wordpress 2.1+ to gain the News Feed feature for the Organize Series Plugin.  This feature checks with the plugin related posts on <a href="http://unfoldingneurons.com">UnfoldingNeurons.com</a> and pulls the titles to display on this page for a quick way to see if there are any updates to the plugin.</p>
		</div>
		</fieldset>
		<?php } ?>
		</div>
	</div>
	<form action="" method="post">
	<input type="hidden" name="submit_option" value="1" />
	<div id="advancedstuff" class="dbx-group">
		
<?php	
	org_series_echo_fieldset_mainsettings($settings);
	org_series_echo_series_templates($settings);
	org_series_echo_series_icon($settings);
?>
	</div>
	<div>
		<p class="submit">
			<input type="submit" name="update_orgseries" value="<?php _e('Update Options'); ?>" />
			<input type="submit" onclick='return confirm("Do you really want to reset to default options (all your custom changes will be lost)?");' name="reset_option" value="<?php _e('Reset options to default') ?>" />
		</p>
	</div>
	</form>
	</div>
</div>
<?php
}
				
function org_series_import_form() {
 ?>
	<div class="import-box">
			<h3>Import Options:</h3>
			<p>It is important that you make at least one selection and submit this form otherwise you won't be able to save any changes to your Organize Series Options - if you want to start fresh but leave the old Organize Series data in the database then select "do nothing"</p>
			<form action="" method="post">
				<input type="hidden" name="import_option" value="1" />
				<input name="import_series" id="import_series" type="checkbox" value="1" />Import Series
					<p>Selecting this means you wish to import all series data from old category schema into the new version of Organize Series</p>
				<input name="delete_series" id="delete_series" type="checkbox" value="1" />Delete old series data
					<p>Selecting this means you wish to remove the old series categories in Organize Series 1.6.3 and lower.  NOTE: this will only be applied if you've also selected to import the series.</p>
				<input name="import_cat_icons" id="import_cat_icons" type="checkbox" value="1" />Import Category Icons
					<p>Selecting this means you wish to import category icons associated with series in the old version of Organize Series to the new integrated series icons in the new version.  NOTE: This option will only work if you've selected to Import Series and have the Category Icons Plugin installed and activated.  You can deactivate the category icons plugin after the import.</p>
				<input name="import_options" id="import_options" type="checkbox" value="1" />Import Organize Series Options
					<p>Selecting this means that you want to import all your customizations from the old Organize Series Options into the new token template system</p>
				<input name="do_nothing" id="do_nothing" type="checkbox" value="1" />Do nothing
				<p>Selecting this option will make no changes to the old data in your database (leaving all categories and category icons and post associations as is).  Some options will be overwritten as you use the plugin.  If you select other import options they will override selecting this checkbox.</p>
				<p class="submit">
					<input type="submit" name="Import_Series" value="<?php _e('Import') ?>" />
				</p>
			</form>
	</div>
	<?php
}

function org_series_echo_fieldset_mainsettings($settings) {
	$url = parse_url(get_bloginfo('siteurl'));
	$url = $url['path'] . '/';
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="main-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
	<h3 class="dbx-handle"><?php _e('Automation Settings'); ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content">
			<p><em>Choose from the following options for turning on or off automatic insertion of template tags for Organize Series into your blog.  If you wish to have more control over the location of the template tags (you power user you) then deselect as needed.</em></p>
			<div class="org-option">
				<input name="auto_tag_toggle" id="auto_tag_toggle" type="checkbox" value="<?php echo $settings['auto_tag_toggle']; ?>" <?php checked('1', $settings['auto_tag_toggle']); ?> /> Display list of series on post pages?
			</div>
			<div class="org-description">
				<p>Selecting this will indicate that you would like the plugin to automatically insert the code into your theme for the listing of posts in a series when a post is displayed that is part of a series.  [default=selected]</p>
			</div>
			<div class="org-option">
				<input name="auto_tag_seriesmeta_toggle" id="auto_tag_seriesmeta_toggle" type="checkbox" value="<?php echo $setttings['auto_tag_seriesmeta_toggle']; ?>" <?php checked('1', $settings['auto_tag_seriesmeta_toggle']); ?> /> Display series meta information with posts?
			</div>
			<div class="org-description">
				<p>Series meta will include whatever is listed in the Template tag options for the series meta tag (see settings on this page). [default = selected]</p>
			</div>
			<div class="org-option">
				<input name="custom_css" id="custom_css" type="checkbox" value="<?php echo $setttings['custom_css']; ?>" <?php checked('1', $settings['custom_css']); ?> /> Use custom .css?
			</div>
			<div class="org-description">
				<p>Leaving this box checked will make the plugin use the included .css file.  If you uncheck it you will need to add styling for the plugin in your themes "style.css" file. [default = checked]</p>
			</div>
			<div class="org-option">
				Series Table of Contents URL:<br />
				<?php bloginfo('siteurl') ?>/<input type="text" name="series_toc_url" value="<?php echo substr($settings['series_toc_url'], strlen($url)) ?>" /><br />
			</div>
			<div class="org-description">
				<p>Enter the path where you want the Series Table of Contents to be shown</p>
			</div>				
			<div class="org-option">
				<input name="series_posts_orderby" id="series_posts_orderby_part" type="radio" value="meta_value" <?php checked('meta_value', $settings['series_posts_orderby']); ?> />Order by series part
				<input name="series_posts_orderby" id="series_posts_orderby_date" type="radio" value="post_date" <?php checked('post_date', $settings['series_posts_orderby']); ?> />Order by date <br />
				<input name="series_posts_order" id="series_posts_order_ASC" type="radio" value="ASC" <?php checked('ASC', $settings['series_posts_order']); ?> />Ascending
				<input name="series_posts_order" id="series_posts_order_DESC" type="radio" value="DESC" <?php checked('DESC', $settings['series_posts_order']); ?> />Descending
			</div>
			<div class="org-description">
				<p>You can choose what order you want the posts on a series archive page to be displayed.  Default is by date, descending.
				</p>
			</div>
		</div>
	</div>
	</fieldset>
	</div>
	<?php
}	

function org_series_echo_series_templates($settings) {
	?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="series-template-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
		<h3 class="dbx-handle"><?php _e('Template Tag options'); ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content">
			<p>This section is where you tell the plugin how you would like to format the various displays of the series information.  Only play with this if you are familiar with html/css.  Use the "template tokens" to indicate where various series related data should go and/or where the template tag should be inserted (if auto-tag is enabled).</p>
			<div class="org-option">
				<p>Series Post List Template:</p>
				<p>This affects the list of series in a post on the page of a post belonging to a series [template tag -> wp_postlist_display()]</p>
				<textarea name="series_post_list_template" id="series_post_list_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_post_list_template']); ?></textarea>
				<br />
				<p>Series Post List Post Title Template:</p>
				<p>Use this to indicate what html tags will surround the post title in the series post list.</p>
				<textarea name="series_post_list_post_template" id="series_post_list_post_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_post_list_post_template']); ?></textarea>
				<br />
				<p>Series Post List Current Post Title Template:</p>
				<p>Use this to style how you want the post title in the post list that is the same as the current post to be displayed.</p>
				<textarea name="series_post_list_currentpost_template" id="series_post_list_currentpost_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_post_list_currentpost_template']); ?></textarea>
				<br />
				<p>Series Post Navigation Template:</p>
				<p>Use this to style the Next/Previous post navigation strip on posts that are part of a series. (Don't forget to use the %postcontent% token to indicate where you want the navigation to show).</p>
				<textarea name="series_post_nav_template" id="series_post_nav_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_post_nav_template']);?></textarea>
				<br />
				<input name="series_nextpost_nav_custom_text" id="series_nextpost_nav_custom_text" type="text" value="<?php echo $settings['series_nextpost_nav_custom_text']; ?>" size="40" /> Custom next post navigation text.<br />
				<input name="series_prevpost_nav_custom_text" id="series_prevpost_nav_custom_text" type="text" value="<?php echo $settings['series_prevpost_nav_custom_text']; ?>" size="40" /> Custom previous post navigation text.<br />
				<p>Series Table of Contents Listings:</p>
				<p>This will affect how each series is listed on the Series Table of Contents Page (created at plugin init) [template tag -> wp_serieslist_display()]</p>
				<textarea name="series_table_of_contents_box_template" id="series_table_of_contents_box_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_table_of_contents_box_template']); ?></textarea>
				<br />
				<p>Series Meta:</p>
				<p>This will control how and what series meta information is displayed with posts that are part of a series. [template tag -> wp_seriesmeta_write()]</p>
				<textarea name="series_meta_template" id="series_meta_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['series_meta_template']); ?></textarea>
				<br />
				<p>Latest Series:</p>
				<p>This will control the layout/style and contents that will be returned with the latest_series() template tag (both via widget and/or manual calls).</p>
				<textarea name="latest_series_template" id="latest_series_template" rows="4" cols="100" class="template"><?php echo htmlspecialchars($settings['latest_series_template']); ?></textarea>
			</div>
		</div>
	</div>
	</fieldset>
	</div>
	<?php
}

function org_series_echo_series_icon($settings) {
?>
	<div class="dbx-b-ox-wrapper">
	<fieldset id="series-icon-options" class="dbx-box">
	<div class="dbx-h-andle-wrapper">
		<h3 class="dbx-handle"><?php _e('Series Icon Options'); ?></h3>
	</div>
	<div class="dbx-c-ontent-wrapper">
		<div class="dbx-content">
			<p>This section is for setting the series icon options (note if you do not include one of the %tokens% for series icon in the template settings section then series-icons will not be displayed. All images for series-icons will upload into your default wordpress upload directory.</p>
			<div class="org-option">
				<input name="series_icon_width_series_page" id="series_icon_width_series_page" type="text" value="<?php echo $settings['series_icon_width_series_page']; ?>" size="10" /> Width for icon on series table of contents page (in pixels).
			</div>
			<div class="org-option">
				<input name="series_icon_width_post_page" id="series_icon_width_post_page" type="text" value="<?php echo $settings['series_icon_width_post_page']; ?>" size="10" /> Width for icon on a post page (in pixels).
			</div>	
			<div class="org-option">
				<input name="series_icon_width_latest_series" id="series_icon_width_latest_series" type="text" value="<?php echo $settings['series_icon_width_latest_series']; ?>" size="10" /> Width for icon if displayed via the latest series template (in pixels).
			</div>
		</div>
	</div>
	</fieldset>
	</div>
<?php
}

org_series_admin_page();
?>
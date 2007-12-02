<?php
//basic outline for code that needs to go here - for the organize series options page.
/*
** check if this is an init load or a submit or reset
** do a test for whether there's an earlier version installed.  IF so, give option to import series and options from earlier version.  
** If first install of orgSeries -> initialize default options.
** do i want separate pages for options?
** after setting this up I will have to do an overhal of the postlist display and the series meta code.
*/

function org_series_import($import_series = false, $delete_series_cats = false, $import_cat_icons = false, $import_options = false, $do_nothing = false) {
	//this will contain the actual query and code for importing from version 1.6 -> 2.0.  It is called from if conditions for it being called are met.
	$oldsettings = get_option('org_series_options');
	$series_cats = $oldsettings['series_cats'];
	$message = '';  //TODO - add in message indicators so that the return will list what categories were imported and whatelse was successfully completed (thus giving an indicator to the user if it worked properly or not).
	if ( empty( $series_cats ) || $series_cats == 0 )  { 
		return $message = '<div class="updated"><p><strong>Something went wrong with the import...are you sure you had a previous version of OrgSeries installed?</strong></p></div>';
	}
	
	if ( $import_series ) {
		$old_series_cats_args = array('child_of' => $series_cats, 'hide_empty' => false);
		$old_series_cats = get_terms('category', $old_series_cats_args);
		
		foreach ( $old_series_cats as $old_series ) {
			$oldseries_id = $old_series->term_id;
			$series_slug = $old_series->slug;
			$series_name = $old_series->name;
			$series_description = $old_series->description;
			$posts = get_posts($series_id);
			$series_id = wp_insert_series( array('series_name' => $series_name, 'series_nicename' = $series_slug, 'series_description' => $series_description) );
			
			foreach ($posts as $post) {
				$id = $post->ID;
				wp_set_post_series($id, $series_id);
			}
			
			if ( $delete_series_cats ) wp_delete_category($oldseries_id);
			
			if ( $import_cat_icons ) {
				//TODO ...write the new series-icon code so I can do the imports!
			}
		}
	}
	
	if ( $import_options ) {
			$settings = get_option('org_series_options');
			extract($settings, EXTR_SKIP);
			
		//build variables for the new options array
			$custom_css = $custom_css;
			$perpage_series_page = $perpage_cat_page;
			$auto_tag_toggle = $auto_tag_toggle;
			$auto_tag_seriesmeta_toggle = $auto_tag_seriesmeta_toggle;
			$series_post_list_titleformat_template = $series_post_list_titleformat_template;
			
			//build series-post-list-template
			$series_post_list_template = $beforelistbox_post_page;
			$series_post_list_template .= $series_intro_text_post_page';
			if (1 == $cat_icon_chk_post_page ) $series_post_list_template .= '<div class="center">' . '%series_icon_linked%' . '</div>';
			if (1 == $text_chk_post_page ) {
				if (1 == $cat_title_chk_post_page ) $series_post_list_template .= $before_series_title_post_page . '%series_title_linked%' . $after_series_title_post_page;
				if (1 == $cat_description_cat_post_page ) $series_post_list_template .= $before_description_post_page . '%series_description%' . $after_description_post_page;
			}
			$series_post_list_template .= $before_post_title_list_post_page . '%post_title_list_linked%' . $after_post_title_list_post_page;
			$series_post_list_template .= $afterlistbox_post_page . '%postcontent%';
			$series_post_list_template = trim(stripslashes($series_post_list_template));
			
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
			
			//series_icon related settings
			$series_icon_width_series_page = $cat_icon_width_series_page;
			$series_icon_width_post_page = $cat_icon_width_series_page;
		
		//build new options array
		$new_options = array(
			'custom_css' => $custom_css,
			'perpage_series_page' => $perpage_series_page,
			'auto_tag_toggle' => $auto_tag_toggle,
			'auto_tag_seriesmeta_toggle' => $auto_tag_seriesmeta_toggle,
			'series_post_list_titleformat_template' => $series_post_list_titleformat_template,
			'series_post_list_template' => $series_post_list_template,
			'series_meta_template' => $series_meta_template,
			'series_table_of_contents_box_template' => $series_table_of_contents_box_template,
			'series_icon_width_series_page' => $series_icon_width_series_page,
			'series_icon_width_post_page' => $series_icon_width_post_page);
		
		delete_option('org_series_options');
		add_option('org_series_options', $new_options, 'Array of optoins for the Organize Series plugin');
	}
		
	if ($do_nothing) {
		//TODO return message indicating that nothing has been done!	Do check to see if there was any other selection - because if there was then obviously something WAS done!!
	}
	
	update_option('org_series_oldversion', '0'); //this will prevent the import from being called again?
}
			
			
				
			
			
				
				
	##option - import series from earlier version (transfer from category schema to new series schema)
		##option - delete category associations after import?
		##option - delete old series related categories.
	##option - import category-icons from category icon plugin into new series table schema (after import be sure to erase old category-icon tables)
	##option - import options from previous plugin (after import be sure to erase old option tables)
	##option - do nothing I want to start from scratch (this will delete old option tables that are not being used by the new version but will leave the old series category structure intact)
}

function org_series_init($reset = false) {
	if (!$reset)  { 
		$settings = get_option('org_series_options');
		$oldversion = get_option('org_series_oldversion');
	}
	
	if (!($is_initialized=get_option('org_series_is_initialized')) || empty ($settings) || $reset) {
		$init_settings = array( //options for the orgSeries plugin
		//main settings
			'custom_css' => 1, 
			'perpage_series_page' => 1, //1=on, 0=off
			'auto_tag_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series.
			'auto_tag_seriesmeta_toggle' => 1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
		//new template options
			'series_post_list_titleformat_template' => '<li class="serieslist-li">%post_title_linked%</li>',
			'series_post_list_template' => '<div class="seriesbox"><div class="center">%series_icon_linked%</div><ul class="serieslist-ul">%post_title_list_linked%</ul></div>%postcontent%',
			'series_meta_template' => '<div class="seriesmeta">This entry is part %series_part% of %total_posts_in_series% in the series %series_title_linked%</div>%postcontent%',
			'series_table_of_contents_box_template' => '<div class="serieslist-box"><div class="imgset">%series_icon_linked%</div><div class="serieslist-content"><h2>%series_title_linked%</h2><p>%series_description%</p></div></div>',
		//series_icon related settings
		'series_icon_width_series_page' => 200,
		'series_icon_width_post_page' =>100 );
			
		if (!empty ($settings)) {
			$newSettings = array_merge($init_settings, $settings);
		} else {
			$newSettings = $init_settings;
		}
		
		$newSettings['last_modified'] = gmdate("D, d M Y H:i:s", time());
		
		update_option('org_series_is_initialized', 1, 'Organize Series Plugin has been initialized');
		update_option('org_series_options', $newSettings, 'Array of options for the Organize Series plugin');
		
		if ( ($is_initialized=get_option('org_series_is_initialized') ) && ($old_version == '1.6') ) { ?>
			<div class="updated"><p><strong>Organize Series Plugin has been initialized. However it has been detected that you have had a previous version of the plugin installed.  If you want you can import your existing series schema and old series options you had set up by selecting from the following intial options.</strong></p></div>
			<?php
			} elseif ($is_initialized=get_option('org_series_is_initialized') ) { ?>
			<div class="updated"><p><strong>Organize Series Plugin has been initialized.</strong></p></div>
			<?php
			}
			return;
		}
		return;
}
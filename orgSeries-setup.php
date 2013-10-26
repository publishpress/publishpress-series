<?php

/**
* This file contains the orgSeries class which initializes the plugin and provides global variables/methods for use in the rest of the plugin.
*
* @package Organize Series
* @since 2.2
*/

if ( !class_exists('orgSeries') ) {

class orgSeries {

	var $settings;
	var $version = ORG_SERIES_VERSION;
	var $org_domain = 'organize-series';
	
	//__constructor
	function orgSeries() {
		global $wp_version;
		
		// WordPress version check
		if ( version_compare($wp_version, '3.0', '<'))
			add_action('admin_notices', array(&$this, 'update_warning'));
			
		//install OrgSeries
		add_action('activate_'.SERIES_DIR.'/orgSeries.php', array(&$this, 'org_series_install'));
		
		//all other actions and filters...
		add_action('plugins_loaded', array(&$this, 'add_settings'), 10);
		add_action('init', array(&$this, 'register_textdomain'));
		add_action('init', array(&$this, 'register_taxonomy'),0);
		add_action('admin_enqueue_scripts', array(&$this, 'register_scripts'));
		add_action('init', array(&$this, 'maybe_fix_upgrade'));
		add_filter('rewrite_rules_array', array(&$this,'seriestoc_rewrite_rules'));
		//add_action('init', array($this, 'rewrite_rules'));
		add_action('parse_query', array(&$this, 'seriestoc_parsequery'));
		add_filter('query_vars', array(&$this,'orgSeries_add_queryvars'));
		add_action('template_redirect', array(&$this,'orgSeries_toc_template')); //setsup the seriestoc url
					
		add_action('wp_enqueue_scripts', array(&$this, 'orgSeries_header'));
		add_action( 'wp_footer', array(&$this, 'series_dropdown_js'), 1 );
		add_filter('wp_title', array(&$this, 'seriestoc_title'));
		
		//series post list box
		add_action('the_content', array(&$this, 'add_series_post_list_box'), 12);
		
		//series meta strip
		add_filter('the_content', array(&$this, 'add_series_meta'), 12);
		add_filter('get_the_excerpt', array(&$this, 'orgseries_trim_excerpt'),1);
		add_filter('the_excerpt', array(&$this, 'add_series_meta_excerpt'));
		
		//joins, wheres, sortbys
		add_filter('posts_join_paged', array(&$this, 'sort_series_page_join'));
		add_filter('posts_where', array(&$this,'sort_series_page_where'));
		add_filter('posts_orderby', array(&$this,'sort_series_page_orderby'));
						
		//series post-navigation
		add_action('the_content', array(&$this, 'series_nav_filter'));
		
		//broswer page title
		add_filter('wp_title', array(&$this, 'add_series_wp_title'));
		
		//settings link on plugin page
		add_filter('plugin_action_links', array(&$this, 'AddPluginActionLink'), 10, 2);
	}
	
	function update_warning() {
		$msg = '<div id="wpp-message" class="error fade"><p>'.__('Your WordPress version is too old. Organize Series 2.2 requires at least WordPress 3.0 to function correctly. Please update your blog via Tools &gt; Upgrade.', 'organize-series').'</p></div>';
		echo trim($msg);
	}
	
	function maybe_fix_upgrade() {
		$version_chk = get_option('org_series_version');
		if ( empty($version_chk) ) { //we got a problem this shouldn't be empty!!
			update_option('org_series_version', '2.2.9');
			$this->update('2.2.9');
		}

		if ( $version_chk != $this->version )
			update_option( 'orgseries_version', $this->version );
		return;
	}
	
	function org_series_install() {
		global $wpdb, $wp_rewrite;
		$this->orgSeries_roles();
		//take care of old versioning routines
		$old_oldversion = get_option( 'org_series_oldversion' );
		$old_version = get_option( 'org_series_version' );
		if ( !empty( $old_version ) || $old_oldversion != '2.2' ) {
			$old_version = $old_version; 
		} else {
			$old_version = $old_oldversion;
		}
		
		if ( !empty( $old_version ) ) { //register the current version of orgSeries and check if any updates.
			if ( $old_version != $this->version ) {
				$this->update($old_version);
			}
			update_option( 'org_series_version', $this->version );
		} else {
			add_option("org_series_version", $this->version);
		}/**/
		
	//create table for series icons
	$table_name = $wpdb->prefix . "orgseriesicons";
	$sql = "CREATE TABLE $table_name (
		term_id INT NOT NULL,
		icon VARCHAR(100) NOT NULL,
		PRIMARY KEY  (term_id)
		);";
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
	
	add_option( 'series_icon_path', '' );
	add_option( 'series_icon_url', '' );
	add_option( 'series_icon_filetypes', 'jpg gif jpeg png' );
	}
	
	//function for all updates
	function update($version) {
		global $wpdb;


		//upgrading from 2.2
		if ( $version == '2.2'  || $version < '2.2') {
			$settings = get_option('org_series_options');
			
			if ( empty($settings['series_custom_base']) ) {
				$settings['series_custom_base'] = SERIES_QUERYVAR;
			} else {
				$settings['series_custom_base'] = preg_replace('/(^\/)|(\/$)/', '', $settings['series_custom_base']);
			}
			
			if ( empty($settings['series_toc_url'] ) ) {
				$settings['series_toc_url'] = SERIES_TOC_QUERYVAR;
			} else {
				$settings['series_toc_url'] = preg_replace('/(^\/)|(\/$)/', '', $settings['series_toc_url']);
			}
			
			update_option('org_series_options', $settings);
		}
		
		//upgrading for versions before 2.3. We're updating the series_part meta key to the new format for all posts that are a part of a series.
		if ( $version < '2.3' ) {
			$query = "SELECT p.ID, pm.meta_value FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id LEFT JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id LEFT JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE pm.meta_key = 'series_part' AND tt.taxonomy = 'series'";
			$posts = $wpdb->get_results($query);
			
			//let's cycle through the posts and update the meta_keys to the new format.
			if ( empty($posts) ) return; //get out there's no posts to update.
			foreach ($posts as $post) {
				$meta_key = SERIES_PART_KEY;
				$meta_value = $post->meta_value;
				add_post_meta($post->ID, $meta_key, $meta_value);
			}
			
			//let's take this opportunity to do some database cleanup.  We need to delete any post that has the SERIES_PART_KEY meta_key including those that are actually not part of a series (from some legacy bugs).
			$query = "SELECT p.ID FROM $wpdb->posts AS p LEFT JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id LEFT JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id LEFT JOIN $wpdb->term_taxonomy AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE pm.meta_key = 'series_part'";
			$posts = $wpdb->get_results($query);
			
			//now let's delete the meta_key/value combo from those posts
			if ( empty($posts) ) return; //no posts, let's get out and save the environment (sic).
			foreach ($posts as $post) {
				$meta_key = 'series_part';
				delete_post_meta($post->ID, $meta_key);
			}
		}
		return;
	}
	
	function register_scripts() {
		$url = WP_PLUGIN_URL.'/'.SERIES_DIR.'/js/';
		wp_register_script('inline-edit-series',$url.'inline-series.js', array('jquery'),ORG_SERIES_VERSION, TRUE);  
		wp_register_script( 'ajaxseries', $url.'series.js', array('jquery', 'jquery-ui-core', 'jquery-color'), ORG_SERIES_VERSION, TRUE );
		wp_localize_script( 'ajaxseries', 'seriesL10n', array(
				'add' => esc_attr(__('Add', 'organize-series')),
				'how' => __('Select "Not part of a series" to remove any series data from post', 'organize-series'),
				'addnonce' => wp_create_nonce('add-series-nonce')
			));
		wp_register_script( 'orgseries_options', $url.'orgseries_options.js', array('jquery', 'thickbox'), ORG_SERIES_VERSION, TRUE);
	}
	
	function orgSeries_roles() {
		global $wp_roles;
		$roles = array('administrator', 'editor');
		$capability = 'manage_series';
		
		foreach ($roles as $role) {
			$wp_roles->add_cap($role, $capability, true);
		}
		return true;
	}
	
	function register_textdomain() {
		$plugin_dir = basename(dirname(__FILE__)).'/lang';
		load_plugin_textdomain('organize-series', false, $plugin_dir);
	}
	
	function register_taxonomy() {
		$permalink_slug = $this->settings['series_custom_base'];
		$taxonomy = SERIES_QUERYVAR;
		$taxonomy_name = 'series';
		$object_type = apply_filters('orgseries_posttype_support', array('post'));
		$capabilities = array(
			'manage_terms' => 'manage_series',
			'edit_terms' => 'manage_series',
			'delete_terms' => 'manage_series',
			'assign_terms' => 'manage_series'
			);			
		$labels = array(
			'name' => _x('Series', 'taxonomy general name', 'organize-series'),
			'singular_name' => _x('Series', 'taxonomy singular name', 'organize-series'),
			'search_items' => __('Search Series', 'organize-series'),
			'popular_items' => __('Popular Series', 'organize-series'),
			'all_items' => __('All Series', 'organize-series'),
			'edit_item' => __('Edit Series', 'organize-series'),
			'update_item' => __('Update Series', 'organize-series'),
			'add_new_item' => __('Add New Series', 'organize-series'),
			'new_item_name' => __('New Series Name', 'organize-series'),
			'menu_name' => __('Manage Series', 'organize-series')
			);
		$args = array(
			'update_count_callback' => '_update_post_term_count',
			'labels' => $labels,
			'rewrite' => array( 'slug' => $permalink_slug, 'with_front' => true ),
			'show_ui' => true,
			'capabilities' => $capabilities,
			'query_var' => $this->settings['series_custom_base'],
			);
		register_taxonomy( $taxonomy_name, $object_type, $args );
	}

	function add_settings($reset = false) {
		$url = parse_url(get_bloginfo('url'));
		if ( !($this->settings = get_option('org_series_options')) || $reset == true ) {
			$this->settings = array(
				//main settings
			'custom_css' => 1, 
			'kill_on_delete' => 0, //determines if all series information (including series-icon tables) will be deleted when the plugin is deleted using the delete link on the plugins page.
			'auto_tag_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series.
			'auto_tag_nav_toggle' => 1, //sets the auto-tag insertions for the series navigation strip.
			'auto_tag_seriesmeta_toggle' => 1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
			'series_toc_url' => 'series-toc',
			'series_custom_base' => 'series',
			'series_perp_toc' => 3,
			'series_toc_title' => __('Series Table of Contents << ','organize-series'),
		//new template options
			'orgseries_api' => '',
			'series_post_list_template' => '<div class="seriesbox"><div class="center">%series_icon_linked%<br />%series_title_linked%</div><ul class="serieslist-ul">%post_title_list%</ul></div>%postcontent%',
			'series_post_list_post_template' => '<li class="serieslist-li">%post_title_linked%</li>',
			'series_post_list_currentpost_template' => '<li class="serieslist-li-current">%post_title%</li>',
			'series_meta_template' => '<div class="seriesmeta">' . _x('This entry is part %series_part% of %total_posts_in_series% in the series ','leave the %tokens% as is when translating','organize-series') . '%series_title_linked%</div>%postcontent%',
			'series_meta_excerpt_template' => '<div class="seriesmeta">' ._x('This entry is part %series_part% of %total_posts_in_series% in the series ','leave the %tokens% as is when translating','organize-series') . '%series_title_linked%</div>%postcontent%',
			'series_table_of_contents_box_template' => '<div class="serieslist-box"><div class="imgset">%series_icon_linked%</div><div class="serieslist-content"><h2>%series_title_linked%</h2><p>%series_description%</p></div><hr style="clear: left; border: none" /></div>',
			'latest_series_before_template' => '<div class="latest-series"><ul>',
			'latest_series_inner_template' => '<li>%series_title_linked%</li>',
			'latest_series_after_template' => '</ul></div>',
			'series_post_nav_template' => '%postcontent%<fieldset><legend>'. __('Series Navigation','organize-series') .'</legend><span class="series-nav-left">%previous_post%</span><span class="series-nav-right">%next_post%</span></fieldset>',
			'series_nextpost_nav_custom_text' => '%post_title% >>',
			'series_prevpost_nav_custom_text' => '<< %post_title%',
			//series_icon related settings
			'series_icon_width_series_page' => 200,
			'series_icon_width_post_page' =>100,
			'series_icon_width_latest_series' =>100,
			//series posts order options
			'series_posts_orderby' => 'meta_value',
			'series_posts_order' => 'ASC',
			//series meta style options
			'series_css_tougle' => 'default'
			);
			
			$this->settings = apply_filters('org_series_settings', $this->settings);
			update_option('org_series_options', $this->settings);
			return true;
		}
		if ( !defined('SERIES_QUERYVAR') )
			define('SERIES_QUERYVAR', $this->settings['series_custom_base'] );  // get/post variable name for querying series from WP
		return false;
	}
	
	function seriestoc_rewrite_rules( $the_rules ) {  
		$settings = $this->settings;
		if ( $settings['series_toc_url'] == $settings['series_custom_base'] ) {
			$series_toc_qv = $settings['series_toc_url'].'-toc';
		} else {
			$series_toc_qv = $settings['series_toc_url'];
		}
		$custom_base = $series_toc_qv;
		$cb_reg_ex = $settings['series_toc_url'].'\z';
		$cb_reg_ex_page = $settings['series_toc_url'].'/page/?([0-9]{1,})/?$';
		$new_rules = array( 
			$cb_reg_ex => 'index.php?'.$series_toc_qv.'=series_toc',
			$cb_reg_ex_page => 'index.php?'.$series_toc_qv.'=series_toc&paged=$matches[1]'
			);
		$the_rules = $new_rules + $the_rules;
		return $the_rules;		
	}
	
	function seriestoc_parsequery() {
		global $wp_query;
		$settings = $this->settings;
		if ( $settings['series_toc_url'] == $settings['series_custom_base'] ) {
			$series_toc_qv = $settings['series_toc_url'].'-toc';
		} else {
			$series_toc_qv = $settings['series_toc_url'];
		}
		$custom_base = $series_toc_qv;
		if (isset($wp_query->query_vars[$custom_base])) {
			$wp_query->is_single = false;
			$wp_query->is_category = false;
			$wp_query->is_tax = false;
			$wp_query->is_tag = false;
			$wp_query->is_search = false;
			$wp_query->is_front = false;
			$wp_query->is_posts_page = false;
			$wp_query->is_feed = false;
			$wp_query->is_month = false;
			$wp_query->is_author = false;
			$wp_query->is_page = false;
			$wp_query->is_archive = false;
			$wp_query->is_search = false;
			$wp_query->is_home = false;
			$wp_query->is_series = false;
			$wp_query->is_post = false;
			$wp_query->is_seriestoc = true;
			$wp_query->is_404 = false;
		} else {
			$wp_query->is_seriestoc = false;
		}
	}
	
	function orgSeries_add_queryvars($qvs) {  
		$settings = $this->settings;
		// first check to see if series_toc_url and series custom_base are the same.  If they are then we need to add a suffix to the queryvar for series_toc.
		if ( $settings['series_toc_url'] == $settings['series_custom_base'] ) {
			$series_toc_qv = $settings['series_toc_url'].'-toc';
		} else {
			$series_toc_qv = $settings['series_toc_url'];
		}
		$custom_base = $series_toc_qv;
		$qvs[] = $custom_base;
		return $qvs;
	}
	
	function seriestoc_title( $title ) {
		global $wp_query;
		$settings = $this->settings;
		
		if ( !isset($wp_query->is_seriestoc) || !$wp_query->is_seriestoc ) return $title;
		
		$seriestoc_title = $settings['series_toc_title'];
		if ( $seriestoc_title == '' ) $seriestoc_title = __('Series Table of Contents', 'organize-series');
		$title = $seriestoc_title  .  $title;
		return $title;
	}
		
	function orgSeries_toc_template() {
		global $wp_query;
		$settings = $this->settings;
		if ( isset($wp_query->is_seriestoc) && $wp_query->is_seriestoc ) {
				$template = locate_template(array('seriestoc.php')); //checks in child theme (if child theme) and then parent theme.  props Ricky (jeshyr)
			if (!$template)
				$template = WP_CONTENT_DIR . '/plugins/' . SERIES_DIR .'/seriestoc.php';
					
			/*function seriestoc_title( $title ) {
				$seriestoc_title = $settings['series_toc_title'];
				if ( $seriestoc_title == '' ) $seriestoc_title = __('Series Table of Contents', 'organize-series');
				$title = $seriestoc_title . ' &laquo; ' . $title;
				return $title;
			}*/
		
			//add_filter('wp_title', 'seriestoc_title');
			if ($template) {
				include($template);
				exit;
			}		
		}
	}
	
	//orgSeries dropdown nav js
	function series_dropdown_js() {
		global $wp_rewrite;
		$settings = $this->settings;
		$series_custom_base = $settings['series_custom_base'];
		if ( !($wp_rewrite->using_permalinks()) ) {
			?>
			<script type='text/javascript'><!--
			var seriesdropdown = document.getElementById("orgseries_dropdown");
			if (seriesdropdown) {
				function onSeriesChange() {
					if ( seriesdropdown.options[seriesdropdown.selectedIndex].value != ( 0 || -1 ) ) {
						location.href = "<?php echo get_option('home'); ?>/?<?php echo SERIES_QUERYVAR; ?>="+seriesdropdown.options[seriesdropdown.selectedIndex].value;
					}
				}
				seriesdropdown.onchange = onSeriesChange;
			}
			--></script>
				<?php
			} else {
				?>
				<script type='text/javascript'><!--
			var seriesdropdown = document.getElementById("orgseries_dropdown");
			if (seriesdropdown) { 
			 function onSeriesChange() {
					if ( seriesdropdown.options[seriesdropdown.selectedIndex].value != ( 0 || -1 ) ) {
						location.href = "<?php echo get_option('home'); ?>/<?php echo $series_custom_base; ?>/"+seriesdropdown.options[seriesdropdown.selectedIndex].value;
					}
				}
				seriesdropdown.onchange = onSeriesChange;
			}
			--></script>
			<?php
		}
	}
	
	//joins and wheres etc.
	
	function sort_series_page_join($join) {
		global $wp_query, $wpdb;
		if (!is_series() || ( is_series() && is_feed() ) || !empty($wp_query->request) || ( is_admin() && $wp_query->query_vars[SERIES_QUERYVAR] == 0 ) || $wp_query->is_search ) return $join;
		$os_join = " LEFT JOIN $wpdb->postmeta orgmeta ON($wpdb->posts.ID = orgmeta.post_id) ";
		$join .= apply_filters('orgseries_sort_series_page_join', $os_join);
		return $join;
	}

	function sort_series_page_where($where) {
		global $wp_query, $wpdb;
		if (!is_series() || ( is_series() && is_feed() ) || !empty($wp_query->request) || ( is_admin() && $wp_query->query_vars[SERIES_QUERYVAR] == 0 ) || $wp_query->is_search ) return $where;
		$part_key = SERIES_PART_KEY;
		$os_where = " AND orgmeta.meta_key = '$part_key' ";
		$where .= apply_filters('orgseries_sort_series_page_where', $os_where);
		return $where;
	}

	function sort_series_page_orderby($ordering) {
		global $wp_query, $wpdb;
		if (!is_series() || ( is_series() && is_feed() ) || !empty($wp_query->request) || ( is_admin() && $wp_query->query_vars[SERIES_QUERYVAR] == 0 ) || $wp_query->is_search ) return $ordering;
		$settings = $this->settings;
		$orderby = $settings['series_posts_orderby'];
		if ( $orderby == 'meta_value' )
			$orderby = 'orgmeta.'.$orderby . '+ 0';
		$order = $settings['series_posts_order'];
		if (!isset($orderby)) $orderby = "post_date";
		if (!isset($order)) $order = "DESC";
		$ordering = " $orderby $order ";
		return apply_filters('orgseries_sort_series_page_orderby', $ordering);	
	}
	
	// Add .css to header if enabled via options
	function orgSeries_header() {
		$plugin_path = SERIES_LOC;
		$css_style_type = isset($this->settings['series_css_tougle']) ? $this->settings['series_css_tougle'] : 'default';
		$text = '';
		if ($this->settings['custom_css']) {
			switch ( $css_style_type ) {
				case 'dark':
					wp_register_style('dark-style', plugins_url('orgSeriesDarkTheme.css', __FILE__));
					wp_enqueue_style('dark-style');
					break;
				case 'light':
					wp_register_style('light-style', plugins_url('orgSeriesLightTheme.css', __FILE__));
					wp_enqueue_style('light-style');
					break;
				default:
					$csspath = $plugin_path.'orgSeries.css';
					$text = '<link rel="stylesheet" href="' . $csspath . '" type="text/css" media="screen" />';
			}

		} else {
				$text = '';
		}
		echo $text;
	}
	
	//add series post-list box to a post in that series (on single.php view)
	function add_series_post_list_box($content) {

		if ( is_front_page() )
			return $content;

		if ($this->settings['auto_tag_toggle']) {
			if ( ( is_single() || is_page() ) && $postlist = wp_postlist_display() ) {
				$addcontent = $content;
				$content = str_replace('%postcontent%', $addcontent, $postlist);
			}
		}
		return $content;
	}
	
	//add series meta information to posts that belong to a series.
	function add_series_meta($content) {

                if ( is_front_page() )
                        return $content;

		if($this->settings['auto_tag_seriesmeta_toggle']) {
			if ($series_meta = wp_seriesmeta_write()) {
				$addcontent = $content;
				$content = str_replace('%postcontent%', $addcontent, $series_meta);
			}
		}
		return $content;
	}
	
	function orgseries_trim_excerpt($content) {
		remove_filter('the_content',array($this,'add_series_meta'));
		return $content;
	}
	
	function add_series_meta_excerpt($content) {
		if ( is_single() ) return;
		if($this->settings['auto_tag_seriesmeta_toggle']) {
			if ($series_meta = wp_seriesmeta_write(true)) {
				$addcontent = $content;
				$content = str_replace('%postcontent%', $addcontent, $series_meta);
			}
		}
		return $content;
	}	
	
	//add series navigation strip to posts that are part of a series (on single.php view)
	function series_nav_filter($content) {

                if ( is_front_page() )
                        return $content;

		if (is_single() || is_page() ) {
			if($this->settings['auto_tag_nav_toggle'] && $series_nav = wp_assemble_series_nav() ) {
				$addcontent = $content;
				$content = str_replace('%postcontent%', $addcontent, $series_nav);
			}
		}
		return $content;
	}
	
	//add series information to browser title info
	function add_series_wp_title( $title ) {
		$series = single_series_title('', false);
		$blogname = get_bloginfo('name');
		
		if ( !empty($series) ) {
			if ( !is_feed() )
				$title = __('Series: ','organize-series') . $series . ' &laquo; ';
			else
				$title = __('Posts from the series: ','organize-series') . $series . ' ('. get_bloginfo('url').')';
		}
		return $title;
	}
	
	//ADD in link to settings on manage plugins page.
	function AddPluginActionLink( $links, $file ) {
		static $this_plugin;
		
		if( empty($this_plugin) ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ) {
			$settings_link = '<a href="' . admin_url( 'options-general.php?page='.SERIES_DIR.'/orgSeries-options.php' ) . '">' . __('Settings', 'organize-series') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}
	
} //end of orgSeries class

$orgseries = new orgSeries();

} //end of class check
?>

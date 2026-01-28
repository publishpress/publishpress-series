<?php

/**
* This file contains the orgSeries class which initializes the plugin and provides global variables/methods for use in the rest of the plugin.
*
* @package Publishpress Series
* @since 2.2
*/

if ( !class_exists('orgSeries') ) {

class orgSeries {

	var $settings;
	var $version = ORG_SERIES_VERSION;

	//__constructor
	public function __construct() {
		$this->register_textdomain();

		//install OrgSeries
		add_action('activate_'.PPSERIES_BASE_NAME.'', array($this, 'org_series_install'));
        add_action( 'admin_init', array($this, 'pp_series_upgrade_version_upgrade'));

		//add support for capabilities tab in PublishPress Capabilities
		add_filter('cme_plugin_capabilities', array($this, 'pp_series_cme_plugin_capabilities'));

		//all other actions and filters...
		add_action('publishpress_series_after_init', array($this, 'add_settings'), 10);
		add_action('publishpress_series_pro_before_init', array($this, 'add_settings'), 10);
		add_action('init', array($this, 'register_taxonomy'),0);
		add_action('admin_enqueue_scripts', array($this, 'register_scripts'));
		add_action('init', array($this, 'maybe_fix_upgrade'));
		add_filter('rewrite_rules_array', array($this,'seriestoc_rewrite_rules'));
		//add_action('init', array($this, 'rewrite_rules'));
		add_action('parse_query', array( $this, 'seriestoc_parsequery') );
		add_filter('query_vars', array($this,'orgSeries_add_queryvars'));
		add_action('template_redirect', array($this,'orgSeries_toc_template')); //setsup the seriestoc url
        //series TOC shortcode
        add_shortcode('publishpress_series_toc', array($this, 'publishpress_series_toc_shortcode'));

		add_action('wp_enqueue_scripts', array($this, 'orgSeries_header'));
		add_action( 'wp_footer', array($this, 'series_dropdown_js'), 1 );
		add_filter('wp_title', array($this, 'seriestoc_title'));
		add_filter('pre_get_document_title', array($this, 'seriestoc_title'), 20);//https://github.com/publishpress/publishpress-series/issues/82

		//series post list box
		add_filter('the_content', array($this, 'add_series_post_list_box'), 12);

		//series meta strip
		add_filter('the_content', array($this, 'add_series_meta'), 12);
		add_filter('get_the_excerpt', array($this, 'orgseries_trim_excerpt'),1);
		add_filter('the_excerpt', array($this, 'add_series_meta_excerpt'));

		//joins, wheres, sortbys
		add_filter('posts_join_paged', array($this, 'sort_series_page_join'));
		add_filter('posts_where', array($this,'sort_series_page_where'));
		add_filter('posts_orderby', array($this,'sort_series_page_orderby'));

		//series post-navigation
		add_filter('the_content', array($this, 'series_nav_filter'));

		//broswer page title
		add_filter('wp_title', array($this, 'add_series_wp_title'));

		//settings link on plugin page
		add_filter('plugin_action_links', array($this, 'AddPluginActionLink'), 10, 2);

		// custom taxonomy template
		add_filter('taxonomy_template', array($this, 'series_load_tax_template'));

        //fix series content issue for Beaver Builder header and footer
        add_action('fl_theme_builder_before_render_footer', array($this, 'remove_series_content'));
        add_action('fl_theme_builder_before_render_header', array($this, 'remove_series_content'));

        add_action('fl_theme_builder_after_render_footer', array($this, 'add_series_content'));
        add_action('fl_theme_builder_after_render_header', array($this, 'add_series_content'));

		add_filter('orgseries_part_key', array(&$this, 'part_key'), 10, 2);
		add_filter('orgseries_pending_part_key', array(&$this, 'part_key'), 10, 2);

	}

	function part_key($part_key, $series_id) {
		$key = $part_key.'_'.$series_id;

		return $key;
	}

    /**
     * Fix series content issue for Beaver Builder header and footer
     *
     * https://github.com/publishpress/publishpress-series/issues/306
     */
    public function remove_series_content(){
        add_filter('pp_series_add_series_content', '__return_false');
    }

    /**
     * Fix series content issue for Beaver Builder header and footer
     *
     * https://github.com/publishpress/publishpress-series/issues/306
     */
    public function add_series_content(){
        add_filter('pp_series_add_series_content', '__return_true');
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

	}

    function pp_series_upgrade_version_upgrade() {
        pp_series_upgrade_function();
    }

    //add support for capabilities tab in PublishPress Capabilities
    function pp_series_cme_plugin_capabilities($plugin_caps){

        $plugin_caps['PublishPress Series'] = apply_filters('publishpress_series_capabilities', ['manage_series', 'manage_publishpress_series']);

        return $plugin_caps;
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

		return;
	}

	function register_scripts() {
        global $orgseries;
        $org_opt = $orgseries->settings;


		$url = WP_PLUGIN_URL.'/'.SERIES_DIR.'/js/';
		wp_register_script('inline-edit-series',$url.'inline-series.js', array('jquery'),ORG_SERIES_VERSION, TRUE);
		$add_series_js = current_user_can( 'manage_publishpress_series' ) ? 'series.js' : 'series-restricted.js';
		wp_register_script( 'ajaxseries', $url.$add_series_js, array('jquery', 'jquery-ui-core', 'jquery-color'), ORG_SERIES_VERSION, TRUE );
		wp_localize_script( 'ajaxseries', 'seriesL10n', array(
				'add' => esc_attr(__('Add New', 'organize-series')),
				'how' => __('Select "Not part of a series" to remove any series data from post', 'organize-series'),
				'addnonce' => wp_create_nonce('add-series-nonce'),
			));
		wp_register_script( 'orgseries_options', $url.'orgseries_options.js', array('jquery', 'thickbox'), ORG_SERIES_VERSION, TRUE);
	}

	function orgSeries_roles() {
		global $wp_roles;
		$roles = array('administrator', 'editor');
		$capability = 'manage_series';
		$capability_2 = 'manage_publishpress_series';

		foreach ($roles as $role) {
			$wp_roles->add_cap($role, $capability, true);
			$wp_roles->add_cap($role, $capability_2, true);
		}
		return true;
	}

	function register_textdomain() {

        $domain = 'organize-series';
		$mofile_custom = sprintf( '%s-%s.mo', $domain, get_locale() );
		$locations = array(
				trailingslashit( WP_LANG_DIR . '/' . $domain ),
				trailingslashit( WP_LANG_DIR . '/loco/plugins/'),
				trailingslashit( WP_LANG_DIR ),
				trailingslashit( PPSERIES_PATH . 'languages' ),
			);
		// Try custom locations in WP_LANG_DIR.
		foreach ( $locations as $location ) {
			if ( load_textdomain( 'organize-series', $location . $mofile_custom ) ) {
				return true;
			}
		}

	}

	function register_taxonomy() {
		$permalink_slug = isset($this->settings['series_custom_base']) ? $this->settings['series_custom_base'] : 'series';
		$taxonomy_name = ppseries_get_series_slug();
		$object_type = apply_filters('orgseries_posttype_support', array('post'));
		$capabilities = array(
			'manage_terms' => 'manage_publishpress_series',
			'edit_terms' => 'manage_publishpress_series',
			'delete_terms' => 'manage_publishpress_series',
			'assign_terms' => 'manage_publishpress_series'
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
			'menu_name' => __('Series', 'organize-series'),
			'not_found' => __('No series found', 'organize-series'),
			'back_to_items' => __('&larr; Go to Series', 'organize-series'),
			'view_item' => __('View Series', 'organize-series'),
			'no_terms' => __('No Series', 'organize-series'),
			'items_list' => __('Series List', 'organize-series'),
			'item_link' => __('Series Link', 'organize-series'),
			'item_link_description' => __('A link to a Series', 'organize-series')
			);
		$args = array(
			'update_count_callback' => '_os_update_post_term_count',
			'labels' => $labels,
            'show_in_rest' => true,
			'rewrite' => array( 'slug' => $permalink_slug, 'with_front' => true ),
			'show_ui' => true,
			'capabilities' => $capabilities,
			'query_var' => isset($this->settings['series_custom_base']) ? $this->settings['series_custom_base'] : 'series',
			);
		register_taxonomy( $taxonomy_name, $object_type, $args );
	}

	function add_settings($reset = false) {
		$url = parse_url(get_bloginfo('url'));
		if ( !($this->settings = get_option('org_series_options')) || $reset == true ) {
			$this->settings = array(
				//main settings
			'custom_css' => 1,
			'metabox_show_add_new' => 0,
			'metabox_show_post_title_in_widget' => 0,
			'kill_on_delete' => 0, //determines if all series information (including series-icon tables) will be deleted when the plugin is deleted using the delete link on the plugins page.
			'auto_tag_toggle' => 1, //sets the auto-tag insertions for the post-list box for posts that are part of series.
			'auto_tag_nav_toggle' => 1, //sets the auto-tag insertions for the series navigation strip.
			'auto_tag_seriesmeta_toggle' => 1, //sets the auto-tag insertions for the series-meta information in posts that are part of a series.
			'series_toc_url' => 'series-toc',
			'series_custom_base' => 'series',
			'series_perp_toc' => 3,
			'series_metabox_position' => 'default',
			'limit_series_meta_to_single' => 0,
			'series_navigation_box_position' => 'default',
			'series_toc_title' => __('All Series','organize-series'),
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
			'series_firstpost_nav_custom_text' => 'Series Home',
			//series_icon related settings
			'series_icon_width_series_page' => 200,
			'series_icon_width_post_page' =>100,
			'series_icon_width_latest_series' =>100,
			//series posts order options
			'series_posts_orderby' => 'meta_value',
			'series_posts_order' => 'ASC',
			//series meta style options
			'series_css_tougle' => 'default',
			//series meta style options
			'series_taxonomy_slug' => 'series',
			'metabox_series_order' => 'default',
			);

			$this->settings = apply_filters('org_series_settings', $this->settings);
			update_option('org_series_options', $this->settings);
			return true;
		}
		if ( is_array($this->settings) &&  !defined('SERIES_QUERYVAR') ){
			define('SERIES_QUERYVAR', isset($this->settings['series_custom_base']) ? $this->settings['series_custom_base'] : 'series' );  // get/post variable name for querying series from WP
        }

        if(is_array($this->settings) && !isset($this->settings['series_table_of_contents_box_template'])){// this need to move to upgrade function
            $this->settings['series_table_of_contents_box_template'] = '<div class="serieslist-box"><div class="imgset">%series_icon_linked%</div><div class="serieslist-content"><h2>%series_title_linked%</h2><p>%series_description%</p></div><hr style="clear: left; border: none" /></div>';
        }

        if(is_array($this->settings) && !isset($this->settings['series_taxonomy_slug'])){// this need to move to upgrade function
            $this->settings['series_taxonomy_slug'] = 'series';
        }

		return false;
	}

	function seriestoc_rewrite_rules( $the_rules ) {
		$settings = $this->settings;
		if ( isset($settings['series_custom_base']) && $settings['series_toc_url'] == $settings['series_custom_base'] ) {
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

	function seriestoc_parsequery( $wp_query ) {
	    if ( ! $wp_query instanceof WP_Query ) {
	        return;
        }
		$settings = $this->settings;

		if(!is_array($settings)){
			return;
		}
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
		if ( $wp_query instanceof WP_Query ) {
			$settings = $this->settings;

			if ( ! isset( $wp_query->is_seriestoc ) || ! $wp_query->is_seriestoc ) {
				return $title;
			}

			$seriestoc_title = $settings['series_toc_title'];
			if ( $seriestoc_title == '' ) {
				$seriestoc_title = __( 'Series Table of Contents', 'organize-series' );
			}
			$title = $seriestoc_title . $title;
		}
		return $title;
	}

	function orgSeries_toc_template() {
		global $wp_query;
		$settings = $this->settings;
		if ( $wp_query instanceof WP_Query ) {
			if ( isset( $wp_query->is_seriestoc ) && $wp_query->is_seriestoc ) {
				$template = locate_template( array( 'seriestoc.php' ) ); //checks in child theme (if child theme) and then parent theme.  props Ricky (jeshyr)
				if ( ! $template ) {
					$template = WP_CONTENT_DIR . '/plugins/' . SERIES_DIR . '/seriestoc.php';
				}

				/*function seriestoc_title( $title ) {
					$seriestoc_title = $settings['series_toc_title'];
					if ( $seriestoc_title == '' ) $seriestoc_title = __('Series Table of Contents', 'organize-series');
					$title = $seriestoc_title . ' &laquo; ' . $title;
					return $title;
				}*/

				//add_filter('wp_title', 'seriestoc_title');
				if ( $template ) {
					include( $template );
					exit;
				}
			}
		}
	}

    function publishpress_series_toc_shortcode($atts) {
        ob_start();
        ?>
        <div id="series-content">
            <?php wp_serieslist_display(); ?>
            <div class="stocpagination">
                <?php series_toc_paginate(); ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

	//orgSeries dropdown nav js
	function series_dropdown_js() {
		global $wp_rewrite;
		$settings = $this->settings;
		$series_custom_base = $settings['series_custom_base'];
		if ( $wp_rewrite instanceof WP_Rewrite && !($wp_rewrite->using_permalinks()) ) {
			?>
			<script type='text/javascript'><!--
			var seriesdropdown = document.getElementById("orgseries_dropdown");
			if (seriesdropdown) {
				function onSeriesChange() {
					if ( seriesdropdown.options[seriesdropdown.selectedIndex].value != ( 0 || -1 ) ) {
						location.href = "<?php echo esc_attr(get_option('home')); ?>/?<?php echo esc_attr(SERIES_QUERYVAR); ?>="+seriesdropdown.options[seriesdropdown.selectedIndex].value;
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
						location.href = "<?php echo esc_attr(get_option('home')); ?>/<?php echo esc_attr($series_custom_base); ?>/"+seriesdropdown.options[seriesdropdown.selectedIndex].value;
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
		if ( $wp_query instanceof WP_Query && $wpdb instanceof wpdb ) {
			if ( ! is_series() || ( is_series() && is_feed() ) || ! empty( $wp_query->request ) || ( is_admin() && $wp_query->query_vars[ SERIES_QUERYVAR ] == 0 ) || $wp_query->is_search ) {
				return $join;
			}
			$os_join = " LEFT JOIN $wpdb->postmeta orgmeta ON($wpdb->posts.ID = orgmeta.post_id) ";
			$join .= apply_filters( 'orgseries_sort_series_page_join', $os_join );
		}
		return $join;
	}

	function sort_series_page_where($where) {
		global $wp_query, $wpdb;
		if ( $wp_query instanceof WP_Query ) {
			if ( ! is_series() || ( is_series() && is_feed() ) || ! empty( $wp_query->request ) || ( is_admin() && $wp_query->query_vars[ SERIES_QUERYVAR ] == 0 ) || $wp_query->is_search ) {
				return $where;
			}

			$series = get_query_var(SERIES_QUERYVAR);
			$ser_id = is_numeric($series) ? (int) $series : get_series_ID($series);
			$part_key = SERIES_PART_KEY.'_'.$ser_id;
			$os_where = $wpdb->prepare(" AND orgmeta.meta_key = %s ", $part_key);

			$where .= apply_filters( 'orgseries_sort_series_page_where', $os_where );
		}
		return $where;
	}

	function sort_series_page_orderby($ordering) {
		global $wp_query;
		if ( $wp_query instanceof WP_Query ) {
			if ( ! is_series() || ( is_series() && is_feed() ) || ! empty( $wp_query->request ) || ( is_admin() && $wp_query->query_vars[ SERIES_QUERYVAR ] == 0 ) || $wp_query->is_search ) {
				return $ordering;
			}
			$settings = $this->settings;
			$orderby  = $settings['series_posts_orderby'];
			if ( $orderby == 'meta_value' ) {
				$orderby = 'orgmeta.' . $orderby . '+ 0';
			}
			$order = $settings['series_posts_order'];
			if ( ! isset( $orderby ) ) {
				$orderby = "post_date";
			}
			if ( ! isset( $order ) ) {
				$order = "DESC";
			}
			$ordering = " $orderby $order ";
		}
		return apply_filters('orgseries_sort_series_page_orderby', $ordering);
	}

	// Add CSS to header if enabled via options and CSS design if overview page is different to default
	function orgSeries_header() {
		// Only load series CSS when needed
		if (!$this->should_load_series_css()) {
			return;
		}

		$plugin_path = SERIES_LOC;
		$css_style_type = isset($this->settings['series_css_tougle']) ? $this->settings['series_css_tougle'] : 'default';
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
				case 'box':
					wp_register_style('orgseries-box-css', plugins_url('orgSeriesBoxTheme.css', __FILE__));
					wp_enqueue_style('orgseries-box-css');
					break;
				default:
					wp_register_style( 'orgseries-default-css', $plugin_path . 'orgSeries.css' );
					wp_enqueue_style( 'orgseries-default-css');
					break;
			}

		}

		if (isset($this->settings['series_overview_page_layout']) && $this->settings['series_overview_page_layout'] !== 'default') {
			wp_enqueue_style(
				'pps-series-overview-style',
				plugins_url('css/series-overview.css', __FILE__),
				'',
				ORG_SERIES_VERSION
			);
		}
	}

	/**
	 * Check if series CSS should be loaded on the current page.
	 *
	 * @return bool
	 */
	private function should_load_series_css() {
		// Always load on series archive pages
		$taxonomy_slug = get_option('pp_series_taxonomy_slug', 'series');
		if (is_tax($taxonomy_slug)) {
			return true;
		}

		// Load on singular posts/pages that belong to a series
		if (is_singular()) {
			$post_id = get_queried_object_id();
			if ($post_id) {
				$series = wp_get_post_series($post_id);
				if (!empty($series)) {
					return true;
				}
			}
		}

		return false;
	}

	//add series post-list box to a post in that series (on single.php view)
	public function add_series_post_list_box($content) {

        /**
        * Filter whether to add series content
        */
        $add_series_content = apply_filters('pp_series_add_series_content', true);

		if ($add_series_content && $this->settings['auto_tag_toggle']) {
			if ( ( is_single() || is_page() ) && $postlist = wp_postlist_display() ) {
				// Get position from the selected post list box
				$position = 'top'; // fallback value
				
				$post_list_box_id = isset($this->settings['series_post_list_box_selection']) ? $this->settings['series_post_list_box_selection'] : '';
				
				if (empty($post_list_box_id)) {
					$post_list_box_id = PPS_Post_List_Box_Utilities::get_default_post_list_box_id();
				}
				
				if (!empty($post_list_box_id) && class_exists('PPS_Post_List_Box_Fields')) {
					$editor_data = PPS_Post_List_Box_Fields::get_post_list_box_layout_meta_values($post_list_box_id);
					$position = isset($editor_data['post_list_position']) ? $editor_data['post_list_position'] : 'top';
				}
				
				if($position === 'top'){
					$postlist = str_replace('%postcontent%', '', $postlist);
					$content = $postlist.$content;
				}elseif($position === 'bottom'){
					$postlist = str_replace('%postcontent%', '', $postlist);
					$content = $content.$postlist;
				}
			}
		}
		return $content;
	}

	//add series meta information to posts that belong to a series.
	public function add_series_meta($content) {

        /**
        * Filter whether to add series content
        */
        $add_series_content = apply_filters('pp_series_add_series_content', true);

		$layout_settings = null;
		if (
			class_exists('PPS_Series_Post_Details_Utilities')
			&& isset($this->settings['series_post_details_selection'])
			&& (int) $this->settings['series_post_details_selection'] > 0
		) {
			$layout_settings = PPS_Series_Post_Details_Utilities::get_selected_layout_settings($this->settings);
		}

		$limit_to_single = isset($this->settings['limit_series_meta_to_single']) ? (int) $this->settings['limit_series_meta_to_single'] : 0;
		if ($layout_settings && isset($layout_settings['limit_to_single'])) {
			$limit_to_single = (int) $layout_settings['limit_to_single'];
		}

        if ($limit_to_single > 0 && !is_single()) {
            return $content;
        }

		if($add_series_content && $this->settings['auto_tag_seriesmeta_toggle']) {
			if ($series_meta = wp_seriesmeta_write()) {
				$position = isset($this->settings['series_metabox_position']) ? $this->settings['series_metabox_position'] : 'default';
				if ($layout_settings && isset($layout_settings['metabox_position'])) {
					$position = $layout_settings['metabox_position'];
				}
				if($position === 'top'){
					$series_meta = str_replace('%postcontent%', '', $series_meta);
					$content = $series_meta.$content;
				}elseif($position === 'bottom'){
					$series_meta = str_replace('%postcontent%', '', $series_meta);
					$content = $content.$series_meta;
				}else{
					$addcontent = $content;
					$content = str_replace('%postcontent%', $addcontent, $series_meta);
				}
			}
		}
		return $content;
	}

	function orgseries_trim_excerpt($content) {
        if (!is_single()) {
            remove_filter('the_content', array($this,'add_series_meta'), 12);
        }
		return $content;
	}

	//add series meta information to excerpts for posts that belong to a series.
	public function add_series_meta_excerpt($content) {
		/**
		 * Filter whether to add series content to excerpts
		 */
		$add_series_content = apply_filters('pp_series_add_series_content', true);

		$layout_settings = null;
		if (
			class_exists('PPS_Series_Post_Details_Utilities')
			&& isset($this->settings['series_post_details_selection'])
			&& (int) $this->settings['series_post_details_selection'] > 0
		) {
			$layout_settings = PPS_Series_Post_Details_Utilities::get_selected_layout_settings($this->settings);
		}

		$limit_to_single = isset($this->settings['limit_series_meta_to_single']) ? (int) $this->settings['limit_series_meta_to_single'] : 0;
		if ($layout_settings && isset($layout_settings['limit_to_single'])) {
			$limit_to_single = (int) $layout_settings['limit_to_single'];
		}

		if ($limit_to_single > 0 && !is_single()) {
			return $content;
		}

		if ($add_series_content && $this->settings['auto_tag_seriesmeta_toggle']) {
			if ($series_meta = wp_seriesmeta_write(true)) {
				$position = isset($this->settings['series_metabox_position']) ? $this->settings['series_metabox_position'] : 'default';
				if ($layout_settings && isset($layout_settings['metabox_position'])) {
					$position = $layout_settings['metabox_position'];
				}
				if ($position === 'top') {
					$series_meta = str_replace('%postcontent%', '', $series_meta);
					$content = $series_meta . $content;
				} elseif ($position === 'bottom') {
					$series_meta = str_replace('%postcontent%', '', $series_meta);
					$content = $content . $series_meta;
				} else {
					$addcontent = $content;
					$content = str_replace('%postcontent%', $addcontent, $series_meta);
				}
			}
		}
		return $content;
	}

	//add series navigation strip to posts that are part of a series (on single.php view)
	public function series_nav_filter($content) {
		if (is_single() || is_page() ) {

            /**
            * Filter whether to add series content
            */
            $add_series_content = apply_filters('pp_series_add_series_content', true);

			if($add_series_content && $this->settings['auto_tag_nav_toggle'] && $series_nav = wp_assemble_series_nav() ) {
				$position = isset($this->settings['series_navigation_box_position']) ? $this->settings['series_navigation_box_position'] : 'default';
				if($position === 'top'){
					$series_nav = str_replace('%postcontent%', '', $series_nav);
					$content = $series_nav.$content;
				}elseif($position === 'bottom'){
					$series_nav = str_replace('%postcontent%', '', $series_nav);
					$content = $content.$series_nav;
				}else{
					$addcontent = $content;
					$content = str_replace('%postcontent%', $addcontent, $series_nav);
				}
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
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.SERIES_DIR.'/orgSeries-options.php' ) . '">' . __('Settings', 'organize-series') . '</a>';
			array_unshift( $links, $settings_link );
		}

		return $links;
	}

	function series_load_tax_template($tax_template) {
		if (isset($this->settings['series_overview_page_layout']) && $this->settings['series_overview_page_layout'] !== 'default') {

			$series_slug = ppseries_get_series_slug();
			if (is_tax('series')) {
				// Override taxonomy-series.php in child theme by pasting the file in root child theme folder
				$theme_template = locate_template( array('taxonomy-' . $series_slug . '.php') );
				if ( !$theme_template ) {
	            	$tax_template = dirname( __FILE__ ) . '/inc/templates/taxonomy-' . $series_slug . '.php';
	        	}
			}
			$this->series_load_theme_css();
		}

		return $tax_template;
	}

	/*
	 * CSS to fix core themes styling for Taxonomy Series template
	 *
	 */
	function series_load_theme_css(){
		$themes = [
			'twentynineteen',
			'twentytwenty',
			'twentytwentyone'
		];
		$ctheme = wp_get_theme();
		$ptheme = wp_get_theme()->parent();

		if(
			in_array($ctheme->get('TextDomain'), $themes)
			|| (!empty($ptheme) && in_array($ptheme->get('TextDomain'), $themes))
		){
			if($ctheme->get('TextDomain')){
				$textdomain = $ctheme->get('TextDomain');
			} else {
				$textdomain = $ptheme->get('TextDomain'); // Parent text domain
			}
			wp_enqueue_style(
				'orgseries-' . $textdomain,
				plugins_url('css/themes/' . $textdomain . '.css', __FILE__),
				array(),
				ORG_SERIES_VERSION
			);
		}
	}

} //end of orgSeries class

$orgseries = new orgSeries();

} //end of class check
?>

<?php
/** 
 * This file contains all the code for the orgSeries widgets.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.0
 **/
 
 #########WIDGETS####################	
function orgSeries_widget_seriestoc_init() {
	//Check for widget API
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
			
	//Save options and print widget's config form.
	function orgSeries_widget_control() {
		global $org_domain;
		$options = $newoptions = (array) get_option('orgSeries_widget');
		$defaults = array(
			'orgSeries-widget-title' => __('Series', $org_domain),
			'list-type' => 'list',
			'show-count' => 1,
			'hide-empty' => 1,
			'postlistdisplay-toggle' => 1);
			
			foreach ( $defaults as $key => $value )
			if ( !isset($newoptions[$key]) )
				$newoptions[$key] = $defaults[$key];
			
		if ( $_POST['orgSeries-widget-submit'] ) {
			$newoptions['orgSeries-widget-title'] = trim(stripslashes($_POST['orgSeries-widget-title']));
			$newoptions['list-type'] = trim(stripslashes($_POST['list-type']));
			$newoptions['show-count'] = (int) $_POST['show-count'];
			$newoptions['hide-empty'] = (int) $_POST['hide-empty'];
			$newoptions['postlistdisplay-toggle'] = (int) $_POST['postlistdisplay-toggle'];
		}
		
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('orgSeries_widget', $options);
		}
	?>
		<div style="text-align:right">
		<label for="orgSeries-widget-title" style="line-height:35px; display:block;"><?php _e('Widget title: ', $org_domain); ?><input type="text" id="orgSeries-widget-title" name="orgSeries-widget-title" value="<?php echo htmlspecialchars($options['orgSeries-widget-title']); ?>" /></label>
		<p><small><?php _e('Series list options:', $org_domain); ?></small></p>
		<label for="list-type" style="line-height:35px; display: block;"><?php _e(' Dropdown: ', $org_domain); ?><input type="radio" id="list-type" name="list-type" value="dropdown"<?php checked('dropdown', $options['list-type']); ?> /></label>
		<label for="list-type2" style="line-height:35px; display: block;"> <?php _e('List: ', $org_domain); ?><input type="radio" id="list-type2" name="list-type" value="list"<?php checked('list', $options['list-type']); ?> /></label>
		<label for="show-count" style="line-height:35px; display: block;"><?php _e('Show post count?', $org_domain); ?> <input type="checkbox" id="show-count" name="show-count" value="1" <?php checked('1' , $options['show-count']); ?> /></label>
		<label for="hide-empty" style="line-height:35px; display: block;"><?php _e('Hide empty series?', $org_domain); ?> <input type="checkbox" id="hide-empty" name="hide-empty" value="1" <?php checked('1' , $options['hide-empty']); ?> /></label>
		<label for="postlistdisplay-toggle" style="line-height:35px; display:block;"><?php _e('Post List toggle:', $org_domain); ?> <input type="checkbox" name="postlistdisplay-toggle" id="postlistdisplay-toggle" value="1" <?php checked('1', $options['postlistdisplay-toggle']); ?> /><br /><small><?php _e('(use to select if a list of other posts in the series will show on post-pages that are part of a series)', $org_domain); ?></small></label>
		<input type="hidden" name="orgSeries-widget-submit" id="orgSeries-widget-submit" value="1" />
		</div>
	<?php
	}
	
	//This prints the widget
	function orgSeries_widget($args) {
		extract($args);
		global $wp_query, $org_domain;
		
		$options = (array) get_option('orgSeries_widget');
		$c = $options['show-count'] ? '1' : '0';
		$e = $options['hide-empty'] ? '1' : '0';
		$showpostlist = $options['postlistdisplay-toggle'] ? '1' : '0';
			
		?>
		<?php echo $before_widget; ?>
			<?php echo $before_title.$options['orgSeries-widget-title'].$after_title; ?>
			<?php
				$series_args = "orderby=name&show_count={$c}&hide_empty={$e}&echo=1";
				if ( $options['list-type'] == 'list' ) {
						echo '<ul>';
						wp_list_series($series_args . '&title_li=');
						echo '</ul>';
					}
					if ( $options['list-type'] == 'dropdown' ) {
						wp_dropdown_series($series_args . '&show_option_none= ' . __('Select Series', $org_domain));
				}
					if ( ( $wp_query->is_single ) && $showpostlist && ( $series = get_the_series() ) ) {
						echo '<br /><br /><h3>' . __('Other posts belonging to this series', $org_domain) . '</h3>';
						echo '<ul>' . get_series_posts('', 'widget') .  '</ul>';
					}
				
?>
		<?php echo $after_widget; ?>
	<?php
	}
	
	//Get the sidebar to load up the widget and it's control
	register_sidebar_widget('Series Widget', 'orgSeries_widget'); 
	register_widget_control('Series Widget', 'orgSeries_widget_control', 600, 500);

}

function orgSeries_widget_latest_series_init() {
	//check for widget API
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;
			
	//Save options and print widget's config form.
	function orgSeries_latest_series_widget_control() {
		global $org_domain; 
		$options = $newoptions = (array) get_option('orgSeries_latest_series_widget');
		$defaults = array(
			'latest_series_widget_title' => __('Most Recent Series',$org_domain),
			'orderby' => 'post_modified',
			'number' => '5',
			'order' => 'ASC',
			'hide_empty' => TRUE);
		
		foreach ( $defaults as $key => $value )
			if ( !isset($newoptions[$key]) )
				$newoptions[$key] = $defaults[$key];
			
		if ( $_POST['orgSeries_latest_series_widget_submit'] ) {
			$newoptions['latest_series_widget_title'] = trim(stripslashes($_POST['latest_series_widget_title']));
			$newoptions['orderby'] = trim(stripslashes($_POST['orderby']));
			$newoptions['number'] = (int) $_POST['number'];
			$newoptions['order'] = trim(stripslashes($_POST['order']));
			$newoptions['hide_empty'] = $_POST['hide_empty']; 
		}
		
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('orgSeries_latest_series_widget', $options);
		}
		$seriesoptionsurl = get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=' . SERIES_DIR . '/orgSeries-options.php';
	?>
		<div style="text-align:right">
			<label for="latest_series_widget_title" style="line-height:35px; display:block;"> <?php _e('Widget title:', $org_domain); ?> <input type="text" id="latest_series_widget_title" name="latest_series_widget_title" value="<?php echo $options['latest_series_widget_title']; ?>" /></label>
			<p><?php printf(__('The layout and content of this widget can be adjusted via the latest-series-template on the <a href="%s">Series Options</a> page.', $org_domain), $seriesoptionsurl); ?> </p>
			<p><small><?php _e('Options for listing the Latest Series', $org_domain); ?></small></p>
			<label for="orderby" style="line-height:45px; display: block;"><?php _e('Orderby:', $org_domain); ?>
			<select name="orderby" id="orderby">
				<option class="post_modified" <?php if ( $options['orderby'] == 'post_modified' ) { ?>selected="selected" <?php } ?>value="post_modified"><?php _e('Post Modified', $org_domain); ?></option>
				<option class="count" <?php if ( $options['orderby'] == 'count' ) { ?>selected="selected" <?php } ?>value="count"><?php _e('Number of Posts in Series', $org_domain); ?></option>
				<option class="name" <?php if ( $options['orderby'] == 'name' ) { ?>selected="selected" <?php } ?>value="name"><?php _e('Name of Series', $org_domain); ?></option>
				<option class="slug" <?php if ( $options['orderby'] == 'slug' ) { ?>selected="selected" <?php } ?>value="slug"><?php _e('Series Slug', $org_domain); ?></option>
				<option class="term_id" <?php if ( $options['orderby'] == 'term_id' ) { ?>selected="selected" <?php } ?>value="term_id"><?php _e('Series ID', $org_domain); ?></option>
			</select>
			</label>
			<label for="number" style="line-height: 45px; display: block;"><?php _e('Number of series to display:', $org_domain); ?>
			<input type="text" id="number" name="number" value="<?php echo htmlspecialchars($options['number']); ?>" />
			</label>
			<label for="order" style="line-height: 45px; display: block;"><?php _e('Display Order:', $org_domain); ?>
			<?php _e('ASC:', $org_domain); ?> <input type="radio" id="ASC" name="order" value="ASC" <?php checked('ASC', $options['order']); ?>/>
			<?php _e('DESC:', $org_domain); ?> <input type="radio" id="DESC" name="order" value="DESC" <?php checked('DESC', $options['order']); ?>/>
			</label>
			<?php /*<label for="hide_empty" style="line-height: 45px; display: block;"><?php _e('Don\'t show series with no posts?', $org_domain); ?>
			<input type="checkbox" name="hide_empty" id="hide_empty" value="true" <?php checked('true', $options['hide_empty']); ?> />
			</label> //FOR ADDING LATER - HIDE_EMPTY IS NOT WORKING AS EXPECTED */ ?>
			<input type="hidden" name="orgSeries_latest_series_widget_submit" id= "orgSeries_latest_series_widget_submit" value="1" />
		</div>
	<?php
	}
	
	//This prints the widget
	function orgSeries_latest_series_widget($args) {
		extract ($args);
		$options = (array) get_option('orgSeries_latest_series_widget');
		$hide_empty = $options['hide_empty'] ? 'true' : 'false';
		$orderby = $options['orderby'];
		$number = $options['number'];
		$order = $options['order'];
		$args = 'hide_empty='.$hide_empty.'&orderby='.$orderby.'&number='.$number.'&order='.$order;
		
		echo $before_widget;
		echo $before_title.$options['latest_series_widget_title'].$after_title;
		latest_series(true,$args);
		echo $after_widget;
	}
	
	register_sidebar_widget('Latest Series', 'orgSeries_latest_series_widget');
	register_widget_control('Latest Series', 'orgSeries_latest_series_widget_control');

}
		
		
//Delay plugin execution so widget has a chance to load first...
add_action('plugins_loaded', 'orgSeries_widget_seriestoc_init'); 
add_action('plugins_loaded', 'orgSeries_widget_latest_series_init');
?>
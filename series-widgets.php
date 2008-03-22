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
		$options = $newoptions = (array) get_option('orgSeries_widget');
		$defaults = array(
			'orgSeries-widget-title' => 'Series',
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
		<label for="orgSeries-widget-title" style="line-height:35px; display:block;">Widget title: <input type="text" id="orgSeries-widget-title" name="orgSeries-widget-title" value="<?php echo htmlspecialchars($options['orgSeries-widget-title']); ?>" /></label>
		<p><small>Series list options:</small></p>
		<label for="list-type" style="line-height:35px; display: block;"> Dropdown: <input type="radio" id="list-type" name="list-type" value="dropdown"<?php checked('dropdown', $options['list-type']); ?> /></label>
		<label for="list-type2" style="line-height:35px; display: block;"> List: <input type="radio" id="list-type2" name="list-type" value="list"<?php checked('list', $options['list-type']); ?> /></label>
		<label for="show-count" style="line-height:35px; display: block;">Show post count? <input type="checkbox" id="show-count" name="show-count" value="1" <?php checked('1' , $options['show-count']); ?> /></label>
		<label for="hide-empty" style="line-height:35px; display: block;">Hide empty series? <input type="checkbox" id="hide-empty" name="hide-empty" value="1" <?php checked('1' , $options['hide-empty']); ?> /></label>
		<label for="postlistdisplay-toggle" style="line-height:35px; display:block;">Post List toggle: <input type="checkbox" name="postlistdisplay-toggle" id="postlistdisplay-toggle" value="1" <?php checked('1', $options['postlistdisplay-toggle']); ?> /><br /><small>(use to select if a list of other posts in the series will show on post-pages that are part of a series)</small></label>
		<input type="hidden" name="orgSeries-widget-submit" id="orgSeries-widget-submit" value="1" />
		</div>
	<?php
	}
	
	//This prints the widget
	function orgSeries_widget($args) {
		extract($args);
		
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
						wp_dropdown_series($series_args . '&show_option_none= ' . __('Select Series'));
?>

<script lang='javascript'><!--
var seriesdropdown = document.getElementById("series");
    function onSeriesChange() {
		if ( seriesdropdown.options[seriesdropdown.selectedIndex].value > 0 ) {
			location.href = "<?php echo get_option('home'); ?>/?series="+seriesdropdown.options[seriesdropdown.selectedIndex].value;
		}
    }
    seriesdropdown.onchange = onSeriesChange;
--></script>

<?php
				}
					if ( is_single() && $showpostlist ) {
						echo '<br /><br /><h3>Other posts belonging to this series</h3>';
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
		$options = $newoptions = (array) get_option('orgSeries_latest_series_widget');
		$defaults = array(
			'latest_series_widget_title' => 'Most&nbsp;Recent&nbsp;Series');
		
		foreach ( $defaults as $key => $value )
			if ( !isset($newoptions[$key]) )
				$newoptions[$key] = $defaults[$key];
			
		if ( $_POST['orgSeries_latest_series_widget_submit'] ) {
			$newoptions['latest_series_widget_title'] = trim(stripslashes($_POST['latest_series_widget_title']));
		}
		
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('orgSeries_latest_series_widget', $options);
		}
	?>
		<div style="text-align:right">
			<label for="latest_series_widget_title" style="line-height:35px; display:block;"> Widget title: <input type="text" id="latest_series_widget_title" name="latest_series_widget_title" value="<?php echo htmlspecialchars($options['latest_series_widget_title']); ?>" /></label>
			<p>The layout and content of this widget can be adjusted via the latest-series-template on the <a href="<?php bloginfo('wpurl'); ?>/wp-admin/options-general.php?page=<?php SERIES_DIR ?>/orgSeries-options.php">Series Options</a> page.</p>
			<input type="hidden" name="orgSeries_latest_series_widget_submit" id= "orgSeries_latest_series_widget_submit" value="1" />
		</div>
	<?php
	}
	
	//This prints the widget
	function orgSeries_latest_series_widget($args) {
		extract ($args);
		$options = (array) get_option('orgSeries_latest_series_widget');
		echo $before_widget;
		echo $before_title.$options['latest_series_widget_title'].$after_title;
		latest_series();
		echo $after_widget;
	}
	
	register_sidebar_widget('Latest Series', 'orgSeries_latest_series_widget');
	register_widget_control('Latest Series', 'orgSeries_latest_series_widget_control');

}
		
		
//Delay plugin execution so widget has a chance to load first...
add_action('plugins_loaded', 'orgSeries_widget_seriestoc_init'); 
add_action('plugins_loaded', 'orgSeries_widget_latest_series_init');
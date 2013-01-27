<?php
/**
 * This file contains all the code for the orgSeries widgets.
 *
 * @package Organize Series WordPress Plugin
 * @since 2.2
**/
class orgSeries_widget_latestseries extends WP_Widget {
	
	function orgSeries_widget_latestseries() {
		global $orgseries;
		$widget_ops = array('description' => __('Use this to control the output of the latest series widget', 'organize-series'));
		$this->WP_Widget('latestseries', __('Latest Series', 'organize-series'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		global $orgseries;
		extract( $args, EXTR_SKIP );
		
		$title = isset($instance['title']) ? $instance['title'] : __('Most Recent Series', 'organize-series');
		$orderby = isset($instance['orderby']) ? $instance['orderby'] : 'name';
		$number = isset($instance['number']) ? $instance['number'] : '5';
		$order = isset($instance['order']) ? $instance['order'] : 'ASC';
		$hide_empty = isset($instance['hide_empty']) ? $instance['hide_empty'] : 0;
		
		$series_args = $args = apply_filters('widget_latestseries_args', array('hide_empty' => $hide_empty, 'orderby' => $orderby, 'number' => $number, 'order' => $order));
		
		$out = latest_series(false,$args);
		
		if ( !empty( $out ) ) {
			echo $before_widget;
			
			if ( $title )
				echo $before_title . $title . $after_title;
			?>
			<ul>
				<?php echo $out; ?>
			</ul>
			<?php
				echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['hide_empty'] = $new_instance['hide_empty'];
		$instance['orderby'] = $new_instance['orderby'];
		$instance['number'] = strip_tags($new_instance['number']);
		$instance['order'] = $new_instance['order'];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $orgseries;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array('title'=> '', 'hide_empty' => 0, 'orderby' => 'name', 'number' => '5', 'order' => 'ASC') );
		$title = esc_attr( $instance['title'] );
		$hide_empty = $instance['hide_empty']; 
		$seriesoptionsurl = menu_page_url( 'manage_series', false );
;
		$number = (int) $instance['number'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'organize-series'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<?php printf(__('The layout and content of this widget can be adjusted via the latest-series-template on the <a href="%s">Series Options</a> page.', 'organize-series'), $seriesoptionsurl); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Don\'t show series with no posts?', 'organize-series'); ?></label>
			<input class="checkbox" type="checkbox" <?php checked($hide_empty, true); ?> value = "1" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" />
			<br />
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Order by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
				<option value="count"<?php selected( $instance['orderby'], 'count' ); ?>><?php _e('Number of posts in Series', 'organize-series'); ?></option>
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e('Name of Series', 'organize-series'); ?></option>
				<option value="slug"<?php selected( $instance['orderby'], 'slug' ); ?>><?php _e('Series Slug', 'organize-series'); ?></option>
				<option value="term_id"<?php selected( $instance['orderby'], 'term_id' ); ?>><?php _e('When Series was Created', 'organize-series'); ?></option>
				<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php _e('Random', 'organize-series'); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of series to display:', 'organize-series'); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $number; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Display Order: ', 'organize-series'); ?></label>
			<?php _e('ASC: ', 'organize-series'); ?><input class="radio" id="<?php echo $this->get_field_id('order'); ?>-asc" name="<?php echo $this->get_field_name('order'); ?>" type="radio" value="ASC" <?php checked('ASC', $instance['order']); ?>/>
			<?php _e('DESC: ', 'organize-series'); ?><input class="radio" id="<?php echo $this->get_field_id('order'); ?>-desc" name="<?php echo $this->get_field_name('order'); ?>" type="radio" value="DESC" <?php checked('DESC', $instance['order']); ?>/>
		</p>
	<?php
	}
}

class orgSeries_widget_seriestoc extends WP_Widget {
	
	function orgSeries_widget_seriestoc() {
		global $orgseries;
		$widget_ops = array('description' => __('Use this to display the Series Table of contents', 'organize-series'));
		$this->WP_WIDGET('seriestoc', __('Series TOC', 'organize-series'), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		global $orgseries, $wp_query;
		extract( $args, EXTR_SKIP );
		$c = $instance['show-count'] ? '1' : '0';
		$e = $instance['hide-empty'] ? '1' : '0';
		$showpostlist = $instance['postlistdisplay-toggle'] ? '1' : '0';
		$showseriestoc = $instance['seriestocdisplay-toggle'] ? '1' : '0';
		$series_args = $args = apply_filters('widget_seriestoc_args', array('orderby' => 'name', 'show_count' => $c, 'hide_empty' => $e, 'echo' => false, 'name' => 'orgseries_dropdown'));
		$title = $instance['title'];
		if (isset($instance['serieswidget-title']) && count($instance['serieswidget-title']) > 0)
			$widget_title = $instance['serieswidget-title'];
		else
			$widget_title = false;
		$out = '';
		if ( $showseriestoc ) {
			if ( $instance['list-type'] == 'list' ) {
				$out .= '<ul>';
				$series_args['title_li'] = '';
				$out .= wp_list_series($series_args);
				$out .= '</ul>';
			} elseif ( $instance['list-type'] == 'dropdown' ) {
				$series_args['show_option_all'] = __('Select Series', 'organize-series');
				$out = wp_dropdown_series( $series_args );
			}
		}
		
		if ( $showpostlist ) {
			if ( ( $wp_query->is_single ) && $showpostlist && $series = get_the_series() ) {
				if ( $showseriestoc ) $out .= '<br /><br />';
				$out .= get_series_posts('','widget', false, $widget_title);	
				}
		}
		
		if ( !empty($out) ) {
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo $out;
			echo $after_widget;
		}
	}
	
	function update( $new_instance, $old_instance ) {
		global $orgseries;
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['serieswidget-title'] = strip_tags($new_instance['serieswidget-title']);
		$instance['list-type'] = strip_tags($new_instance['list-type']);
		$instance['show-count'] = (int) $new_instance['show-count'];
		$instance['hide-empty'] = (int) $new_instance['hide-empty'];
		$instance['postlistdisplay-toggle'] = (int) $new_instance['postlistdisplay-toggle'];
		$instance['seriestocdisplay-toggle'] = (int) $new_instance['seriestocdisplay-toggle'];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $orgseries;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title' => __('Series', 'organize-series'),
			'serieswidget-title' => __('Other posts in series:', 'organize-series'),
			'list-type' => 'list',
			'show-count' => 1,
			'hide-empty' => 1,
			'postlistdisplay-toggle' => 1,
			'seriestocdisplay-toggle' => 1
		));
		$title = esc_attr( $instance['title'] );
		$widget_title =  esc_attr($instance['serieswidget-title']);
		$list_type = $instance['list-type'];
		$show_count = $instance['show-count'];
		$hide_empty = $instance['hide-empty'];
		$postlistdisplay_toggle = $instance['postlistdisplay-toggle'];
		$seriestocdisplay_toggle = $instance['seriestocdisplay-toggle'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'organize-series'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<?php _e('Series list options:', 'organize-series'); ?><br />
			<label for="<?php echo $this->get_field_id('list-type').'-dropdown'; ?>"><?php _e(' Dropdown: ', 'organize-series'); ?><input type="radio" id="<?php echo $this->get_field_id('list-type').'-dropdown'; ?>" name="<?php echo $this->get_field_name('list-type'); ?>" value="dropdown" <?php checked('dropdown', $list_type); ?> /></label>
			<label for="<?php echo $this->get_field_id('list-type').'-list'; ?>"><?php _e(' List: ', 'organize-series'); ?><input type="radio" id="<?php echo $this->get_field_id('list-type').'-list'; ?>" name="<?php echo $this->get_field_name('list-type'); ?>" value="list" <?php checked('list', $list_type); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show-count'); ?>"><?php _e('Show post count?', 'organize-series'); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('show-count'); ?>" name="<?php echo $this->get_field_name('show-count'); ?>" value="1" <?php checked('1', $show_count); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('hide-empty'); ?>"><?php _e('Hide empty series?', 'organize-series'); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('hide-empty'); ?>" name="<?php echo $this->get_field_name('hide-empty'); ?>" value="1" <?php checked('1', $hide_empty); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('postlistdisplay-toggle'); ?>"><?php _e('(toggle if a list of other posts in the series will show on posts that are part of a series)', 'organize-series'); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('postlistdisplay-toggle'); ?>" name="<?php echo $this->get_field_name('postlistdisplay-toggle'); ?>" value="1" <?php checked('1', $postlistdisplay_toggle); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('seriestocdisplay-toggle'); ?>"><?php _e('Show the List of Series?', 'organize-series'); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('seriestocdisplay-toggle'); ?>" name="<?php echo $this->get_field_name('seriestocdisplay-toggle'); ?>" value="1" <?php checked('1', $seriestocdisplay_toggle); ?> /></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('serieswidget-title'); ?>"><strong><?php _e('Series widget title:', 'organize-series'); ?></strong><br />
			<small><?php _e('Put a text here to display it as a title of other posts in the series displayed in widget.', 'organize-series'); ?></small><br />
			<input class="widefat" id="<?php echo $this->get_field_id('serieswidget-title'); ?>" name="<?php echo $this->get_field_name('serieswidget-title'); ?>" type="text" value="<?php echo $widget_title; ?>" /></label><br />
		</p>
		
		<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("orgSeries_widget_latestseries");'));
add_action('widgets_init', create_function('', 'return register_widget("orgSeries_widget_seriestoc");'));


?>
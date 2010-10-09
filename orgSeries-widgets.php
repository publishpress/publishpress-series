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
		$widget_ops = array('description' => __('Use this to control the output of the latest series widget', $orgseries->org_domain));
		$this->WP_Widget('latestseries', __('Latest Series', $orgseries->org_domain), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		global $orgseries;
		extract( $args, EXTR_SKIP );
		
		$title = isset($instance['title']) ? $instance['title'] : __('Most Recent Series', $orgseries->org_domain);
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
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', $orgseries->org_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<?php printf(__('The layout and content of this widget can be adjusted via the latest-series-template on the <a href="%s">Series Options</a> page.', $orgseries->org_domain), $seriesoptionsurl); ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('hide_empty'); ?>"><?php _e('Don\'t show series with no posts?', $orgseries->org_domain); ?></label>
			<input class="checkbox" type="checkbox" <?php checked($hide_empty, true); ?> value = "1" id="<?php echo $this->get_field_id('hide_empty'); ?>" name="<?php echo $this->get_field_name('hide_empty'); ?>" />
			<br />
			<label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e( 'Order by:' ); ?></label>
			<select name="<?php echo $this->get_field_name('orderby'); ?>" id="<?php echo $this->get_field_id('orderby'); ?>" class="widefat">
				<option value="count"<?php selected( $instance['orderby'], 'count' ); ?>><?php _e('Number of posts in Series', $orgseries->org_domain); ?></option>
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php _e('Name of Series', $orgseries->org_domain); ?></option>
				<option value="slug"<?php selected( $instance['orderby'], 'slug' ); ?>><?php _e('Series Slug', $orgseries->org_domain); ?></option>
				<option value="term_id"<?php selected( $instance['orderby'], 'term_id' ); ?>><?php _e('When Series was Created', $orgseries->org_domain); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of series to display:', $orgseries->org_domain); ?></label>
			<input class="widefat" type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $number; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Display Order: ', $orgseries->org_domain); ?></label>
			<?php _e('ASC: ', $orgseries->org_domain); ?><input class="radio" id="<?php echo $this->get_field_id('order'); ?>-asc" name="<?php echo $this->get_field_name('order'); ?>" type="radio" value="ASC" <?php checked('ASC', $instance['order']); ?>/>
			<?php _e('DESC: ', $orgseries->org_domain); ?><input class="radio" id="<?php echo $this->get_field_id('order'); ?>-desc" name="<?php echo $this->get_field_name('order'); ?>" type="radio" value="DESC" <?php checked('DESC', $instance['order']); ?>/>
		</p>
	<?php
	}
}

class orgSeries_widget_seriestoc extends WP_Widget {
	
	function orgSeries_widget_seriestoc() {
		global $orgseries;
		$widget_ops = array('description' => __('Use this to display the Series Table of contents', $orgseries->org_domain));
		$this->WP_WIDGET('seriestoc', __('Series TOC', $orgseries->org_domain), $widget_ops);
	}
	
	function widget( $args, $instance ) {
		global $orgseries, $wp_query;
		extract( $args, EXTR_SKIP );
		$c = $instance['show-count'] ? '1' : '0';
		$e = $instance['hide-empty'] ? '1' : '0';
		$showpostlist = $instance['postlistdisplay_toggle'] ? '1' : '0';
		$series_args = $args = apply_filters('widget_seriestoc_args', array('orderby' => 'name', 'show_count' => $c, 'hide_empty' => $e, 'echo' => false, 'name' => 'orgseries_dropdown'));
		$title = $instance['title'];
		
		if ( $instance['list-type'] == 'list' ) {
			$out = '<ul>';
			$series_args['title_li'] = '';
			$out .= wp_list_series($series_args);
			$out .= '</ul>';
		} elseif ( $instance['list-type'] == 'dropdown' ) {
			$series_args['show_option_all'] = __('Select Series', $orgseries->org_domain);
			$out = wp_dropdown_series( $series_args );
		}
		
		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title )
				echo $before_title . $title . $after_title;
			echo $out;
					
			if ( ( $wp_query->is_single() ) && $showpostlist && $series = get_the_series() ) {
				echo '<br /><br /><h4>' . __('Other posts belonging to this series', $orgseries->org_domain) . '</h3>';
				echo '<ul>' . get_series_posts('','widget') . '</ul>';
			}
			echo $after_widget;
		}
	}
	
	function update( $new_instance, $old_instance ) {
		global $orgseries;
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['list-type'] = strip_tags($new_instance['list-type']);
		$instance['show-count'] = (int) $new_instance['show-count'];
		$instance['hide-empty'] = (int) $new_instance['hide-empty'];
		$instance['postlistdisplay-toggle'] = (int) $new_instance['postlistdisplay-toggle'];
		
		return $instance;
	}
	
	function form( $instance ) {
		global $orgseries;
		//Defaults
		$instance = wp_parse_args( (array) $instance, array(
			'title' => __('Series', $orgseries->org_domain),
			'list-type' => 'list',
			'show-count' => 1,
			'hide-empty' => 1,
			'postlistdisplay-toggle' => 1
		));
		$title = esc_attr( $instance['title'] );
		$list_type = $instance['list-type'];
		$show_count = $instance['show-count'];
		$hide_empty = $instance['hide-empty'];
		$postlistdisplay_toggle = $instance['postlistdisplay-toggle'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', $orgseries->org_domain); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
			<?php _e('Series list options:', $orgseries->org_domain); ?><br />
			<label for="<?php echo $this->get_field_id('list-type').'-dropdown'; ?>"><?php _e(' Dropdown: ', $orgseries->org_domain); ?><input type="radio" id="<?php echo $this->get_field_id('list-type').'-dropdown'; ?>" name="<?php echo $this->get_field_name('list-type'); ?>" value="dropdown" <?php checked('dropdown', $list_type); ?> /></label>
			<label for="<?php echo $this->get_field_id('list-type').'-list'; ?>"><?php _e(' List: ', $orgseries->org_domain); ?><input type="radio" id="<?php echo $this->get_field_id('list-type').'-list'; ?>" name="<?php echo $this->get_field_name('list-type'); ?>" value="list" <?php checked('list', $list_type); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('show-count'); ?>"><?php _e('Show post count?', $orgseries->org_domain); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('show-count'); ?>" name="<?php echo $this->get_field_name('show-count'); ?>" value="1" <?php checked('1', $show_count); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('hide-empty'); ?>"><?php _e('Hide empty series?', $orgseries->org_domain); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('hide-empty'); ?>" name="<?php echo $this->get_field_name('hide-empty'); ?>" value="1" <?php checked('1', $hide_empty); ?> /></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('postlistdisplay-toggle'); ?>"><?php _e('(use to select if a list of other posts in the series will show on posts that are part of a series', $orgseries->org_domain); ?>
			<input type="checkbox" id="<?php echo $this->get_field_id('postlistdisplay-toggle'); ?>" name="<?php echo $this->get_field_name('postlistdisplay-toggle'); ?>" value="1" <?php checked('1', $postlistdisplay_toggle); ?> /></label>
		</p>
		
		<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("orgSeries_widget_latestseries");'));
add_action('widgets_init', create_function('', 'return register_widget("orgSeries_widget_seriestoc");'));


?>
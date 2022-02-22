<?php
/**
 * This file contains all the code for the orgSeries widgets.
 *
 * @package Publishpress Series WordPress Plugin
 * @since 2.2
**/
class orgSeries_widget_latestseries extends WP_Widget {

	public function __construct() {
		global $orgseries;
		$widget_ops = array('description' => esc_html__('Use this to control the output of the latest series widget', 'organize-series'));
		parent::__construct('latestseries', esc_html__('Latest Series', 'organize-series'), $widget_ops);
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
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $before_widget;

            if ($title) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $before_title . $title . $after_title;
            }
			?>
			<ul>
				<?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $out; 
                ?>
			</ul>
			<?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		$seriesoptionsurl = ppseries_series_settings_page();
;
		$number = (int) $instance['number'];
		?>
		<p class="ppseries-widget-paragraph">
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><strong><?php esc_html_e('Title', 'organize-series'); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		<p class="ppseries-widget-paragraph">
			<?php 
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            printf(__('The layout and content of this widget can be adjusted via the <a href="%s">Latest Series</a> area.', 'organize-series'), esc_url($seriesoptionsurl)); ?>
		</p>
		<p class="ppseries-widget-paragraph">
			<label for="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>"><strong><?php esc_html_e('Hide series with no posts?', 'organize-series'); ?></strong></label>
			<input class="checkbox" type="checkbox" <?php checked($hide_empty, true); ?> value = "1" id="<?php echo esc_attr($this->get_field_id('hide_empty')); ?>" name="<?php echo esc_attr($this->get_field_name('hide_empty')); ?>" />
			<br />
			<label for="<?php echo esc_attr($this->get_field_id('orderby')); ?>"><strong><?php esc_html_e( 'Order by:' ); ?></strong></label>
			<select name="<?php echo esc_attr($this->get_field_name('orderby')); ?>" id="<?php echo esc_attr($this->get_field_id('orderby')); ?>" class="widefat">
				<option value="count"<?php selected( $instance['orderby'], 'count' ); ?>><?php esc_html_e('Number of posts in Series', 'organize-series'); ?></option>
				<option value="name"<?php selected( $instance['orderby'], 'name' ); ?>><?php esc_html_e('Name of Series', 'organize-series'); ?></option>
				<option value="slug"<?php selected( $instance['orderby'], 'slug' ); ?>><?php esc_html_e('Series Slug', 'organize-series'); ?></option>
				<option value="term_id"<?php selected( $instance['orderby'], 'term_id' ); ?>><?php esc_html_e('When Series was Created', 'organize-series'); ?></option>
				<option value="rand"<?php selected( $instance['orderby'], 'rand' ); ?>><?php esc_html_e('Random', 'organize-series'); ?></option>
			</select>
		</p>
		<p class="ppseries-widget-paragraph">
			<label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><strong><?php esc_html_e('Number of series to display:', 'organize-series'); ?></strong></label>
			<input class="widefat" type="text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" value="<?php echo esc_attr($number); ?>" />
		</p>
		<p class="ppseries-widget-paragraph">
			<label for="<?php echo esc_attr($this->get_field_id('order')); ?>"><strong><?php esc_html_e('Display Order: ', 'organize-series'); ?></strong></label><br />
			<?php esc_html_e('ASC: ', 'organize-series'); ?><input class="radio" id="<?php echo esc_attr($this->get_field_id('order')); ?>-asc" name="<?php echo esc_attr($this->get_field_name('order')); ?>" type="radio" value="ASC" <?php checked('ASC', $instance['order']); ?>/>
			<?php esc_html_e('DESC: ', 'organize-series'); ?><input class="radio" id="<?php echo esc_attr($this->get_field_id('order')); ?>-desc" name="<?php echo esc_attr($this->get_field_name('order')); ?>" type="radio" value="DESC" <?php checked('DESC', $instance['order']); ?>/>
		</p>
	<?php
	}
}

class orgSeries_widget_seriestoc extends WP_Widget {

	public function __construct() {
		global $orgseries;
		$widget_ops = array('description' => __('Use this to display the Series Table of contents', 'organize-series'));
		parent::__construct('seriestoc', __('Series Table of Contents', 'organize-series'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $orgseries, $wp_query;
		extract( $args, EXTR_SKIP );
		$show_count = isset($instance['show-count']) ? (int)$instance['show-count'] : 0;
		$hide_empty = isset($instance['hide-empty']) ? (int)$instance['hide-empty'] : 0;
		$showpostlist = isset($instance['postlistdisplay-toggle']) ? $instance['postlistdisplay-toggle'] : 0;
		$showseriestoc =isset( $instance['seriestocdisplay-toggle']) ? $instance['seriestocdisplay-toggle'] : 0;
		$series_id = (isset($instance['series-id']) && (int)$instance['series-id'] > 0) ? $instance['series-id'] : '';

		$os_orderby = isset($instance['os_orderby']) ? $instance['os_orderby'] : '';
		$os_order = isset($instance['os_order']) ? $instance['os_order'] : '';
		$os_exclude = isset($instance['os_exclude']) ? (array)$instance['os_exclude'] : [];
		$os_exclude = array_filter($os_exclude);
		$os_include = isset($instance['os_include']) ? (array)$instance['os_include'] : [];
		$os_include = array_filter($os_include);
		$os_number = isset($instance['os_number']) ? $instance['os_number'] : '';
		$os_offset = isset($instance['os_offset']) ? $instance['os_offset'] : '';
		$os_search = isset($instance['os_search']) ? $instance['os_search'] : '';

		//prepare args
		$series_widget_args = [];
		$series_widget_args['orderby'] = $os_orderby;
		$series_widget_args['order'] = $os_order;
		$series_widget_args['show_count'] = $show_count;
		$series_widget_args['hide_empty'] = $hide_empty;
		$series_widget_args['echo'] = false;
		if(count($os_exclude) > 0){
			$series_widget_args['exclude'] = join(", ", $os_exclude);
		}
		if(count($os_exclude) > 0){
			$series_widget_args['include'] = join(", ", $os_exclude);
		}
		if((int)$os_number > 0){
			$series_widget_args['number'] = $os_number;
		}
		if((int)$os_offset > 0){
			$series_widget_args['offset'] = $os_offset;
		}
		if(!empty(trim($os_search))){
			$series_widget_args['search'] = $os_search;
		}

		$series_widget_args['orderby'] = $os_orderby;
		$series_widget_args['orderby'] = $os_orderby;

		$series_args = $args = apply_filters('widget_seriestoc_args', $series_widget_args);

		$title = $instance['title'];
		$series_id = '';

		if (isset($instance['serieswidget-title']) && strlen($instance['serieswidget-title']) > 0)
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
				$series_args['name'] = 'orgseries_dropdown';
				$out = wp_dropdown_series( $series_args );
			}
		}
		
		if ( $showpostlist ) {
			if ( ( $wp_query->is_single ) && $showpostlist && $series = get_the_series() ) {
				if ( $showseriestoc ) $out .= '<br /><br />';
				$out .= get_series_posts($series_id,'widget', false, $widget_title);
				}
		}

		if ( !empty($out) ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $before_widget;
            if ($title) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo $before_title . $title . $after_title;
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $out;
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		global $orgseries;
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['serieswidget-title'] = strip_tags($new_instance['serieswidget-title']);
		$instance['list-type'] = strip_tags($new_instance['list-type']);
		$instance['show-count'] = isset($new_instance['show-count']) ? (int) $new_instance['show-count'] : 0;
		$instance['hide-empty'] = isset($new_instance['hide-empty']) ? (int) $new_instance['hide-empty'] : 0;
		$instance['postlistdisplay-toggle'] = isset($new_instance['postlistdisplay-toggle']) ? (int) $new_instance['postlistdisplay-toggle'] : 0;
		$instance['seriestocdisplay-toggle'] = isset($new_instance['seriestocdisplay-toggle']) ? (int) $new_instance['seriestocdisplay-toggle'] : 0;
		$instance['series-id'] = (int) $new_instance['series-id'];


		$instance['os_orderby'] = $new_instance['os_orderby'];
		$instance['os_order'] = $new_instance['os_order'];
		$instance['os_exclude'] = (array)$new_instance['os_exclude'];
		$instance['os_include'] = (array)$new_instance['os_include'];
		$instance['os_number'] = $new_instance['os_number'];
		$instance['os_offset'] = $new_instance['os_offset'];
		$instance['os_search'] = $new_instance['os_search'];

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
			'series-id' => 0,
			'hide-empty' => 1,
			'postlistdisplay-toggle' => 1,
			'seriestocdisplay-toggle' => 1,
			'os_orderby' => 'term_id',
			'os_order' => 'DESC',
			'os_exclude' => [0],
			'os_include' => [0],
			'os_number' => '',
			'os_offset' => '',
			'os_search' => ''
		));

		$title = esc_attr( $instance['title'] );
		$widget_title =  esc_attr($instance['serieswidget-title']);
		$list_type = $instance['list-type'];
		$show_count = $instance['show-count'];
		$series_id  = $instance['series-id'];
		$hide_empty = $instance['hide-empty'];
		$postlistdisplay_toggle = $instance['postlistdisplay-toggle'];
		$seriestocdisplay_toggle = $instance['seriestocdisplay-toggle'];

		$os_orderby = $instance['os_orderby'];
		$os_order = $instance['os_order'];
		$os_exclude = (array)$instance['os_exclude'];
		$os_include = (array)$instance['os_include'];
		$os_number = $instance['os_number'];
		$os_offset = $instance['os_offset'];
		$os_search = $instance['os_search'];
		$series_array_list = ppseries_get_series_list();
		?>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><strong><?php esc_html_e('Title', 'organize-series'); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('seriestocdisplay-toggle')); ?>"><strong><?php esc_html_e('Show Series Table Of Content', 'organize-series'); ?></strong>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('seriestocdisplay-toggle')); ?>" name="<?php echo esc_attr($this->get_field_name('seriestocdisplay-toggle')); ?>" value="1" <?php checked('1', $seriestocdisplay_toggle); ?> /></label>
		</p>

		<p>
			<strong><?php esc_html_e('Show Series Table Of Content as', 'organize-series'); ?></strong><br />
			<label for="<?php echo esc_attr($this->get_field_id('list-type')).'-dropdown'; ?>"><?php esc_html_e(' Dropdown: ', 'organize-series'); ?><input type="radio" id="<?php echo esc_attr($this->get_field_id('list-type')).'-dropdown'; ?>" name="<?php echo esc_attr($this->get_field_name('list-type')); ?>" value="dropdown" <?php checked('dropdown', $list_type); ?> /></label>
			<label for="<?php echo esc_attr($this->get_field_id('list-type')).'-list'; ?>"><?php esc_html_e(' List: ', 'organize-series'); ?><input type="radio" id="<?php echo esc_attr($this->get_field_id('list-type')).'-list'; ?>" name="<?php echo esc_attr($this->get_field_name('list-type')); ?>" value="list" <?php checked('list', $list_type); ?> /></label>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show-count')); ?>"><strong><?php esc_html_e('Show post count', 'organize-series'); ?></strong>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('show-count')); ?>" name="<?php echo esc_attr($this->get_field_name('show-count')); ?>" value="1" <?php checked('1', $show_count); ?> /></label>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('hide-empty')); ?>"><strong><?php esc_html_e('Hide empty series', 'organize-series'); ?></strong>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('hide-empty')); ?>" name="<?php echo esc_attr($this->get_field_name('hide-empty')); ?>" value="1" <?php checked('1', $hide_empty); ?> /></label>
		</p>

		<p>
		<label for="<?php echo esc_attr($this->get_field_id('os_orderby')).''; ?>"><strong><?php esc_html_e('Order by	', 'organize-series'); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr($this->get_field_id('os_orderby')).''; ?>" name="<?php echo esc_attr($this->get_field_name('os_orderby')); ?>">
				<?php 
				$order_by_options = [
					'name' 		=> __('name', 'organize-series'),
					'count' 	=> __('count', 'organize-series'),
					'slug' 		=> __('slug', 'organize-series'),
					'term_id' 	=> __('series id', 'organize-series'),
				];
				
				foreach($order_by_options as $key => $label){
					$selected = $os_orderby === $key ? 'selected="selected"' : '';
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<option value="'. esc_attr($key) .'" '.$selected.'>'. esc_html($label) .'</option>';
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_order')).''; ?>"><strong><?php esc_html_e('Order	', 'organize-series'); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr($this->get_field_id('os_order')).''; ?>" name="<?php echo esc_attr($this->get_field_name('os_order')); ?>">
				<?php 
				$order_options = [
					'DESC' 		=> __('Descending', 'organize-series'),
					'ASC' 		=> __('Ascending', 'organize-series'),
				];
				
				foreach($order_options as $key => $label){
					$selected = $os_order === $key ? 'selected="selected"' : '';
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo '<option value="'. esc_attr($key) .'" '.$selected.'>'. esc_html($label) .'</option>';
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_exclude')).''; ?>"><strong><?php esc_html_e('Exclude series', 'organize-series'); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr($this->get_field_id('os_exclude')).''; ?>" name="<?php echo esc_attr($this->get_field_name('os_exclude')); ?>[]" multiple>
				<?php 
				if(is_array($series_array_list) && count($series_array_list) > 0) {
					foreach($series_array_list as $key => $label){
						$selected = in_array($key, $os_exclude) ? 'selected="selected"' : '';
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<option value="'. esc_attr($key) .'" '.$selected.'>'. esc_html($label) .'</option>';
					}
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_include')).''; ?>"><strong><?php esc_html_e('Include series', 'organize-series'); ?></strong></label>
			<select class="widefat" id="<?php echo esc_attr($this->get_field_id('os_include')).''; ?>" name="<?php echo esc_attr($this->get_field_name('os_include')); ?>[]" multiple>
				<?php 
				if(is_array($series_array_list) && count($series_array_list) > 0) {
					foreach($series_array_list as $key => $label){
						$selected = in_array($key, $os_include) ? 'selected="selected"' : '';
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '<option value="'. esc_attr($key) .'" '.$selected.'>'. esc_html($label) .'</option>';
					}
				}
				?>
			</select>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_number')); ?>"><strong><?php esc_html_e('Number of Series', 'organize-series'); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('os_number')); ?>" name="<?php echo esc_attr($this->get_field_name('os_number')); ?>" type="number" min="1" value="<?php echo esc_attr($os_number); ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_offset')); ?>"><strong><?php esc_html_e('Offset', 'organize-series'); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('os_offset')); ?>" name="<?php echo esc_attr($this->get_field_name('os_offset')); ?>" type="number" min="1" value="<?php echo esc_attr($os_offset); ?>" />
			<br /> <small><?php esc_html_e('You can select the offset for the number of series (useful for paging).  No offset if left blank', 'organize-series'); ?></small>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('os_search')); ?>"><strong><?php esc_html_e('Search', 'organize-series'); ?></strong></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('os_search')); ?>" name="<?php echo esc_attr($this->get_field_name('os_search')); ?>" type="text" value="<?php echo esc_attr($os_search); ?>" />
			<br /> <small><?php esc_html_e('You can return any series that match this search string (matched against series names) - case insensitive', 'organize-series'); ?></small>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('postlistdisplay-toggle')); ?>"><strong><?php esc_html_e('Show other posts in the current series', 'organize-series'); ?></strong>
			<input type="checkbox" id="<?php echo esc_attr($this->get_field_id('postlistdisplay-toggle')); ?>" name="<?php echo esc_attr($this->get_field_name('postlistdisplay-toggle')); ?>" value="1" <?php checked('1', $postlistdisplay_toggle); ?> /></label>
		</p>


		<p>
			<?php esc_html_e('Series', 'organize-series'); ?>
		<ul class="pp-widget-series">
				<?php 
				$series_list = get_series_list($series_id);
				$series_default = (int)$series_id === 0 ? 'checked' : '';
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<li  class="pp-widget-series-li" id="pp-widget-series-li-0">
						<label for="pp-widget-series-item-0" class="selectit">
						<input value="0" type="radio" name="'. esc_attr($this->get_field_name('series-id')) .'"
						 id="pp-widget-series-item-0"
						 '. esc_attr($series_default) .'> 
						<span class="li-series-name">'.esc_html__('Current series', 'organize-series').'</span></label></li>';
				if(is_array($series_list) && count($series_list) > 0){
					foreach($series_list as $serie_list){
						$series_checked = (int)$serie_list['checked'] > 0 ? 'checked' : '';
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo '
						<li  class="pp-widget-series-li" id="pp-widget-series-li-'.esc_attr($serie_list['series_ID']).'">
						<label for="pp-widget-series-item-'.esc_attr($serie_list['series_ID']).'" class="selectit">
						<input value="'.esc_attr($serie_list['series_ID']).'" type="radio" name="'. esc_attr($this->get_field_name('series-id')) .'"
						 id="pp-widget-series-item-'.esc_attr($serie_list['series_ID']).'"
						 '. esc_attr($series_checked) .'> 
						<span class="li-series-name">'.esc_html($serie_list['ser_name']).'</span></label></li>';
					}

				}?>
		</ul>
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('serieswidget-title')); ?>"><strong><?php esc_html_e('Series widget title:', 'organize-series'); ?></strong><br />
				<input class="widefat" id="<?php echo esc_attr($this->get_field_id('serieswidget-title')); ?>" name="<?php echo esc_attr($this->get_field_name('serieswidget-title')); ?>" type="text" value="<?php echo esc_attr($widget_title); ?>" />
			<small><?php esc_html_e('This text will display above other posts in this series.', 'organize-series'); ?></small><br />
			</label><br />
		</p>

		<?php
	}
}

add_action(
        'widgets_init',
        function () {
            register_widget('orgSeries_widget_latestseries');
            register_widget('orgSeries_widget_seriestoc');
        }
);

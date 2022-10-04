<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

/**
 * Class PPS_Publisher_Post_Publish_Table
 */
class PPS_Publisher_Post_Publish_Table extends WP_List_Table
{

    /**
     * PPS_Publisher_Post_Publish_Table constructor.
     *
     * @param array $args
     */
    function __construct($args = [])
    {

        //Set parent defaults
        parent::__construct([
            'singular' => 'series-part',     //singular name of the listed records
            'plural' => 'series-parts',    //plural name of the listed records
            'ajax' => true        //does this table support ajax?
        ]);
    }

    /**
     * Get a list of CSS classes for the WP_List_Table table tag.
     *
     * @return array List of CSS classes for the table tag.
     */
    protected function get_table_classes()
    {

        return parent::get_table_classes();
    }

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 3.1.0
	 */
	public function no_items() {
		_e( 'There are no unpublished posts in this series.', 'organize-series' );
	}

    /**
     * Show single row item
     *
     * @param array $item
     */
    public function single_row($item)
    {
        $class = ['series-part-tr'];

        echo sprintf('<tr id="%s" class="%s">', 'post-' . esc_attr($item->ID), esc_attr(implode(' ', $class)));
        $this->single_row_columns($item);
        echo '</tr>';
    }

    /**
     * Get list table columns
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = [
            'cb'             => '<input type="checkbox"/>', //Render a checkbox instead of text
            'title'          => esc_html__('Title', 'organize-series'),
            'author'         => esc_html__('Author', 'organize-series'),
            'categories'     => esc_html__('Categories', 'organize-series'),
            'tags'           => esc_html__('Tags', 'organize-series'),
            'post_status'    => esc_html__('Status', 'organize-series'),
            'series_part'    => esc_html__('Order', 'organize-series'),
            'series_preview' => esc_html__('Preview', 'organize-series'),
        ];

        return $columns;
    }

    public function get_table_data(){

        $series_id = isset($_GET['series_ID'])? (int)$_GET['series_ID'] : false;
        $meta_key = SERIES_PART_KEY;
        $series_posts = [];

        if ($series_id) {
            $per_page = $this->get_items_per_page('pp_series_publisher_per_page', 20);
            $current_page = $this->get_pagenum();

            $arg = array(
                'post_status' => ['future', 'draft', 'pending'],
                'paged' => $current_page,
                'posts_per_page' => $per_page,
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ppseries_get_series_slug(),
                        'field' => 'id',
                        'terms' => array($series_id)
                    )
                ),
                'meta_query' => array(
                    "relation" => "or",
                    'part_field_sort_value' => array(
                        'key' => $meta_key,
                        'type'=> 'NUMERIC'
                    ), 
                    'part_field_sort' => array(
                        'key' => $meta_key,
                        'compare' => 'NOT EXISTS',
                        'type'=> 'NUMERIC'
                    ),
                ),
                'orderby' => array( 
                    'part_field_sort' => 'ASC'
                ),
            );

            /**
             * Handle category filter
             */
            if ((!empty($_REQUEST['cat'])) && $category = sanitize_text_field($_REQUEST['cat'])) {
                $arg['tax_query'][] = [
                    'taxonomy' => 'category',
                    'field' => 'slug',
                    'terms' => array($category)
                ];
            }

            /**
             * Handle search
             */
            if ((!empty($_REQUEST['s'])) && $search = sanitize_text_field($_REQUEST['s'])) {
                $arg['s'] = $search;
            }

            $series_query = new WP_Query($arg);
    
            return ['posts'=> $series_query->posts, 'counts'=> $series_query->found_posts];
        }

        return $series_posts;
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count()
    {
        return $this->get_table_data()['counts'];
    }


	/**
     * Add custom filter to tablenav
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {

		if ( 'top' === $which ) {

            $selected_category = (!empty($_REQUEST['cat'])) ? sanitize_text_field($_REQUEST['cat']) : '';
             ?>
            <div class="alignleft actions">
                <?php 
                wp_dropdown_categories(
                    array(
                        'show_option_all' => __( 'All Categories', 'organize-series' ),
                        'orderby'         => 'name',
                        'order'           => 'ASC',
                        'hide_empty'      => false,
                        'hide_if_empty'   => true,
                        'selected'        => $selected_category,
                        'hierarchical'    => true,
                        'name'            => 'cat',
                        'taxonomy'        => 'category',
                        'value_field'     => 'slug',
                    )
                );

                submit_button( __( 'Filter', 'organize-series' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
                ?>
            </div>
        <?php
		}
	}

    /**
     * Displays the search box.
     *
     * @param string $text The 'submit' button label.
     * @param string $input_id ID attribute value for the search input field.
     *
     *
     */
    public function search_box($text, $input_id)
    {
        if (!isset($_REQUEST['s']) && !$this->has_items()) {
            return;
        }

        $input_id = $input_id . '-search-input';

        if (!empty($_REQUEST['orderby'])) {
            echo '<input type="hidden" name="orderby" value="' . esc_attr(sanitize_text_field($_REQUEST['orderby'])) . '" />';
        }
        if (!empty($_REQUEST['order'])) {
            echo '<input type="hidden" name="order" value="' . esc_attr(sanitize_text_field($_REQUEST['order'])) . '" />';
        }
        if (!empty($_REQUEST['page'])) {
            echo '<input type="hidden" name="page" value="' . esc_attr(sanitize_text_field($_REQUEST['page'])) . '" />';
        }
        if (!empty($_REQUEST['action'])) {
            echo '<input type="hidden" name="action" value="' . esc_attr(sanitize_text_field($_REQUEST['action'])) . '" />';
        }
        if (!empty($_REQUEST['series_ID'])) {
            echo '<input type="hidden" name="series_ID" value="' . esc_attr(sanitize_text_field($_REQUEST['series_ID'])) . '" />';
        }
        if (!empty($_REQUEST['cat'])) {
            echo '<input type="hidden" name="cat" value="' . esc_attr(sanitize_text_field($_REQUEST['cat'])) . '" />';
        }
        
        if (!empty($_REQUEST['s'])) {
            echo '<input type="hidden" name="s" value="' . esc_attr(sanitize_text_field($_REQUEST['s'])) . '" />';
        }
            
        echo '<input type="hidden" name="action" value="list" />';
        ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($text); ?>:</label>
            <input type="search" id="<?php echo esc_attr($input_id); ?>" name="s"
                   value="<?php _admin_search_query(); ?>"/>
            <?php submit_button($text, '', '', false, ['id' => 'taxopress-log-search-submit']); ?>
        </p>
        <?php
    }

    /**
     * Generates and display row actions links for the list table.
     *
     * @param object $item The item being acted upon.
     * @param string $column_name Current column name.
     * @param string $primary Primary column name.
     *
     * @return string The row actions HTML, or an empty string if the current column is the primary column.
     */
    protected function handle_row_actions($item, $column_name, $primary)
    {
        $series_id = isset($_GET['series_ID'])? (int)$_GET['series_ID'] : false;

        $actions['edit'] = sprintf(
            '<a href="%s">%s</a>',
                esc_url(
                    add_query_arg(
                        [
                            'post' => $item->ID, 
                            'action' => 'edit',
                        ],
                        admin_url('post.php')
                    )
                ),
                esc_html__('Edit', 'organize-series')
            );
            
            $actions = array_merge($actions, [
                'delete' => sprintf(
                    '<a href="%s" class="delete-post">%s</a>',
                    add_query_arg([
                        'page' => 'manage-issues',
                        'action' => 'list', 
                        'part_action' => 'pps-publisher-delete-posts', 
                        'series_ID' => esc_attr($series_id),
                        'series_post' => esc_attr($item->ID),
                        '_wpnonce' => wp_create_nonce('bulk-series-parts')
                    ], 
                    admin_url('edit.php')),
                    esc_html__('Trash', 'organize-series')
                ),
            ]);

        return $column_name === $primary ? $this->row_actions($actions, false) : '';
    }

    /**
     * Add default
     *
     * @param object $item
     * @param string $column_name
     *
     * @return mixed|string|void
     */
    protected function column_default($item, $column_name)
    {
        return !empty($item->$column_name) ? $item->$column_name : '&mdash;';
    }

    /**
     * The checkbox column
     *
     * @param object $item
     *
     * @return string|void
     */
    protected function column_cb($item)
    {
        $out = sprintf('<input type="checkbox" name="%1$s[]" value="%2$s"/>', 'series_post', (int)$item->ID);
    
        return $out;
    }

    /**
     * The series_part column
     *
     * @param $item
     *
     * @return string
     */
    protected function column_series_part($item)
    {
        $series_id = isset($_GET['series_ID'])? (int)$_GET['series_ID'] : false;
        $series_part = get_post_meta($item->ID, SERIES_PART_KEY, true);

        if(empty(trim($series_part))){
            $series_part_output =  esc_html__('(Currently has no Part number)', 'organize-series');
        }else{
            $series_part_output = $series_part;
        }

        return $series_part_output;
    }

    /**
     * The title column
     *
     * @param $item
     *
     * @return string
     */
    protected function column_title($item)
    {

        return sprintf(
            '<a href="%1$s"><strong><span class="row-title">%2$s</span></strong></a>',
                esc_url(
                    add_query_arg(
                        [
                            'post' => $item->ID, 
                            'action' => 'edit',
                        ],
                        admin_url('post.php')
                    )
                ),
                esc_html($item->post_title)
            );
    }

	/**
	 * Handles the post author column output.
	 *
	 */
	protected function column_author($item) 
    {
        return get_the_author_meta('display_name', $item->post_author);
	}

	/**
	 * Handles the post category column output.
	 *
	 */
	protected function column_categories($item) 
    {
        $terms = get_the_terms($item->ID, 'category');
        $term_html = '';

        if ( is_array( $terms ) ) {
            $term_links = [];
            foreach ( $terms as $t ) {
                $term_links[] = '<a href="'. get_term_link($t->term_id) .'"> ' . esc_html($t->name) . ' </a>';
            }
            $term_html = implode(', ', $term_links);
        } else {
            $term_html = '&mdash;';
        }

        return $term_html;
	}

	/**
	 * Handles the post tags column output.
	 *
	 */
	protected function column_tags($item) 
    {
        $terms = get_the_terms($item->ID, 'post_tag');
        $term_html = '';

        if ( is_array( $terms ) ) {
            $term_links = [];
            foreach ( $terms as $t ) {
                $term_links[] = '<a href="'. get_term_link($t->term_id) .'"> ' . esc_html($t->name) . ' </a>';
            }
            $term_html = implode(', ', $term_links);
        } else {
            $term_html = '&mdash;';
        }

        return $term_html;
	}

	/**
	 * Handles the post post_status column output.
     * 
	 */
	public function column_post_status( $post ) {
        $post_status = get_post_status_object($post->post_status);

        return $post_status->label;
	}

    /**
     * The preview column
     *
     * @param $item
     *
     * @return string
     */
    protected function column_series_preview($item)
    {
        return sprintf(
            '<a href="%1$s">%2$s</a>', 
            esc_url(home_url('?p='.$item->ID.'&preview=true')), 
            esc_html__('Preview', 'organize-series')
        );
    }

    /**
     * Get the bulk actions to show in the top page dropdown
     *
     * @return array
     */
    protected function get_bulk_actions()
    {
        $actions = [
            'pps-publisher-delete-posts' => esc_html__('Move to Trash', 'organize-series')
        ];

        return $actions;
    }

    /**
     * Sets up the items to list.
     */
    public function prepare_items()
    {

        /**
         * First, lets decide how many records per page to show
         */
        $per_page = $this->get_items_per_page('pp_series_publisher_per_page', 20);

        /**
         * Fetch the data
         */
        $results = $this->get_table_data();
        $data = $results['posts'];
        $total_items  = $results['counts'];
        $current_page = $this->get_pagenum();

        /**
         * Now we can add the data to the items property, where it can be used by the rest of the class.
         */
        $this->items = $data;
        /**
         * We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args([
            'total_items' => $total_items,                      //calculate the total number of items
            'per_page'    => $per_page,                         //determine how many items to show on a page
            'total_pages' => ceil($total_items / $per_page)   //calculate the total number of pages
        ]);
    }

	/**
	 * Display the list table.
	 *
	 * @access public
	 * @return void
	 */
	public function display() {

		$this->views();

		parent::display();
	}
}
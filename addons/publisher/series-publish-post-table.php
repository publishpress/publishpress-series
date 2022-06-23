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
            'series_preview' => esc_html__('Preview', 'organize-series'),
        ];

        return $columns;
    }

    public function get_table_data(){

        $series_id = isset($_GET['series_ID'])? (int)$_GET['series_ID'] : false;
        $meta_key = SERIES_PART_KEY;
        $series_posts = [];

        if ($series_id) {
            $arg = array(
                'post_status' => ['future', 'draft', 'pending'],
                'posts_per_page' => -1,
                'no_found_rows' => true,
                'tax_query' => array(
                    'relation' => 'AND',
                    array(
                        'taxonomy' => ppseries_get_series_slug(),
                        'field' => 'id',
                        'terms' => array($series_id)
                    )
                ),
            );
            $series_query = new WP_Query($arg);
            $series_posts = $series_query->posts;
        }

        return $series_posts;
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
        $per_page = $this->get_items_per_page(str_replace('-', '_', $this->screen->id . '_per_page'), 999);


        /**
         * Fetch the data
         */
        $data = $this->get_table_data();

        /**
         * Pagination.
         */
        $current_page = $this->get_pagenum();
        $total_items = count($data);


        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to
         */
        $data = array_slice($data, (($current_page - 1) * $per_page), $per_page);

        /**
         * Now we can add the data to the items property, where it can be used by the rest of the class.
         */
        $this->items = $data;

        /**
         * We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args([
            'total_items' => $total_items,                      //calculate the total number of items
            'per_page' => $per_page,                         //determine how many items to show on a page
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
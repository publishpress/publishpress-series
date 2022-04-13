<div class="wrap">
  <h2><?php _e('Manage Series to Publish', 'organize-series-publisher'); ?></h2>

  <table class="widefat im_category_list wp-list-table">
    <thead>
      <tr>
        <th scope="col" class="manage-column column-title column-primary"><?php _e('Publish posts in series', 'organize-series-publisher'); ?></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
        <th scope="col" class="manage-column"></th>
      </tr>
    </thead>
    <tbody>
    <?php $alt = ' class="alternate"'; ?>
    <?php foreach ( $series as $ser ): ?>
      <?php

        //count published posts
        $posts_arg = array(
            'post_status' => 'publish',
            'paged' => 1,
            'posts_per_page' => 1,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                  'taxonomy' => ppseries_get_series_slug(),
                  'field' => 'id',
                  'terms' => array($ser->term_id)
                )
            ),
        );
        $posts = new WP_Query($posts_arg);
        $published_posts_counts = $posts->found_posts;

        //count unpublished posts
        $posts_arg = array(
            'post_status' => ['draft', 'pending'],
            'paged' => 1,
            'posts_per_page' => 1,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                  'taxonomy' => ppseries_get_series_slug(),
                  'field' => 'id',
                  'terms' => array($ser->term_id)
                )
            ),
        );
        $posts = new WP_Query($posts_arg);
        $unpublished_posts_counts = $posts->found_posts;

        //count scheduled posts
        $posts_arg = array(
            'post_status' => 'future',
            'paged' => 1,
            'posts_per_page' => 1,
            'tax_query' => array(
                'relation' => 'AND',
                array(
                  'taxonomy' => ppseries_get_series_slug(),
                  'field' => 'id',
                  'terms' => array($ser->term_id)
                )
            ),
        );
        $posts = new WP_Query($posts_arg);
        $scheduled_posts_counts = $posts->found_posts;

      ?>
      <tr id="cat-<?php echo $ser->term_id; ?>"<?php echo $alt; ?>>
        <td class="title column-title column-primary"><strong><a title="<?php echo sprintf(__('Edit the status of %1$s', 'organize-series-publisher'), $ser->name); ?>" href="<?php echo 'edit-tags.php?action=edit&taxonomy=series&tag_ID='.$ser->term_id; ?>"><?php echo $ser->name; ?></a></strong></td>
        <td> <?php echo sprintf(__('%d Published Posts', 'organize-series-publisher'), $published_posts_counts); ?> </td>
        <td> <?php echo sprintf(__('%d Unpublished Posts', 'organize-series-publisher'), $unpublished_posts_counts); ?> </td>
        <td> <?php echo sprintf(__('%d Scheduled Posts', 'organize-series-publisher'), $scheduled_posts_counts); ?> </td>
        <td><?php
        echo "<a class='im-publish' href='?page=manage-issues&amp;action=list&amp;series_ID=$ser->term_id'>". __('Publish all posts', 'organize-series-publisher')."</a>";
        ?></td>
        <td><?php
            echo "<a class='im-unpublish' href='?page=manage-issues&amp;action=unpublish&amp;series_ID=$ser->term_id'>". __('Unpublish all posts', 'organize-series-publisher')."</a>";
        ?></td>
        <td><?php
            echo "<a class='im-ignore' href='/wp-admin/edit.php?series=".$ser->slug."'>".__('View series in admin','organize-series-publisher')."</a>";
        ?></td>
        <td><?php
            echo "<a class='im-ignore' href='". get_term_link($ser->term_id) ."'>".__('View series in frontend','organize-series-publisher')."</a>";
        ?></td>
      </tr>
      <?php $alt = empty( $alt ) ? ' class="alternate"' : ''; ?>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php if(count($series) === 0){ ?>
    <div class="ppseries-warning"><?php _e('You have no series available to publish.', 'organize-series-publisher'); ?></div>
  <?php }
  ?>
</div>

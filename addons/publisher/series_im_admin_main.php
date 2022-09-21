<div class="wrap">
  <h2><?php _e('Manage Series to Publish', 'organize-series'); ?></h2>

  <table class="widefat im_category_list wp-list-table">
    <thead>
      <tr>
        <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Series', 'organize-series'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Series Order', 'organize-series'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Publish Series', 'organize-series'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Admin View', 'organize-series'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Front View', 'organize-series'); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php $alt = ' class="alternate"'; ?>
    <?php foreach ( $series as $ser ): ?>
      <tr id="<?php echo esc_attr('cat-' . $ser->term_id); ?>"<?php echo $alt;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <td class="title column-title column-primary"><strong><a title="<?php echo sprintf(esc_html__('Edit the status of %1$s', 'organize-series'), esc_html($ser->name)); ?>" href="<?php echo esc_url(admin_url('edit-tags.php?action=edit&taxonomy=series&tag_ID='.(int)$ser->term_id)); ?>"><?php echo esc_html($ser->name); ?></a></strong></td>
        <td><?php
            echo "<a class='im-publish' href='". esc_url(admin_url('edit.php?page=manage-issues&amp;action=part&amp;series_ID='. (int)$ser->term_id.'')) ."'>". esc_html__('Series order', 'organize-series')."</a>";
        ?></td>
        <td><?php
        echo "<a class='im-publish' href='". esc_url(admin_url('edit.php?page=manage-issues&amp;action=list&amp;series_ID='. (int)$ser->term_id.'')) ."'>". esc_html__('Publish or schedule posts', 'organize-series')."</a>";
        ?></td>
        <td><?php
            echo "<a class='im-ignore' href='". esc_url(admin_url('edit.php?series='. $ser->slug.'')) ."'>". esc_html__('View series in admin','organize-series')."</a>";
        ?></td>
        <td><?php
            echo "<a class='im-ignore' href='". esc_url(get_term_link($ser->term_id)) ."'>". esc_html__('View series in frontend','organize-series')."</a>";
        ?></td>
      </tr>
      <?php $alt = empty( $alt ) ? ' class="alternate"' : ''; ?>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php if(count($series) === 0){ ?>
    <div class="ppseries-warning"><?php esc_html_e('You have no series available to publish.', 'organize-series'); ?></div>
  <?php }
  ?>
</div>

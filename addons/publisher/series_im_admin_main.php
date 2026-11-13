<?php defined('ABSPATH') || exit;
?>
<div class="wrap">
  <h2><?php esc_html_e('Manage Series', 'organize-series'); ?></h2>

  <form method="get" action="<?php echo esc_url(admin_url('edit.php')); ?>">
    <input type="hidden" name="page" value="manage-issues" />
    <div class="tablenav top">
      <div class="alignleft actions">
        <label class="screen-reader-text" for="ppseries-series-post-count"><?php esc_html_e('Filter by post count', 'organize-series'); ?></label>
        <select name="series_post_count" id="ppseries-series-post-count">
          <option value="all" <?php selected($series_post_count, 'all'); ?>><?php esc_html_e('All series', 'organize-series'); ?></option>
          <option value="with-posts" <?php selected($series_post_count, 'with-posts'); ?>><?php esc_html_e('Series with posts', 'organize-series'); ?></option>
          <option value="empty" <?php selected($series_post_count, 'empty'); ?>><?php esc_html_e('Empty series', 'organize-series'); ?></option>
        </select>
        <?php submit_button(__('Filter', 'organize-series'), '', 'filter_action', false); ?>
      </div>
      <p class="search-box">
        <label class="screen-reader-text" for="ppseries-series-search-input"><?php esc_html_e('Search Series', 'organize-series'); ?></label>
        <input type="search" id="ppseries-series-search-input" name="s" value="<?php echo esc_attr($series_search); ?>" />
        <?php submit_button(__('Search Series', 'organize-series'), '', 'submit', false); ?>
      </p>
    </div>
  </form>

  <table class="widefat im_category_list wp-list-table fixed">
    <thead>
      <tr>
        <th scope="col" class="manage-column column-title column-primary"><?php esc_html_e('Series', 'organize-series'); ?></th>
        <th scope="col" class="manage-column"><?php esc_html_e('Series Order', 'organize-series'); ?></th>
        <?php if (!empty($can_publish_series)) : ?>
        <th scope="col" class="manage-column"><?php esc_html_e('Publish Series', 'organize-series'); ?></th>
        <?php endif; ?>
        <th scope="col" class="manage-column"><?php esc_html_e('Front View', 'organize-series'); ?></th>
      </tr>
    </thead>
    <tbody>
    <?php $alt = ' class="alternate"'; ?>
    <?php foreach ($series as $ser) : ?>
      <tr id="<?php echo esc_attr('cat-' . $ser->term_id); ?>"<?php echo $alt;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
        <td class="title column-title column-primary"><strong><a title="<?php /* translators: %1$s: Series name. */ echo sprintf(esc_html__('Edit the status of %1$s', 'organize-series'), esc_html($ser->name)); ?>" href="<?php echo esc_url(admin_url('edit-tags.php?action=edit&taxonomy=' . ppseries_get_series_slug() . '&tag_ID=' . (int)$ser->term_id)); ?>"><?php echo esc_html($ser->name); ?></a></strong></td>
        <td><?php
            echo "<a class='im-publish' href='" . esc_url(admin_url('edit.php?page=manage-issues&amp;action=part&amp;series_ID=' . (int)$ser->term_id . '')) . "'>" . esc_html__('Series order', 'organize-series') . "</a>";
        ?></td>
        <?php if (!empty($can_publish_series)) : ?>
        <td><?php
        echo "<a class='im-publish' href='" . esc_url(admin_url('edit.php?page=manage-issues&amp;action=list&amp;series_ID=' . (int)$ser->term_id . '')) . "'>" . esc_html__('Publish or schedule posts', 'organize-series') . "</a>";
        ?></td>
        <?php endif; ?>
        <td><?php
            echo "<a class='im-ignore' href='" . esc_url(get_term_link($ser->term_id)) . "'>" . esc_html__('View series in frontend', 'organize-series') . "</a>";
        ?></td>
      </tr>
        <?php $alt = empty($alt) ? ' class="alternate"' : ''; ?>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php if (count($series) === 0) { ?>
    <div class="ppseries-warning">
        <?php if ($series_search !== '' || $series_post_count !== 'all') { ?>
            <?php esc_html_e('No series found.', 'organize-series'); ?>
        <?php } else { ?>
            <?php esc_html_e('You have no series available.', 'organize-series'); ?> <a href="<?php echo esc_url(admin_url('/edit-tags.php?taxonomy=' . ppseries_get_series_slug() . '')); ?>"><?php esc_html_e('Add new Series.', 'organize-series'); ?></a>
        <?php } ?>
    </div>
  <?php }
    ?>
</div>

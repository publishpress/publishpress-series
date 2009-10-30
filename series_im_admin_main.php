<?php global $org_domain; ?>
<div class="wrap">
  <h2><?php _e('Manage Issues', $org_domain); ?></h2>
  
  <table class="widefat im_category_list">
    <thead>
      <tr>
        <th scope="col"><?php _e('Series Name', $org_domain); ?></th>
        <th scope="col"></th>
        <th scope="col"></th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
    <?php $alt = ' class="alternate"'; ?>
    <?php foreach ( $series as $ser ): ?>
      <?php
        if ( in_array($ser->term_id, $published) )
          $status = "published";
        elseif ( in_array($ser->term_id, $unpublished) )
          $status = "unpublished";
        else
          $status = "ignored";
      ?>
      <tr id="cat-<?php echo $ser->term_id; ?>"<?php echo $alt; ?>>
        <td><strong><a title="<?php echo sprintf(__('Edit the status of %1$s', $org_domain), $ser->name); ?>" href="<?php echo 'edit.php?page='.SERIES_DIR.'/orgSeries-manage.php&amp;action=edit&amp;series_ID='.$ser->term_id; ?>"><?php echo $ser->name; ?></a></strong></td>
        <td><?php
          if ( "published" == $status ) { echo "<strong>".__('Published', $org_domain) . "</strong>"; }
          else { echo "<a class='im-publish' href='?page=manage-issues&amp;action=list&amp;series_ID=$ser->term_id'>". __('Publish', $org_domain)."</a>"; }
        ?></td>
        <td><?php
          if ( "unpublished" == $status ) { echo "<strong>". __('Unpublished', $org_domain)."</strong>"; }
          else { echo "<a class='im-unpublish' href='?page=manage-issues&amp;action=unpublish&amp;series_ID=$ser->term_id'>". __('Unpublish', $org_domain)."</a>"; }
        ?></td>
        <td><?php
          if ( "ignored" == $status ) { echo "<strong>".__('Ignored', $org_domain)."</strong>"; }
          else { echo "<a class='im-ignore' href='?page=manage-issues&amp;action=ignore&amp;series_ID=$ser->term_id'>".__('Ignore',$org_domain)."</a>"; }
        ?></td>
      </tr>
      <?php $alt = empty( $alt ) ? ' class="alternate"' : ''; ?>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
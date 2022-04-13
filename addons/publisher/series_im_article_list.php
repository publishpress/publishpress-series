<?php
  $series = get_series( "orderby=name&hide_empty=0&include=$series_ID" );
  $posts = get_objects_in_term($series_ID, ppseries_get_series_slug() );
  $post_IDs = array();
  if ( $posts ) {
    foreach ( $posts as $post ) {
      $post_IDs[] = $post;
    }
  } else {
    $post_IDs[] = 0;
  }
  /*// take the series out of the unpublished list
  $unpublished_series = $unpublished;
  $key = array_search( $series_ID, $unpublished_series );
  if ( FALSE !== $key ) {
    array_splice( $unpublished_series, $key, 1 );
  }*/
  query_posts(array(
    "post__in" => $post_IDs,
    "posts_per_page" => -1,
	"post_status" => ['future', 'draft', 'pending']
  ));
?>
<div class="wrap pp-series-publisher-wrap">
<?php if ( have_posts() && isset( $series[0] ) ) : ?>
  <h1><?php _e('Publishing Series:', 'organize-series-publisher');?> <?php echo $series[0]->name; ?></h1>
  <p class="description"><?php _e('Drag the post names into the order you want them to be in the series, from the first part to the last part.', 'organize-series-publisher'); ?></p>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

    <div id="post-body-content" style="position: relative;">


    <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
            <tr>
                <th scope="col" id="title" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'organize-series-publisher'); ?></th>
                <th scope="col" id="authors" class="manage-column column-authors"><?php esc_html_e('Authors', 'organize-series-publisher'); ?></th>
                <th scope="col" id="preview" class="manage-column column-preview"><?php esc_html_e('Preview', 'organize-series-publisher'); ?></th>	
            </tr>
            </thead>
            <tbody class="im_article_list">
                <?php   
                while ( have_posts() ) : the_post();
                $post                = get_post(get_the_ID());
                $classes = 'iedit author-' . ( get_current_user_id() === (int) $post->post_author ? 'self' : 'other' );
                if ( $post->post_parent ) {
                    $count    = count( get_post_ancestors( $post->ID ) );
                    $classes .= ' level-' . $count;
                } else {
                    $classes .= ' level-0';
                }
                ?>
                <tr id="post-<?php echo $post->ID; ?>" class="<?php echo implode( ' ', get_post_class( $classes, $post->ID ) ); ?>" style="cursor: move;padding: 10px;">
                    <td class="title column-title column-primary">
                        <a href="<?php echo esc_url(admin_url('post.php?post='.get_the_ID().'&action=edit')); ?>"><?php the_title(); ?></a>
                    </td>
                    <td class="title column-authors">
                        <?php the_author(); ?>
                    </td>
                    <td class="title column-preview">
                        <a href="<?php echo esc_url(home_url('?p='.get_the_ID().'&preview=true')); ?>">
                            <?php esc_html_e('Preview', 'organize-series-publisher'); ?>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </div>
    
    <div id="postbox-container-1" class="postbox-container">
        <div id="side-sortables" class="meta-box-sortables ui-sortable" style="">
        <div id="submitdiv" class="postbox">
          <div class="postbox-header">
            <h2 class="hndle ui-sortable-handle"><?php _e('Publish Series', 'organize-series-publisher'); ?></h2>
          </div>
          <form id="im_publish_form" method="get" action="edit.php">
            <div class="hidden-fields">
              <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
              <input type="hidden" name="action" id="im_publish_action" value="publish" />
              <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo $series_ID; ?>" />
              <input type="hidden" name="posts" id="im_publish_posts" value="" />
            </div>
            <div class="inside">
              <div id="minor-publishing">
                <div id="misc-publishing-actions">
                  <div class="misc-pub-section misc-pub-section-last" style="margin:0;">
                    <p><?php _e('Publication Date/Time:', 'organize-series-publisher'); ?></p>
                    <div id='timestampdiv'>
                      <?php
                        global $wp_locale;
                        $time_adj = time() + (get_option( 'gmt_offset' ) * 3600 );
                        $jj = gmdate( 'd', $time_adj );
                        $mm = gmdate( 'm', $time_adj );
                        $aa = gmdate( 'Y', $time_adj );
                        $hh = gmdate( 'H', $time_adj );
                        $mn = gmdate( 'i', $time_adj );
                        $ss = gmdate( 's', $time_adj );
                        $month = "<select id=\"mm\" name=\"mm\">\n";
                        for ( $i = 1; $i < 13; $i = $i +1 ) {
                          $month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
                          if ( $i == $mm )
                            $month .= ' selected="selected"';
                          $month .= '>' . $wp_locale->get_month( $i ) . "</option>\n";
                        }
                        $month .= '</select>';
                        $day = '<input type="text" id="jj" name="jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off"  />';
                        $year = '<input type="text" id="aa" name="aa" value="' . $aa . '" size="4" maxlength="5" autocomplete="off"  />';
                        $hour = '<input type="text" id="hh" name="hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off"  />';
                        $minute = '<input type="text" id="mn" name="mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off"  />';
                        printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);
                      ?>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
              <div id="major-publishing-actions">
                <div id="publishing-action"><input type="submit" value="<?php _e('Publish Series', 'organize-series-publisher'); ?>" class="button-primary" id="publish" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.im_article_list tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('#im_publish_posts').val(im_post_IDs.join(','));alert(im_post_IDS);" /></div>
                <div class="clear"></div>
              </div>
            </div>
          </form>
        </div>

        </div>
    </div>

    </div>
    
    <br class="clear" />
  </div>
<?php elseif ( isset( $series[0] ) ): ?>
  <h2><?php echo sprintf(__('No pending posts in %1$s', 'organize-series-publisher'), $series[0]->name); ?></h2>
<?php else: ?>
  <h2><?php echo sprintf(__('Series %1$s does not exist', 'organize-series-publisher'), $series_ID); ?></h2>
<?php endif; ?>
</div>

<?php
  $series = get_series( "orderby=name&hide_empty=0&include=$series_ID" );
  $object_posts = get_objects_in_term($series_ID, ppseries_get_series_slug() );
  $post_IDs = array();
  if ( $object_posts ) {
    foreach ( $object_posts as $post_ ) {
      $post_IDs[] = $post_;
    }
  } else {
    $post_IDs[] = 0;
  }

  query_posts(array(
    "post__in" => $post_IDs,
    "posts_per_page" => -1,
	"post_status" => ['future', 'draft', 'pending']
  ));
?>
<div class="wrap pp-series-publisher-wrap">
<?php if ( have_posts() && isset( $series[0] ) ) : ?>
  <h1><?php esc_html_e('Publishing Series:', 'organize-series');?> <?php echo esc_html($series[0]->name); ?></h1>
  <p class="description"><?php esc_html_e('Drag the post names into the order you want them to be in the series, from the first part to the last part.', 'organize-series'); ?></p>
  <div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">

    <div id="post-body-content" style="position: relative;">


    <table class="wp-list-table widefat fixed striped table-view-list posts">
            <thead>
            <tr>
                <th scope="col" id="title" class="manage-column column-title column-primary"><?php esc_html_e('Title', 'organize-series'); ?></th>
                <th scope="col" id="authors" class="manage-column column-authors"><?php esc_html_e('Authors', 'organize-series'); ?></th>
                <th scope="col" id="preview" class="manage-column column-preview"><?php esc_html_e('Preview', 'organize-series'); ?></th>	
            </tr>
            </thead>
            <tbody class="im_article_list">
                <?php   
                while ( have_posts() ) : the_post();
                $post_item                = get_post(get_the_ID());
                $classes = 'iedit author-' . ( get_current_user_id() === (int) $post_item->post_author ? 'self' : 'other' );
                if ( $post_item->post_parent ) {
                    $count    = count( get_post_ancestors( $post_item->ID ) );
                    $classes .= ' level-' . $count;
                } else {
                    $classes .= ' level-0';
                }
                ?>
                <tr id="<?php echo esc_attr('post-' . $post_item->ID); ?>" class="<?php echo esc_attr(implode( ' ', get_post_class( esc_html($classes), (int)$post_item->ID ) )); ?>" style="cursor: move;padding: 10px;">
                    <td class="title column-title column-primary">
                        <a href="<?php echo esc_url(admin_url('post.php?post='.(int)get_the_ID().'&action=edit')); ?>"><?php the_title(); ?></a>
                    </td>
                    <td class="title column-authors">
                        <?php the_author(); ?>
                    </td>
                    <td class="title column-preview">
                        <a href="<?php echo esc_url(home_url('?p='.get_the_ID().'&preview=true')); ?>">
                            <?php esc_html_e('Preview', 'organize-series'); ?>
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
            <h2 class="hndle ui-sortable-handle"><?php esc_html_e('Publish Series', 'organize-series'); ?></h2>
          </div>
          <form id="im_publish_form" method="get" action="edit.php">
            <div class="hidden-fields">
              <input type="hidden" name="page" id="im_publish_page" value="manage-issues" />
              <input type="hidden" name="action" id="im_publish_action" value="publish" />
              <input type="hidden" name="series_ID" id="im_publish_series_ID" value="<?php echo esc_attr($series_ID); ?>" />
              <input type="hidden" name="posts" id="im_publish_posts" value="" />
            </div>
            <div class="inside">
              <div id="minor-publishing">
                <div id="misc-publishing-actions">
                  <div class="misc-pub-section misc-pub-section-last" style="margin:0;">
                    <p><?php _e('Publication Date/Time:', 'organize-series'); ?></p>
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
                        $publish_month = "<select id=\"mm\" name=\"mm\">\n";
                        for ( $i = 1; $i < 13; $i = $i +1 ) {
                          $publish_month .= "\t\t\t" . '<option value="' . zeroise($i, 2) . '"';
                          if ( $i == $mm )
                            $publish_month .= ' selected="selected"';
                          $publish_month .= '>' . $wp_locale->get_month( $i ) . "</option>\n";
                        }
                        $publish_month .= '</select>';
                        $publish_day = '<input type="text" id="jj" name="jj" value="' . esc_attr($jj) . '" size="2" maxlength="2" autocomplete="off"  />';
                        $publish_year = '<input type="text" id="aa" name="aa" value="' . esc_attr($aa) . '" size="4" maxlength="5" autocomplete="off"  />';
                        $hour = '<input type="text" id="hh" name="hh" value="' . esc_attr($hh) . '" size="2" maxlength="2" autocomplete="off"  />';
                        $minute = '<input type="text" id="mn" name="mn" value="' . esc_attr($mn) . '" size="2" maxlength="2" autocomplete="off"  />';
                        printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $publish_month, $publish_day, $publish_year, $hour, $minute);// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                      ?>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
              <div id="major-publishing-actions">
                <div id="publishing-action"><input type="submit" value="<?php esc_attr_e('Publish Series', 'organize-series'); ?>" class="button-primary" id="publish" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.im_article_list tr').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('#im_publish_posts').val(im_post_IDs.join(','));" /></div>
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
  <h2><?php echo sprintf(esc_html__('No pending posts in %1$s', 'organize-series'), esc_html($series[0]->name)); ?></h2>
<?php else: ?>
  <h2><?php echo sprintf(esc_html__('Series %1$s does not exist', 'organize-series'), (int)$series_ID); ?></h2>
<?php endif; ?>
</div>

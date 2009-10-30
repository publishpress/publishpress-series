<?php
  global $org_domain;
  $series = get_series( "orderby=name&hide_empty=0&include=$series_ID" );
  $posts = get_objects_in_term($series_ID, 'series' );
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
	"post_status" => 'pending'
  ));
?>
<div class="wrap">
<?php if ( have_posts() && isset( $series[0] ) ) : ?>
  <h2><?php _e('Publishing Series:', $org_domain); echo $series[0]->name; ?></h2>
  <div id="poststuff" class="metabox-holder has-right-sidebar">
    <div id="side-info-column" class="inner-sidebar">
      <div id="side-sortables" class="meta-box-sortables ui-sortable">
        <div id="submitdiv" class="postbox">
          <div class="handlediv" title="Click to toggle"><br /></div>
          <h3 class="hndle"><span><?php _e('Publish Issue', $org_domain); ?></span></h3>
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
                  <div class="misc-pub-section misc-pub-section-last">
                    <p><?php _e('Publication Date/Time:', $org_domain); ?></p>
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
                        printf(_c('%1$s%2$s, %3$s @ %4$s : %5$s|1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input'), $month, $day, $year, $hour, $minute);
                      ?>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
              </div>
              <div id="major-publishing-actions">
                <div id="publishing-action"><input type="submit" value="<?php _e('Publish Issue', $org_domain); ?>" class="button-primary" id="publish" name="publish" onclick="var im_post_IDs = new Array(); jQuery('.im_article_list li').each( function(){im_post_IDs.push(jQuery(this).attr('id').substring(5));});jQuery('#im_publish_posts').val(im_post_IDs.join(','));alert(im_post_IDS);" /></div>
                <div class="clear"></div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div id="post-body" class="has-sidebar">
      <div id="post-body-content" class="has-sidebar-content">
        <p><?php _e('Drag the post names into the order you want them to be in the series, from the first part to the last part. Keep in mind that any <strong>Draft</strong> posts that are a part of this series will not show up in this list and will not be published.', $org_domain); ?></p>
        <ul class="im_article_list">
          <?php while ( have_posts() ) : the_post(); ?>
           <li id="post-<?php the_ID(); ?>" style="cursor: move; background-color: #E4F2FD; padding: 0.25em;">
            <p class="title" style="font-weight: bold; margin: 0;"><?php the_title(); ?></p>
            <p class="author" style="padding-left: 2em; font-size: 90%; margin: 0;"><?php the_author(); ?> &raquo; Category: <?php the_category(', ');?></p>
          </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
    <br class="clear" />
  </div>
<?php elseif ( isset( $series[0] ) ): ?>
  <h2><?php echo sprintf(__('No pending posts in %1$s', $org_domain), $series[0]->name); ?></h2>
<?php else: ?>
  <h2><?php echo sprintf(__('Series %1$s does not exist', $org_domain), $series_ID); ?></h2>
<?php endif; ?>
</div>
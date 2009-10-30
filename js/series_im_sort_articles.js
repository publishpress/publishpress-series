jQuery(document).ready( function($) {
  /*im_update_post_order();*/
  $(".im_article_list").sortable({
    axis: "y",
});
 
 function im_update_post_order() {
  var im_post_IDs = new Array();
  jQuery(".im_article_list li").each( function() {
    im_post_IDs.push(jQuery(this).attr('id').substring(5));
  });
  jQuery("#im_publish_posts").val(im_post_IDs.join(','));
}

});
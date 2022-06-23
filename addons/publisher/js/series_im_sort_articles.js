jQuery(document).ready( function($) {
  /*im_update_post_order();*/
  /*$(".im_article_list").sortable({
    axis: "y",
  });*/
  
  $(".pp-series-publisher-wrap.series-order table.series-parts tbody").sortable({
    axis: "y",
  });
  
 function im_update_post_order() {
  var im_post_IDs = new Array();
  jQuery(".im_article_list tr").each( function() {
    im_post_IDs.push(jQuery(this).attr('id').substring(5));
  });
  jQuery("#im_publish_posts").val(im_post_IDs.join(','));
}

});
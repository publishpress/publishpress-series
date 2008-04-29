<?php
/*
Template Name: OrgSeries Table of Contents

This file is used when displaying the table of contents for your Series related content.  What is a series table of contents?  Simply a listing of all the series you've written.  

To customize the look and feel of the Series Table of Contents, follow these steps.  (You'll probably need a good understanding of HTML and CSS!

1. Make a copy of this file and place it into your current active theme's directory (../wp-content/themes/[active theme name/seriestoc.php)
2. Go to the series options page and change the Series Table of Contents Listings template code to match how you want the series listings on the page to be layed out (using the tokens provided).
3. Modify the .css in your active theme/orgSeries.css to your liking.
4. That's it!

Troubleshooting:
Not all WordPress themes are created equal, so default look and feel might look kind of wierd on your setup.  If that is the case, try looking at your theme's "index.php" and replace anything between and including <?php if (have_posts()); ?> AND <?php endif; ?>  WITH <?php wp_serieslist_display(); ?>
*/

get_header();
?>
<div id="content" class="narrowcolumn">
	<?php wp_serieslist_display(); ?>
</div>

<?php

//comment the following if you don't want a sidebar on your Series TOC
get_sidebar();

get_footer();
?>
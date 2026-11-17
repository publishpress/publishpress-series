---
title: "Create a Table of Contents for Categories"
order: 23
doc_category: series
---

# Create a Table of Contents for Categories

Table of Contents

- ['.$group->name.'](#group-title-'.$group->term_id.')

Out-of-the-box, the [PublishPress Series](https://publishpress.com/links/series) Categories feature does not provide any frontend templates for displaying the series group information. This document gives some basic instructions for how to create a Table of Contents listing all your series categories and the series in each category.

There are many ways that this could be done but for this tutorial, we’re going to go with a page template. You can learn more about [WordPress page templates on the WordPress codex](http://codex.wordpress.org/Pages#Creating_Your_Own_Page_Templates) and a read of that will be a good start for this tutorial.

Every page template has common code with whatever is found in the *page.php* file in your theme. Here’s the opening few tags in the one we’re using for our example. **NOTE: this is only a snippet of the relevant code in a template, not the entire thing**.

```
<?php

/*  Template Name: OrgSeries Grouping Table of Contents */

?>

<?php get_header(); ?>

<div id="contentwrap">
<div id="content">
<div id="content-area">
```

Notice that I’ve called the Page Template “OrgSeries Grouping” this is so when I create the page I can easily tell that this is the template for the Table of Contents.

Next up is the actual series grouping code. We’ll step through this bit by bit:

```
$args =  array('include' => array( '22', '6' ), 'order' => 'DESC' );
$get_series_args =  array( 'hide_empty' => 1);

$groups = get_series_groups( $args );
```

Notice that I’m setting a `$groups` variable and assigning the results of the `get_series_groups` function to that variable. `get_series_groups()` is a function used to get a list of groups data. Notice that I set the `$args` variable to include groups with the id’s of ’22’ and ‘6’. These are the id’s for the “Setup” and “Usage” series group. So what I’m telling the code is I want to get the information on the Usage Groups and Series Groups and have that info returned in a *descending order.*The `$get_series_args` variable will come in later when we start to call the series that belong with each group. Up next:

```
foreach ( $groups as $group ) { 
	echo '<div>'; 
	echo '<h3 id="group-title-'.$group->term_id.'">'.$group->name.'</h3>'; 
	$get_series = get_series_in_group($group->term_id, $get_series_args); 
	$series_args =  array( 'include' => $get_series, 'order' => 'DESC' );
	wp_serieslist_display(false, $series_args); 
	echo '</div>'; 
}
```

What we are doing here is beginning to output the layout of group and series information on the page by looping through each group object that we obtained via `get_series_groups()`. Notice that we are also using the `get_series_in_group()` function to get a list of series that are found in the current group in the loop. I’m sending to that function the `$get_series_args` that I set earlier. Then I’m using the array of series ids returned using the `get_series_in_group()` function to set the `$series_args` for sending to the `wp_serieslist_display()` template function. This function is from the core Series plugin and will output the information for each series in `$get_series` according to the information set in the *Series Table of Contents Listing* *Template* on the Series Options page. Finally,

```
</div> <!-- end #content-area --> 
<?php get_sidebar(); ?> 
<?php get_footer(); ?>
```

This closes off the template with the code that matches the theme’s page.php page.

You can view this example template in its entirety [here](https://gist.github.com/nerrad/ad44761d77a01dc05e80ae6c20bd8d5b). Remember that this is specific to a given theme so you will need to adjust the HTML container structure to work with your active theme.

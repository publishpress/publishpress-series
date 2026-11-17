---
title: "Create a Custom List of Series"
order: 2
doc_category: series
section: Series for Developers
---

# Create a Custom List of Series

Table of Contents

- [Introducing wp_list_series](#0-toc-title)
- [The Parameters for wp_list_series](#h-the-parameters-for-wp-list-series)
- [Usage of wp_list_series](#h-usage-of-wp-list-series)

The `wp_list_series` function included with [PublishPress Series](https://publishpress.com/links/series) allow you to output a list of all the series you’ve written.

## Introducing wp_list_series [#](#0-toc-title)

The usage of `wp_list_series` is to insert this code:

```
<?php 
if ( function_exists('wp_list_series') ) { 
    wp_list_series();
}
?>
```

However, the real control comes in when you submit various parameters via the function. Here is an expanded function, based on `wp_list_series`:

```
$defaults =  array(
    'orderby' => 'name',
    'order' => 'ASC', 'show_last_update' => 0,
    'style' => 'list', 'show_count' => 0,
    'hide_empty' => 1, 'use_desc_for_title' => 1,
    'feed' => '', 'feed_image' => '', 'exclude' => '',
    'title_li' => __('Series'),'number' => '',
    'echo' => 1
);
```

By default, `wp_list_series()` outputs a list…

The `wp_list_series` function included with [PublishPress Series](https://publishpress.com/links/series) allow you to output a list of all the series you’ve written.

The usage of `wp_list_series` is to insert this code:

```
<?php 
if ( function_exists('wp_list_series') ) { 
    wp_list_series();
}
?>
```

However, the real control comes in when you submit various parameters via the function. Here is an expanded function, based on `wp_list_series`:

```
$defaults =  array(
    'orderby' => 'name',
    'order' => 'ASC', 'show_last_update' => 0,
    'style' => 'list', 'show_count' => 0,
    'hide_empty' => 1, 'use_desc_for_title' => 1,
    'feed' => '', 'feed_image' => '', 'exclude' => '',
    'title_li' => __('Series'),'number' => '',
    'echo' => 1
);
```

By default, `wp_list_series()` outputs a list…

- ordered by the names of the series
- in ascending order
- that does not show the date/time of the last updated post in the series.
- outputted in “list” style (i.e. with <li> and </li> surrounding each series item.
- that does not show the number of posts in the series.
- hiding series that do not have posts
- using the description of the series as the “title” attribute for the link to each series.
- with no feed or feed image used
- not excluding any series
- displaying “Series” as the heading over the list,
- with no “limit” set for the number of series pulled from the database, and,
- echoed (i.e. displayed) instead of just returning the output (not displaying).

## The Parameters for wp_list_series [#](#h-the-parameters-for-wp-list-series)

Here’s a more detailed summary of each parameter:

`orderby` – (string) Sort categories alphabetically, by unique Series ID, or by the count of posts in that Series. The default is sort by series name. Valid values:

- ID
- name – default
- count

`order` – (string) Sort order for series (either ascending or descending). The default is ascending. Valid values:

- ASC – default
- DESC

`show_last_updated` – Should the last updated timestamp for posts be displayed. Defaults to FALSE.

- 1 (true)
- 0 (false) – default

`style` – (string) Style to display the series list in. A value of list displays the series as list items while none generates no special display method (the list items are separated by <br>tags). The default setting is list (creates list items for an unordered list). Valid values:

- list – default.
- none

`show_count` – (boolean) Toggles the display of the current count of posts in each series. The default is false (do not show post counts). Valid values:

- 1 (true)
- 0 (false) – default

`hide_empty` – (boolean) Toggles the display of series with no posts. The default is true (hide empty series). Valid values:

- 1 (true) – default
- 0 (false)

`use_desc_for_title` – (boolean) Sets whether a series’ description is inserted into the `title` attribute of the links created (i.e. <a title=”<em>Series Description</em>” href=”…). The default is true (series descriptions will be inserted). Valid values:

- 1 (true) – default
- 0 (false)

`feed (string)` – Display a link to each series’ RSS feed and set the link text to display. The default is no text and no feed displayed.

`feed_image` – (string) Set a URL for an image (usually an RSS feed icon) to act as a link to each series’ rss-2 feed. This parameter overrides the feed parameter. There is no default for this parameter.

`exclude` – (string) Exclude one or more series from the results. This parameter takes a comma-separated list of series by unique ID, in ascending order.

`title_li` – (string) Set the title and style of the outer list item. Defaults to “_Series”. If present but empty, the outer list item will not be displayed.

`number` – (integer) Sets the number of Series to display. This causes the SQL LIMIT value to be defined. Default to no LIMIT.

`echo` – (boolean) Show the result or keep it in a variable. The default is true (display the series organized). Valid values:

- 1 (true) – default
- 0 (false)

CSS selector : `.current-series { ... }`

## Usage of wp_list_series [#](#h-usage-of-wp-list-series)

Let’s give an example of using `wp_list_series`.

In this example, you want to show the three most recent series you’ve posted, with the number of posts in each series indicated, not including the crappy “101 ways to watch paint dry” series that you wrote.

Go to the “Posts” > “Manage Series” page in your WordPress site, you find that the series ID of your “101 ways to watch paint dry” series. In this example, we’ll used an ID of 33.

Equipped with this information here’s the code you would write:

```
wp_list_series('orderby=id&order=DESC&show_count=1&exclude=33&number=3');
```

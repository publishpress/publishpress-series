---
title: "Getting Groups Data in Template Files"
order: 4
doc_category: series
section: Series for Developers
---

# Getting Groups Data in Template Files

Table of Contents

- [get_series_in_group](#h-get-series-in-group)[Parameters](#h-parameters)[Usage](#h-usage)
- [get_groups_in_series](#h-get-groups-in-series)[Parameters](#h-parameters-1)[Usage](#h-usage-1)
- [get_series_groups](#h-get-series-groups)[Parameters](#h-parameters-2)[Usage](#h-usage-2)
- [get_series_group_list](#h-get-series-group-list)[Parameters](#h-parameters-3)[Usage](#h-usage-3)

In this guide we’re going to walk through all the different template tags available for showing [PublishPress Series](https://publishpress.com/links/series) Categories.

All of the listed functions can be found in the `orgseries-grouping-template-tags.php` file.

## get_series_in_group [#](#h-get-series-in-group)

This will return all the series in a specified group:

```
get_series_in_group($group_id, $args)
```

#### Parameters [#](#h-parameters)

- `$group_id:` You will need to provide the group_id for the group you want a list of series from.
- `$args:` You can control what gets returned with the series information through the array you provide here. For more information on the options see the `get_terms()` function found in the “/wp-includes/taxonomy.php” within your WordPress directory.

#### Usage [#](#h-usage)

You could use this to get the series data for a particular group and then loop through the array (or object, depending on the $args) to output the list however you needed.

## get_groups_in_series [#](#h-get-groups-in-series)

This will return the groups a series belongs to.

```
get_groups_in_series($series_id)
```

#### Parameters [#](#h-parameters-1)

- `$seriesid` – You must include the series id for the series you’d like to get the groups for

#### Usage [#](#h-usage-1)

This will be helpful if you want to need to get the groups a series belongs to for assembling and displaying this data with series meta data.

## get_series_groups [#](#h-get-series-groups)

This will return the ID s for all the groups (categories) that contain series.

```
get_series_groups($args)
```

#### Parameters [#](#h-parameters-2)

`$args(array)`: This allows to control the order and content for the array of groups that are returned. Here are some options that can be affected by this array

- `orderby` = Default is ‘id’, Can be id, name, or slug.`order` – Default is ASC. Can use DESC.
- `exclude` – Default is empty array. An array, comma- or space-delimited string of term_ids (groups) to exclude from the returned array. If ‘include’ is non-empty, ‘exclude’ is ignored.
- `include` – Default is empty array. An array, comma- or space-delimited string of term_ids to include in the return array. If both include and exclude are empty then all the groups containing series will be returned.
- `number` – The maximum number of groups to return. Default is to return them all.
- `offset` – The number by which to offset the groups query.
- `fields` – Default is ‘all’, which returns an array of group objects (containing id, name, slug); This can be set to just return ‘ids’, just ‘names’, or just ‘slugs’ OR you can ask for a count of all the series_groups. with ‘count’

#### Usage [#](#h-usage-2)

This function would be helpful for starting to assemble a groups “table of contents page” where you can output all the groups with the series in those groups. Remember this is just returning the data, NOT the final display code.

## get_series_group_list [#](#h-get-series-group-list)

This is used to output an unordered list containing a group title followed by the list of series in that group. If no group_id is included then the function will return all the groups with the series in each group. If group_id(s) are included then the groups requested will be returned. Can also choose to “exclude” certain groups from the list. This template tag (function) is the first of what will be other assembly type functions for outputting the group data on your blog.

```
get_series_group_list($group_id, $args, $echo)
```

#### Parameters [#](#h-parameters-3)

- `$group_id`: can be just a string|int containing the id of one group that you want to return the list for OR it can be an array with four paramaters:  `include` (array, comma, or space deliminated list of group_ids to include) `exclude` (array, comma, or space deliminated list of group_ids to exclude) `orderby` (id=default, name, or slug) `order` (asc=default/desc)
- `$args` (optional): you can indicate how you want the series list in each group to be returned. For a list of what args you can include see the `get_terms()` function found in `/wp-includes/taxonomy.php`.
- `$echo` – If set at “true” then the function will echo the results. If “false” then the function will just return the results. Default is true.

#### Usage [#](#h-usage-3)

This is a function that will provide a quick way of getting a list of series in a group (or list of series in all many groups) and displaying that where the function is used.

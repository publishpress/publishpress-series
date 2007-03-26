=== Organize Series WordPress Plugin ===
Contributors: nerrad
Tags: category, series, organize, post
Requires at least: 2.0
Tested up to: 2.1.2
Stable tag: 1.6.1

A plugin for managing the article series you write.

== Description ==

The *Organize Series plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series. Used with the [Category Icons Plugin](http://devcorner.georgievi.net/wp-plugins/wp-category-icons/) (optional and not required) by Ivan Georgiev you can even add attractive graphics/logos (designed by yourself or someone else of course) to accompany your series. 

== Installation ==

For installation, support, change-log,  and usage instructions visit the [Plugin Page on UnfoldingNeurons.com](http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/).

== Screenshots ==

1. Organize Series Options page

== Features ==

* Integrates with the category system of WordPress
* Robust administration options menu for the plugin that allows you to control much of the output of the plugin to suit your own tastes without having to open the plugin file and edit things manually. Of course, for those less familiar with html tags and .css the default options will let you "drop in" and go (with only one caveat - you do have to set what the category id is for your main series category - more in the installation/usage instructions below).
* Automatic tag insert for displaying a "container" on the full post page that will show what series it belongs to and other articles in the series. Of course, via the options panel in the wordpress admin, you can choose to disable the auto-tag if you wish to have more control over where the container is displayed (i.e. in the sidebar instead).
* A custom .css file (orgSeries.css) accompanying the plugin to make it easier to make changes in the display of various elements (for those familiar with .css).
* An example custom category template file that can be added to your template themes directory (with one modification) to create the custom list of all the series you’ve written. The output of this list of course can be controlled via the options panel.
* Two simple tags for easy insertion in your theme if you want to have more control over the way your series are presented.
* Makes managing and viewing the series you write on your blog a whole lot easier.

**NEW FEATURES AS OF 1.5**

* Added a function/tag for counting the number of posts in a series. (tag returns a value). See usage instructions on the [plugin page](http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/) (it is automatically included in the auto-tag insertion for series meta information)
* Added a function for writing series meta information ("This entry is part x of x in the series, "The name of my series") to the posts that belong to a series. This is automatically added by default to your blog but it can be disabled for manual insertion via the new option on the options page for the plugin. See usage instructions on the [plugin page](http://www.unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin/).
* Added options for the html tags surrounding the series meta and for the post description word. Added to the options page for the plugin.
* Redid the layout of the admin options page for the plugin so it’s organized a bit better and a "little" bit more prettier. I recognize that more work still needs to be done of course ;) .
* Added to the admin options page for the plugin a feed from this blog that displays posts related to the Organize Series Wordpress Plugin so that users can see at a glance if there are any updates available.

**New Features as of 1.6**

* New Template Tag - in_series() - this will check to see if the displayed category archive is a series category.
* Can set the way the posts will be displayed on the series "tables of contents" page (category archive for a series category) via the plugin options panel. (for example, have the posts ordered by date, title, author etc. - ascending or descending). NOTE: It appears that the settings will only apply when using WordPress 2.1+ - it doesn’t seem to apply on a blog that I have that is WP 2.0+, I’m still investigating this.
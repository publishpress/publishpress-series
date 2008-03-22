=== Organize Series WordPress Plugin ===
Contributors: nerrad
Tags: category, series, organize, post
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 1.6.3

A plugin for managing the article series you write.

== Description ==

The *Organize Series plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series. 

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

Make sure you check out the features tab for all the neat stuff you can do!

== Installation ==

**NOTE: For upgrading from earlier versions of Organize Series (1.6.3 and earlier) make sure you check out the upgrade tab. These instructions are for fresh installs of Organize Series.**

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. The Series-Icons feature uploads to the default upload directory you've set for your WordPress installation (in Options/Settings->Miscellaneous).  If you want to use series-icons its **important** that you uncheck the "Organize my uploads into month- and year-based folders" box.  

That's it!  You might want to check out the Series Options page (in the "settings" menu of WP 2.5 or "options" in earlier WP versions) and the Manage->Series Page.  You'll also notice that your "write/edit" posts page now has a handy dandy series box on it for adding your posts to a series (and creating new series on the fly).

For support please visit [the support forums](http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin "For help click here!").

== Upgrading ==
If you are visiting this tab then you are probably a user of one of the older versions of Organize Series and have been waiting for me to finally get something out the door that is compatible with WP 2.3+ First, thanks for being patient - I've been frustrated myself with the time it's taken but I think it's been worth it!  The good news is that you can import all your series structures from your old setup into the new series taxonomy!!  Just follow these instructions carefully.

1. Backup your WordPress database (especially if you plan on importing old series data - everything has been tested but I simply have no way of testing for every environment.  So be safe, and you won't chafe)
1. If it isn't already, deactivate the older version of Organize Series.
1. If you haven't already, upgrade your blog to the latest WordPress version.
1. Make sure your other plugins are up to date EXCEPT for the category-icons plugin if you are using it.  If you plan on importing all your old series-icons then it is important that you leave the older version of category-icons (version 1.5) activated until the import is complete.
1. Delete the old Organize Series Plugin from your server (in folder /orgSeries/).  Copy over the new plugin files (as explained via the installation instructions).
1. Activate Organize Series
1. Go to the Options/Settings->Series Options page.  If you are upgrading and have old series to import there should be a form at the top of the page with instructions for importing all that data.  Follow the instructions
1. The import routine will return a message outlining all the series and posts that were imported.  After that's done go and check out all the new stuff (and make sure all your series are correct (check out the new Manage->Series page!)
	
For support please visit [the support forums](http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin "For help click here!").

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

**1.6.3**
* Fixed incorrect link in admin panel for Docs/Help
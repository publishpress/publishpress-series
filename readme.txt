=== Organize Series  ===
Contributors: nerrad
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7871313
Tags: category, series, organize, post, taxonomy
Requires at least: 3.0
Tested up to: 3.1
Stable tag: 2.2.9

A plugin for managing the article series you write.

== Description ==

The *Organize Series WordPress Plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series. 

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

Make sure you check out the [features section in the "other notes" tab](http://wordpress.org/extend/plugins/organize-series/other_notes/) for all the neat stuff you can do!

**As of version 2.2, Organize Series *requires* WordPress 3.0+ to work. Organize Series also requires PHP 5.2+.**

**IMPORTANT LINKS**

[Author's Website](http://unfoldingneurons.com) | [OrganizeSeries.com](http://organizeseries.com) | [OrgSeries ADDONS](http://organizeseries.com/download/) | [OrgSeries Twitter](http://twitter.com/organizeseries)

== Installation ==

**NOTE: - As of version 2.2, Organize Series *requires* WordPress 3.0+ and PHP 5.2+ to work.**
**ALSO**: If you are upgrading make sure you read the upgrade notes as there are a couple of changes for templates.

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File (or use the built-in updater provided by WordPress)
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. The Series Icon Feature uses the built in Media Uploader/picker in WordPress.  

You can do the above or just use the new plugin install integrated in WordPress.

= Upgrading =
If you are upgrading from Organize Series 2.1 or above you can follow the same steps as above.
Other UPGRADE Notes:

1. Your customized `seriestoc.php` file should still work for Series Table of Contents Pages.
1. Rename your `series.php` file (series archive pages) to `taxonomy-series.php`  - starting with version 2.2, Organize Series integrates with the built-in taxonomy template pages that WordPress provides.  You can also customize a particular series by creating a page titled `taxonomy-series-{series-name}.php` where 'series-name' is the name of the particular series you want a different archive page/look for.
1. Organize Series Publisher is NO LONGER bundled with Organize Series.  Don't worry, you can still get it, it's available on the WordPress plugin repository.  Look for [organize series publisher](http://wordpress.org/extend/plugins/organize-series-publisher/).

= Other Notes =
If you customized the included `seriestoc.php` file (for Series Table of Contents Page) to fit your site theme better make sure you copy the file over to your theme directory so future upgrades of Organize Series won't overwrite your customizations.

That's it!  You might want to read through the Series Options page (as a submenu item under 'options') and the Manage->Series Page.  You'll also notice that your "write/edit" posts page now has a handy dandy series box on it for adding your posts to a series (and creating new series on the fly).

== Frequently Asked Questions ==

**What does Organize Series do?**
It makes it easier for you to write a series of posts on your blog/website and have them automatically linked together.

**So?**
Well, one of the best ways to build an audience on your blog/website is to write short posts about the same subject over a period of time rather than one long post.  There are numerous SEO benefits to this as well.  Organize Series takes the hassle out of managing the serial posts and you can just focus on writing.  Another way of saying this is, Organize Series takes the work out of organizing series so that you write more and your readers can EASILY follow along.

**Can I customize the archive page for series?**
You sure can!  Just create a file called `taxonomy-series.php` and copy the code from your themes `archive.php` file and use that as the basis for building out how the series archive page looks.  You'll also want to take a look at all the template tags you can use for series in the `orgSeries-template-tags.php` file.  

**But I want to customize the archive page for a *single* series, can I do that?**
Yup.  Create a file named `taxonomy-series-{series-slug}.php` file and replace `{series-slug}` with the slug of your series and you'll be good to go!

**Hang on, what I really want is to create a page that lists all the series available. You probably don't have that available do you?**
Hey, I've got you covered there as well!  In the `organize-series` folder you'll see a file called `seriestoc.php`.  It's what gets loaded when you go to your series toc url (defaults at \series\ but you can change this on the series options page).  Chances are though it doesn't look great with your theme (the file included just gives you an idea of what you can put in the file). What you want to do is copy this file to your theme folder and then modify it to match your theme structure (see your themes archive page for an example).  You don't need to put any of the WordPress loop code in this file.

**Wow there are sure a lot of features and ways of customizing the Organize Series plugin.  However I'm wondering can you put in ___________ feature?**
Over the last couple of years I've received numerous requests for extra features for Organize Series and have done some custom development work for clients who have the resources to hire me to add stuff.  I simply have not had the time to write up all the features that people request without getting paid for it. Here's the thing though, there's been some really good ideas and I realize that not everyone is able to afford what it costs for custom development.  So I decided what I'd do is move to a pay-as-you-go model for new features.  I've created a website for [Organize Series](http://organizeseries.com) where you'll find all kinds of different add-ons to Organize Series that you can purchase and use along with the core plugin. You'll want to head on over there and see if there's anything you are looking for.  I've priced the add-ons at a low price so new features are more accessible to those of you who can't afford custom development work and yet still provides a way for me to get paid for the work I've done.  It's a win-win!!  You'll also want to check out the basic-support package available as well.

If you don't see an add-on that has the feature you want for Organize Series, write up a request here on the [WordPress Support Forums](http://wordpress.org/support) (make sure you tag the post with organize-series) and I might just develop it as the next add-on!!

**Sounds like you are going to be moving to a paid model for Organize Series...does that mean I'll eventually have to pay for all the goodness Organize Series offers?**
Nope.  The CORE Organize Series plugin WILL ALWAYS REMAIN FREE.  The only thing that I'm charging for going forward is support and the add-ons.  
This will also provide extra incentive for me to keep the core plugin *rock'in* for users and always up to date with the current version of WordPress.  I will also work hard at fixing any bugs etc. that get discovered.  The core plugin has EVERYTHING a user needs to organize series on their blog and that will never change (i.e. I won't remove features from the core).  However, from time to time there are users who want something extra (e.g. the ability to "group" series) and thats where add-ons come in.  From time to time I will release free add-ons as well, you'll want to bookmark [OrganizeSeries.com](http://organizeseries.com) so you'll catch the news when that happens.

Oh and one more thing.  Organize Series will always be GPL and so will ALL the add-ons that I write.  Just thought I'd put that in here.



== Support ==

There have been some changes to how I provide support for Organize Series. Here are the different options for support:

1. Post any support questions on the WordPress Forums at [wordpress.org](http://wordpress.org/support). I will keep an eye on the forums for bug reports but will not be actively responding to usage/installation help.

1. Subscribe to the Paid Basic Support Plan I am offering for Organize Series at [organizeseries.com](http://organizeseries.com).  This will give access to the private support forums that I will be active on.

1. You can also get support from the [WordPress Help Center](http://wphelpcenter.com). This is a service I have partnered with and when you pay for support from them I receive a commission so it's a way of contributing towards the work I do.  When contacting them let them know my affiliate code which is *14322*.

== Screenshots ==
1. Organize Series Options Page:  There are so many possibilities for how Organzie Series can be customized. Nearly everything can be done on the built-in options page.

2. Manage Series Page:  All your series can be edited via the integrated "Manage Series" page.

3. Also, via the "manage series" page, users can attach images to serve as a "series icon" for their series.  This is fully integrated with the built-in media uploader of WordPress.

4. When writing a new post, you can use the Series Box to attach the post to a series and choose what part that post will be in the series. You can also create a new series on the fly if needed.

5. Organize Series fully integrates with the Edit Posts page in WordPress.  In this screenshot you can see the custom column that lists any series information attached to a post.

6. Another feature with the integration into the Edit Posts page is the ability to filter the posts shown by series.

7. You can also use the "Quick-Edit" WordPress feature to edit series information for posts.

8. Organize Series inserts all your series information into the "Right Now" Dashboard widget.

9. Here's the screenshot for the Series Widget Settings...

10. ...and the screenshot of how it looks on the blog (on a page that is part of a series).

11. The screenshot of the Latest Series widget settings box...

12. ...and the screenshot of the results on the blog.

13. What the series-meta information strip looks like (looks can be customized).

14. What the list of posts in the series looks like (also can be customized).

== Features ==

The following is a brief overview of all the features in Organize Series.

#### Integration with WordPress taxonomy system
Beginning with version 2.3, WordPress introduced a database and core change that resulted in a new taxonomy system.  The core WordPress has two taxonomies - "categories" and "tags".  Organize Series introduces a new taxonomy "series".  Thanks to the rich api provided to plugin authors Organize Series takes advantage of (as much as possible) the built-in WP goodness!  As an added bonus - plugin developers can expand on what Organize Series offers by interacting with the new series taxonomy and the built-in filters/hooks.

#### More "organize" for taking care of your series...
Check these additions to your WordPress administration:

+	A "Manage Series" page.
  Add, delete, edit all your series on one handy dandy page.  Functioning much like the "Manage Category" page, Organize Series makes it easy to keep track of all the series you are writing.
+	Integration with the Edit->Posts page.
	Now it's easier than ever to keep track of what posts have been added to series in a familiar setting.  Even better, you can also filter the post list by series giving you a quick way to see all the posts you've already added to the series.
	Organize Series also integrates with the "quick-edit" feature - so you can edit series information for posts right on this page.
  
#### Add "prettify" recognition to your series...
Easily associate images with your Series via the Manage->Series page.  Images can be easily uploaded, picked using the built-in WordPress Media uploader.
 
#### Integration with Write/Edit posts page
Organize Series brings a new "box" to your write/edit posts page for series related tasks:

* Add new series on the fly.
 Ajaxified and simple to use (works pretty much the same as adding new categories on the fly)
* Add posts to existing series.
* Choose what order you want that post to be in the series.  If you leave the field blank, your post will automatically be appended to end of the series.  But if you want you can number that post to be whatever part you want *and every other post in the series will be automatically adjusted for their new parts*  Is that cool? Yeah, I thought so too!
 
#### Works out of the box.
Once you've activated the plugin (and are not importing from earlier versions) all you have to do is visit the Series Options page and then you're ready to go (you don't have to change/or add anything!).  Out of the box, Organize Series defaults to automatically insert into your blog all the necessary "template tags" for displaying series related information. 

Use permalinks?  No problem, Organize Series detects that and introduces a permalink structure for series archive pages (defaults to http://yourblogaddress.com/series/%the_series_slug%).
 Don't like the default settings/look...well...
 
#### Complete options page for easy customization *without having to edit any .php files* 
Nearly everything you might want to customize with Organize Series can be done on the Series->Options page:

* Control placement of series related info (Series Meta, Series Table of Contents, Series Postlist boxes, Series Navigation Strip, etc.)
* Customize the html output or series related info by using the %token% system with a token legend right on the page (check out the screenshots tab).  
* Control what the path of your "series table of contents" link will be.
* Control how posts in a series will be displayed on series archive pages.  The default is by date in descending order (newest to oldest) - but you can order it by part too.
* Set the icon width for images associated with series for different pages.
 
Pretty cool eh?  But maybe you'd like to be able to customize things even more?...
 
#### A whole bundle of template tags for theme authors and WP tweakers
Do you actually *like* going into the guts of your theme files?  Then, you'll love the "template tags" organize series makes available for you!  Better yet, I've made it easy for you to find them:  All the template tags you'll ever need are found in the `series-template-tags.php` file in your Organize Series plugin directory.  Each tag is also fully documented inline so you can have a better idea what it does!

You can also edit the default .css file for orgSeries OR point to your custom .css in `styles.css` for your theme.

Oh, if you implement Organize Series in a cool way [let me know here](http://organizeseries.com/contact/)

Oh and don't forget...

#### Widgets!! There are two built-in widgets that you can use to display all your series information.  

FINALLY...
#### Organize Series Addons
Organize series now has [it's own website](http://organizeseries.com) where you can purchase additional addons that bring additional functionality to the core plugin. I'm releasing as many addons as I can based on features users have requested that I don't have the time to develop for free.  Providing this add-on marketplace enables me to bring the features that have been requested to users of Organize Series at an affordable price.  You'll want to check out the Basic Support package as well.

== Usage ==
All usage instructions are available at [OrganizeSeries.com](http://organizeseries.com). 

Have fun - and get writing those series!!

== Upgrade Notice ==

= 2.2 =
This version is a major update to the plugin and introduces big changes to the core code and structure of the plugin.  Care has been given to make the upgrade as seamless as possible for users of previous versions but you will definitely want to read over the changelog to see if there are any changes that will impact you.

*AS ALWAYS - BACKUP YOUR BLOG FIRST BEFORE UPGRADING!!*


== Changelog ==

= 2.2.9 =

Bugfix release AND full compatibility check with WordPress 3.1

* Organize Series is fully compatible with the upcoming version 3.1 of WordPress.

* Fixed a bug with the ajax form in the series meta box on the edit-post.php page.  When creating a new series using the form the new series would be created BUT not automatically selected.  Also you wouldn't be able to attach the post to the new series until the page was reloaded.  This is fixed.

* The Series dropdown in the SeriesTOC widget would not redirect to a series archive page when a series is selected (in certain cases).  This has been fixed.

* Some other bug fixes for certain cases where a "FATAL ERROR..." message would appear.

* a naming convention change.  On WordPress version prior to 3.1 you will see the name of the menu item for the Manage Series page as "Series".  With WordPress 3.1 this will go back to "Manage Series" which is what it was prior to this release.  The reason for the difference is I'm using a new paramater available for the taxonomy api (menu_name) so I can clean up what the General Taxonomy name is (from "Manage Series" to "Series").  This WILL affect any users who have localization files.  If you start using Organize Series 2.2.9 with the 3.0 branch of WordPress make sure you revisit your translation files once you upgrade to WordPress 3.1 to make sure all the translations are correct.

= 2.2.8 =

minor bugfix (not a necessary update unless you are using any premium addons for Organize Series)

* Automatic Upgrades framework for addons had a bug that surfaced in certain cases.

= 2.2.7 =

Some more bugfixes:

* renamed a class related to the automatic plugin updates (for premium orgseries addons) that conflicted with another plugin.  I should've been more unique...  If you experienced errors when activating a newer version of any Organize Series addons, this was why.  You should be good to go with this update.

* fixed a bug with the `get_series_link()` function that would produce an error if you didn't include a paramater.  

* some more minor cleanups of the readme.txt file.

= 2.2.6 =

Some houskeeping in this release along with including the framework to enable automatic updates for addons released on [OrganizeSeries.com](http://organizeseries.com/downloads)

= 2.2.5 =

Two more bug fixes:

* fix for scheduled posts not retaining a set series part number.

* fix for "<<Manage Series" showing up in the browser title when a series archive page is displayed.  I've completely removed that text and now just the "Series: {series title} << {Blog Title}" is showing up.

= 2.2.4 =

Display Bug Fix

* version 2.2.3 had a bug that affected the display of series icons on public facing side of your website.  This has been fixed.


= 2.2.3 =

More bugfixes

* made some fixes in the javascript for the quick-edit implementation so Organize Series plays nicely with other plugins that hook into the quick-edit.

* fixed an incorrect path pointer that broke the series-options page for users with a custom WP_CONTENT_DIR set.

* modified the function returning the series_icon_url so it works correctly for users with a custom WP_CONTENT_DIR and WP_CONTENT_PATH set.


= 2.2.2 =

Another bugfix release

* Fixed query bug:  When series archive pages were displayed, any widgets or other plugins querying posts (using $wp_query) on the page would not display expected posts (i.e. "Recent Posts" widget, Alikivia Sideposts, "Kates Gasis' Sideblog" plugin).  The custom joins/wheres/orderbys used by Organize Series are now isolated to only occur for the series posts being displayed.  Other queries on the page should be unaffected now.

* Cleaned up errors shown by setting wp_debug as true in the wp-config.php file. 

* Introduced with 2.2.1 was a bug where get_seriestoc() would not return the correct permalink for the series toc in certain cases.  This is fixed in 2.2.2

* Fixed conflict with wp-hide-posts plugin. 	

= 2.2.1 =
This is a bugfix release

* Fixed series archive bug:  When series_custom_base and series_toc_url were the same (as set on the series options page) then any links to specific series archive pages would load the series-toc page.  With this fix it is possible to have both set as the same without any problems.  (**NOTE:** After upgrading make sure to visit your series options page and make sure the custom_base and series_toc_url are set.  If not you will have problems with your sites rss feeds.)

* Fixed scheduled post bug:  This bug affected scheduled posts that were a part of a series that lost all series information when they transitioned to a published post.  

* Fixed draft and pending posts bug:  This bug affected posts that were saved as a draft or pending and then later published.  When published the "total parts in series" count would not increment.

= 2.2 =

This version is a major update to the plugin and introduces big changes to the core code and structure of the plugin.  Care has been given to make the upgrade as seamless as possible for users of previous versions but you will definitely want to read over the changelog to see if there are any changes that will impact you.

*AS ALWAYS - BACKUP YOUR BLOG FIRST BEFORE UPGRADING!!*

*Highlights*

* The code has been rewritten and given a full review to make it as efficient and tidy as possible.
* Everything has been looked at to ensure the plugin integrates as tightly as possible with the built-in custom taxonomy system provided by WordPress.  Some significant changes were introduced with WordPress 3.0 that made this tighter integration possible.
* I've renamed some of the plugin files to keep the naming consistent and make it easier to find components of the plugin.
* Organize Series Publisher has been unbundled from the core plugin and is [offered as a separate download via the WordPress.org repository](http://wordpress.org/extend/plugins/organize-series-publisher).  It is still free.  This unbundling is part of the new direction of releasing add-ons for Organize Series. 
* The code has been structured and given multiple new action and filter hooks for the easier implementation of "Add-ons" for extending the functionality of the core plugin.
* The plugin now has it's own home on [OrganizeSeries.com](http://organizeseries.com)
* Some more PHPDOCs have been added to the files to make it easier to track how things work and where things are (especially helpful for developers wanting to create addons for Organize Series)

*Changes*

* {developer note} Core components of Organize Series have been moved to an Object model.  See the orgSeries class in orgSeries-setup.php.  Note that all the initial setup functions have been moved to this class and out of the orgSeries.php file.
* {developer note} some files have been renamed/removed.  If you customized any Organize Series plugin files you will want to make note of the differences before upgrading.
* {developer note} Organize Series settings (options) are now accessible via the $orgseries object.  $orgseries-settings.
* {developer note} the options page completely utilizes the WordPress options/settings api.
* {developer note} widgets now fully use the WordPress widget api.
* {developer note} did a full review of the plugin files and updated any deprecated WordPress hooks/filters/functions to the current implementation.

* {bug fix} A lot of the jQuery/javascript has been given a once over and this should fix the reported conflicts with plugins like the Admin DropDown Menu plugin by Ozh.
* {bug fixes} There are numerous bug fixes and optimization done to the plugin since the last version.  For a full look at the changes check out [changeset](http://plugins.trac.wordpress.org/changeset?new=294302%40organize-series&old=232344%40organize-series).

* {enhancement} Organize Series now incorporates the same look and feel for the "manage series" page as any of the built-in WordPress taxonomy management pages.  This includes the ability to "Quick-Edit" series information.
* {enhancement} polished up the usage of the built-in WordPress image uploader/manager for associating images with series.
* {enhancement} NEW template tag: `in_series($series_term)`  This function will check if the current post is in a given series OR if the post is in ANY series (when the series_id or series_slug isn't provided).  Will return true or false.
* {enhancement} NEW template tag: `is_series_toc` This function will check to see if the displayed page is the series_toc page.  Helpful for in the cases when you want to do something custom in the header or footer or sidebar of a seriestoc page.


= 2.1.7 =

This version has been tested with WordPress 2.9 and all is in working order. Please use the new compatibility form for the Organize Series listing on WordPress/extend to indicate your results with using it.

*Fixes*

* Fixed a problem with having duplicate id selectors with the_series_title() outputs resulting in validation errors.  See the original report [here](http://unfoldingneurons.com/forums/topic/w3org-validation-issue?replies=2#post-820)

* Fixed a bug resulting in Series Part numbering not propagating correctly through all posts in a series when editing a post that is part of a series.  See original report [here](http://unfoldingneurons.com/forums/topic/conflict-between-series-and-category-indexing?replies=3#post-831)

* Fixed a bug where Series Meta for "the_content" was being displayed where the_excerpt was called.  See [here](http://wordpress.org/support/topic/330096?replies=2#post-1275872) for original report.

* Fixed the seriestoc template not being displayed when permalinks are off. Important: When permalinks are not enabled ".../?seriestoc=x" points to the series table of contents for all series. "x" can be any number.
- get_series_link() has been updated to direct to the correct link depending on whether permalinks are enabled or not.


= 2.1.6 =

*New Features*

* Just a small change that allows users to chooses whether or not the custom seriestoc URL will have a backlash after it or not.  Prior to this, Organize Series would always include the backslash even if it wasn't explicitly included via the Series Options page.

*Major Fixes*

* There was some typos with the roles and capabilities array used with Organize Series that resulted in new users not being able to use the plugin.  If you got a "You do not have sufficient permissions" error, that is fixed now.

= 2.1.5 =

*New Features*

* new function: get_series_ordered() - allows users to pull a list of series_ids, slugs, and names from the database ordered by whatever criteria they want. It returns an array of the data that the user will have to display as needed. (ht to [Amanda Giles](http://www.amandagiles.com) for the initial code that I modified a bit). See the inline docs in the series-taxonomy.php file around line 192.

* new bundled plugin: Organize Series Publisher - allows users to set a series as "unpublished" and all posts published to that series will be held back until the author decides to publish the series all at once.  Great for creating "issues".  This plugin is the result of me being hired by [Amanda Giles](http://www.amandagiles.com) who graciously agreed to make this available to all of you!  This plugin must be activated after Organize Series is activated.

* Users can now set a custom base for for series archive permalinks.  For instance, you can now change: "yourblog.com/series/someseriesname" to "yourblog.com/coolitems/someseriesname".  The place to add the custom base is at the Series Options page.  By default it is set to /series/.

* link to wphelpcenter.com added to the Series Options Page.  I've partnered with wphelpcenter.com for official support and anybody paying them for help with Organize Series will be helping to support me!  It's a great service for those times where you "*need help right now*"!  When contacting them, be sure to give my affiliate number: **14322**

*Major Fixes*

* Fixed a validation issue reported [here](http://unfoldingneurons.com/forums/topic/ie8-shows-errors-on-page) due to some improper js code.

* Posts saved as draft and then published will now show the correct number for "total posts in series".  Reported [here](http://unfoldingneurons.com/forums/topic/total_posts_in_series-not-updating).

* Fixed the problem where those with the "Editor" role were unable to manage series or change series options.  Reported [here](http://wordpress.org/support/topic/320604?replies=2#post-1261455)

* Updated the layout/looks of the Organize Series Options page to better fit in with WordPress 2.8+

*Minor Fixes*

* A couple of mixed localization tags

* Changed the name of some menu items to better correspond with docs.  "Post Series" in the Edit posts menu has been changed to "Manage Series".

* Changed the series filter dropdown on the edit posts page to show all series (not just series that have posts).  This is so if you have a series where all the posts are drafts, or pending, you can still view them filtered by series.

* Also fixed some typos and minor bugs throughout the code.


= 2.1.2 = 

*Major Fixes*

* fixed problems that were happenening with excerpts (I'm pretty sure I squashed this bug - if you notice differently please [add to this thread](http://unfoldingneurons.com/forums/topic/excerpt-problem-with-newest-plugin-version-21)

* Series Feeds weren't working.  Now they are.

*Minor Fixes*

* a couple of missed localization tags.

= 2.1.1 =
This is a recommended upgrade to fix a major bug listed below.

*Major Fixes*

* fixed a bug that occured when comments were added to a post that was part of a series.  When a comment was made the post would lose all series information.  

*Minor Fixes*
* a typo on the Organize Series Options page.

= 2.1 =
Organize Series version 2.1 is a significant update that introduces some new features and fixes many bugs introduced with many of the more recent WordPress updates.  Notice that this version *requires* WordPress 2.8.  I decided that it's becoming too time-consuming to keep making Organize Series backwards compatible. 

*New Features*

* Organize Series now uses the built-in media-uploader with WordPress for selecting and uploading images to use as Series Icons.  What's cool about this is you don't have to worry about where your images are being uploaded now.  When selecting images you click the "Insert into Post" button and this will assign the image as the icon for your series.
* There are more visual cues for what image is selected and what image is the existing image when editing series.
* Organize Series integrates fully with the "quick-edit" feature on the Manage->Posts page.  Now you can Add, remove or change series information quicker and easier than ever before!!
* Made the plugin much more efficient with number of queries and code execution.
* Added an option for customizing what series information is shown when `the_excerpt()` is used ( as opposed to `the_content()` )
* Added a link to the series options page with the Organize Series listing on the WordPress plugin page.
* On new installs of Organize Series there will now be a warning at the top of all WordPress admin pages that the Series Options page needs to be loaded to initialize the Organize Series settings.
* Organize Series now provides the option for users to delete all Organize Series related data from their database when deleting Organize Series using the link on the WordPress plugins page.  This option can be set via the Series Options page.
* The `latest_series()` widget and template tag now allows for more customization of what series information is displayed.  Now users have control over: How many series are displayed and how the list is ordered.  With this addition I've also changed the `latest_series()` templates via the series options page to allow for three settings -> latest series (tags before), latest series (inner tags), and latest series (tags after).  The Latest Series widget now has more options to select what the Latest Series widget outputs.  This was one of the most requested features and I'm glad to be able to implement it.
* added an '`auto_tag_nav_toggle`' option to the Series Options page.  Auto-insertion of Series Navigation used to be controlled by the '`auto_tag_toggle`' option (which also determines whether series_meta information is outputted automatically).  Now you can control the output of the series navigation strip separately from the series meta information.
* fixed the series default `$plugin_url` and `$plugin_path` so it works with symlinks (thanks to patch from  [Georg S. Adamsen](http://wordpress.blogos.dk/) )
* Localized Organize Series.  Organize Series is now fully localized so translators can bring Organize Series to other languages.  Thanks to [Benjamin Lupu](http://www.benjaminlupu.net/) for his initial work on this. I welcome any other translators who'd like to contribute the translation of Organize Series into other languages!

*Major Fixes*

* fixed `series_description()` bug that didn't return a series description
* restructured some of the hooks/filters so they were called in more correct places.
* WordPress 2.8 beefed up the custom taxonomy structure making it much easier for users to create custom taxonomies.  A side-effect for Organize Series is that this created an additional "Manage Series" link in the admin menu and an additional Series Meta Box on the Add/Write Posts page.  Organize Series 2.1 removes these additions to eliminate confusion.
* Posts that are a part of a series will no longer lose their series related information when using the quick-edit or bulk-edit feature on the Manage->posts page.
* Fixed the link on the series widget settings so it correctly directs to the Organize Series Options page.
* fixed dropdown in series widget so it directs to proper page using new taxonomy link format AND is xhtml compliant (moved js to the head and out of body).
* Fixed Organize Series so that scheduled posts added to a series will not lose their series information when reaching published date.
* Reduced number of queries to database when the series-postlist is displayed on a post page belonging to a series.  There will no longer be additional queries for each post that is part of the same series (which quickly multiplied in series with a lot of posts belonging to it).
* added more `wp_nonce` security to the backend. Think orgSeries is all covered now.
* fix for `the_excerpt` stripping series meta tag html.
* Eliminated excess code not needed for earlier versions of WordPress.


*Minor Fixes*

* some fixes to `get_series_rss_link()` in orgSeries-rss.php to autodiscover `series_id` for displayed post.
* minor fixes for guessing the location of plugin files (orgSeries.php)
* minor fix for `wp_series_nav()` (which sometimes didn't display the right series in order for "next" and "previous" links).
* corrected some typo errors.
* Some security fixes -> making sure all wp_queries use $wpdb->prepare.
* All hard-coded wp-content references are corrected to call the WP-CONTENT definition allowing for WordPress installs that renamed their wp-content directory.
* fix for series widget displaying "Other Posts that are part of this series" outside of single post pages and with posts that are not part of a series.
* The title attribute in series links now uses the series name instead of series id and I've added an id attribute to the links using the series id.
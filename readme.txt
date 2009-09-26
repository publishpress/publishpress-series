=== Organize Series  ===
Contributors: nerrad
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7871313
Tags: category, series, organize, post, taxonomy
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 2.1.2

A plugin for managing the article series you write.

== Description ==

The *Organize Series WordPress Plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series. 

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

Make sure you check out the features section in the "other notes" tab for all the neat stuff you can do!

**VERY IMPORTANT** - if you are upgrading, make sure you read the upgrade notes found within the Installation Tab - there are a couple of changes that affect your settings on the Series Options page.

== Installation ==

**NOTE: As of version 2.1, Organize Series now *requires* WordPress 2.8 - for earlier versions of WordPress, use [version 2.0.8 of Organize Series](http://downloads.wordpress.org/plugin/organize-series.2.0.8.zip)**

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File (or use the built-in updater provided by WordPress)
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. *NEW with Organize Series 2.1* -> the Series Icon Feature uses the built in Media Uploader/picker in WordPress.  You don't have to worry about what directory you upload your series icons into anymore.
1. Visit the "Options/Settings->Series Options" page to initialize all the default settings.

You can do the above or just use the new plugin install integrated in WordPress.

= Upgrading =
If you are upgrading from Organize Series 2.0 or above you can follow the same steps as above however **make sure you revisit the Organize Series Settings** page.  There are some new fields that you'll need to populate.  There are two ways of going about doing this:

*Fill in or modify the blank fields on the options page*

Specifically:

+  Display series navigation links checkbox -> make sure this is checked if you want your navigation links to be automatically inserted.
+  Delete all organize series datat from database... checkbox -> You can leave it unchecked UNLESS you want to have everything in your database that is organize series related removed when you delete the organize series plugin.
+  Series Meta (with excerpts) textarea -> You can now control what gets displayed with post-excerpts ( the_excerpt() ).  You can add exactly what is in the "Series Meta" textarea or modify to suit.
+  Latest Series (tags before), (inner tags), and (tags after) textareas. -> Latest Series used to have just one template but I've brokent it out into three separate areas so you can have more control over what the Latest Series widget (or template tag) displays.
 
*Click the "Reset Options to Default" button*

This will populate all the fields with the default settings.

**IMPORTANT** This will also replace any customizations you have already set - so if you choose this option make sure you copy your custom settings FIRST so you can reapply them after.

= Other Notes =
If you customize the included `series.php` file (for series archive pages) and `seriestoc.php` file (for Series Table of Contents Page) to fit your site theme better make sure you copy those two files over to your theme directory so future upgrades of Organize Series won't overwrite your customizations.

That's it!  You might want to read through the Series Options page (in the "settings" menu of WP 2.5 or "options" in earlier WP versions) and the Manage->Series Page.  You'll also notice that your "write/edit" posts page now has a handy dandy series box on it for adding your posts to a series (and creating new series on the fly).

For support please visit [the support forums](http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin "For help click here!").

== Frequently Asked Questions ==
= How can a create a page listing all the series I've written? =
By default the page is already created for you and the url is `http://yourblog.com/series/` (this url can be changed via the series options page).  However, depending on your theme the layout may not be quite what you want it to be.  The output of this table of contents page is controlled both by the `series table of contents listing:` template in the series options page and the `seriestoc.php` file in your organize series plugin directory.  You can make necessary changes to the `seriestoc.php` file to match your theme (go by the layout of your index.php file) and copy it to your theme directory.

Your question not answered?  Ask your usage related question on [the support forums](http://unfoldingneurons.com/forums/forum/usage-help) and I'll put the most common ones here.

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

The following is a brief overview of all the features in Organize Series.  For more indepth coverage of the features of Organize Series you can follow the [Organize Series Usage Tips Series](http://unfoldingneurons.com/series/organize-series-usage-tips) on my blog. Better yet, why not subscribe to the [usage feed](http://unfoldingneurons.com/series/organize-series-usage-tips/feed)?

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

Oh, if you implement Organize Series in a cool way [let me know here](http://unfoldingneurons.com/forums/forum/user-contributed-editshacks)

And one last thing I've just gotta mention...

#### Widgets!! Need I say more?

== Usage ==
There's just so much to say about how you can use Organize Series that I've decided to write a series of posts dealing with using Organize Series.  You can follow along with the series [here](http://unfoldingneurons.com/series/organize-series-usage-tips) or feast on the [feed](http://unfoldingneurons.com/series/organize-series-usage-tips/feed).

Don't forget, if you have questions related to usage, I'm usually pretty good at responding to requests for help [here](http://unfoldingneurons.com/forums/forum/usage-help).

Have fun - and get writing those series!!

== Changelog ==

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


= 2.0.8 =
Significant fixes and a couple minor new features. NOTE - this version was ONLY MADE POSSIBLE thanks to the many people who tested the development betas and reported on bugs.  Without their help, I wouldn't have got this version out the door.  Read through the comments on the [Organize Series feed](http://unfoldingneurons.com/tag/organize-series-wordpress-plugin/feed/) to see all the people who contributed!

*New features*

* Added a new argument to the "wp_list_series" function - "number" which enables users to limit the number of series displayed
* Reworked the layout of Organize Series administration integration with WordPress so it "fits" with WordPress 2.7 - including showing "Series" in the Dashboard "Right Now" widget.
* Organize Series interface can be floated around and respositioned in the write/edit posts screen for WordPress 2.7
* moved the "Manage Series" menu item to the Posts submenu area for WP2.7 (previous versions stay in the same location)

*Minor*

* fixed an inconsistency with 'show_last_update'
* fixed some bugs with the atom feed (was an early update...not sure if it's still working correctly now)
* fixed the 'series_description' function (it wasn't working)

*Major*

* Organize Series will now work correctly with the post-revisions feature introduced with WordPress 2.6
* redid the plugin file locater code to allow for changes with the admin menu filters.
* redid the plugin file locater code to allow for users customizing the location of their wp-content folder
* redid all code referencing wp-config.php to allow for users customizing the location of their wp-config.php file (see http://planetozh.com/blog/2008/07/what-plugin-coders-must-know-about-wordpress-26/ for reference I used).
* Fixed posts that are a part of series but not published showing up in the series list on published posts that are part of a series-taxonomy
* Fixed a bug where custom navigation text set on the Series options page wasn't showing up correctly.



**2.0.7** - Contains the following fixes:

*Minor*

* Removal of extraneous html from series.php and seriestoc.php template files.
* Updated readme.txt for suggestion to copy customized `series.php` and `seriestoc.php` files to theme directory.
* Added missing `< /div >` to `seriestoc.php` file to fix "98%" of the typical default installations of this plugin.
* fixed a potential bug with the `get_series_toc()` function/template tag.
* Fixed a few spacing, code structure errors throughout the files.
* Changed the "title" attribute for the `get_series_toc()` link to something a bit more friendly.

*Major*

* Fixed bug affecting installs of Organize Series on blogs with a subdirectory in their blog address (i.e. `http://www.myblog.com/blog/`).  In these setups the Series Table of Contents page re-direct wouldn't work.  This bug also affected custom seriestoc urls set on the Series Options pages with a multiple slash structure (i.e. the default is `\series\`, but if you used `\series\seriestoc` it wouldn't work).  Many thanks to [Ken Carlson](http://reflections.cyberpastor.net/) for his help in getting it fixed.

**2.0.6** - Fixes a pretty major bug with the custom rewrite/permalink structure for series archives.  This bug only shows up if you use certain prefixes to your custom permalinks such as `/archives/...rest of permalink`.  Hat tip to [Edward](http://www.scompt.com/) for the catch and fix.

**2.0.5** - Contains the following bug fixes:

* PHP error that was reported on the Manage Series page affecting certain installs.
* .rss validation warning for series archive feeds thanks to [this post](http://oriolrius.cat/blog/2008/04/25/problema-al-rss-del-wordpress-solucionat/)
* some typo fixes with readme.txt file
* updating readme.txt to reflect that Organize Series works with WordPress 2.5.1

**2.0.4** - This is a pretty significant changeset that fixes some things with the Series Table of Contents page:

*  Series Table of Contents page now returns the correct header ('200').  It was returning '400' before the fix. [see this thread](http://unfoldingneurons.com/forums/topic/error-404).
*  NEW FEATURE:  You can now indicate what the browser title bar will say on Series Table of Contents page via the Series Option settings in your admin.  The default will be "Series Table of Contents Page"
*  Fixed a conflict with "All In One SEO Pack" plugin affecting Series Table of Contents Page.
* Thanks to [gnouros](http://www.morbleu.com/) for detecting and testing my fixes of the above errors for me!
*  Shortened the "official" plugin name to "Organize Series" to match the directory in the svn repository - hopefully this will fix the indicator not showing up in your plugin list for when there's a new version of Organize Series out.  [See this post in the WP Support Forums](http://wordpress.org/support/topic/165765).

**2.0.3** - Previous fix broke the ajax call for the add series on a fly (on write/edit posts pages).  This fixes that.

**2.0.2** - IMPORTANT FIX for where organize series broke submenu links on plugins that added their own topmenu.  Won't happen anymore.

**2.0.1** - some fixes so that changing the `SERIES_URL` definition in orgSeries.php actually works (you can use this to pick how you want the series-archive permalinks to look - important for avoiding conflicts with existing category permalinks...)
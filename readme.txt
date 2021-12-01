=== PublishPress Series - Organize WordPress Posts into a Series  ===
Contributors: publishpress, kevinB, stevejburge, andergmartins, olatechpro
Author: publishpress
Tags: category, series, organize, post, taxonomy
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 5.8
Stable tag: 2.7.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PublishPress Series allows you to collect WordPress content together into a series. This is ideal for magazines, newspapers, short-story writers, teachers, comic artists, or anyone who writes multiple posts on the same topic.

== Description ==

PublishPress Series allows you to collect WordPress content together into a series. This is ideal for magazines, newspapers, short-story writers, teachers, comic artists, or anyone who writes multiple posts on the same topic.

When you're writing a post, you can easily add a post to an existing series, or start a new series. You can also add a new post into the middle of an existing series, and the plugin will adjust the order.

It's easy to keep track of what posts have been added to a series. You can also filter the post list by series giving you a quick way to see all the posts you’ve already added to the series. 

Right out-of-the-box, the Series meta box will be automatically added to posts that are a part of a series. You can modify how this meta box appears using the template for it in the Series Options Page.

Add, delete, edit all your series on one handy page. You can also upload/select images to associate with the series. PublishPress Series makes it easy to keep track of all the series you are writing!

= PublishPress Series integrates with the WordPress taxonomy system =

Included with WordPress out of the box are two taxonomies - "categories" and "tags". Publishpress Series introduces a new taxonomy "series".  Thanks to the rich API provided to plugin authors by WordPress, Publishpress Series takes advantage of (as much as possible) the built-in WP goodness!  As an added bonus - plugin developers can expand on what Publishpress Series offers by interacting with the new series taxonomy and the built-in filters and hooks.

= PublishPress Series works out of the box =

Once you've activated the plugin, all you have to do is visit the Series Options page and then you're ready to go (you don't have to change/or add anything!).  Out of the box, Publishpress Series defaults to automatically insert into your blog all the necessary "template tags" for displaying series related information.

= Complete options page for easy customization =

Nearly everything you might want to customize with Publishpress Series can be done on the Series > Options page:

* Control placement of series related info (Series Meta, Series Table of Contents, Series Postlist boxes, Series Navigation Strip, etc.)
* Customize the HTML output or series related info by using the %token% system with a token legend right on the page.
* Control what the path of your "series table of contents" link will be.
* Control how posts in a series will be displayed on series archive pages.  The default is by date in descending order (newest to oldest) - but you can order it by part too.
* Set the icon width for images associated with series for different pages.

= Many template tags for theme authors and WordPress coders =

If you like going into your theme files, you'll love the "template tags" that PublishPress Series makes available for you! All the template tags you'll ever need are found in the `series-template-tags.php` file in your Publishpress Series plugin directory.  Each tag is also fully documented inline so you can have a better idea what it does!

You can also edit the default .css file for orgSeries OR point to your custom .css in `styles.css` for your theme.

= Join PublishPress and get the Pro plugins =

The Pro versions of the PublishPress plugins are well worth your investment. The Pro versions have extra features and faster support. [Click here to join PublishPress](https://publishpress.com/pricing/).

Join PublishPress and you'll get access to these Pro plugins:

* [PublishPress Authors Pro](https://publishpress.com/authors) allows you to add multiple authors and guest authors to WordPress posts.
* [PublishPress Blocks Pro](https://publishpress.com/blocks) has everything you need to build professional websites with the WordPress block editor.
* [PublishPress Capabilities Pro](https://publishpress.com/capabilities) is the plugin to manage your WordPress user roles, permissions, and capabilities.
* [PublishPress Checklists Pro](https://publishpress.com/checklists) enables you to define tasks that must be completed before content is published.
* [PublishPress Permissions Pro](https://publishpress.com/permissions)  is the plugin for advanced WordPress permissions.
* [PublishPress Pro](https://publishpress.com/publishpress) is the plugin for managing and scheduling WordPress content.
* [PublishPress Revisions Pro](https://publishpress.com/revisions) allows you to update your published pages with teamwork and precision.
* [PublishPress Series Pro](https://publishpress.com/series) enables you to group content together into a series 

Together, these plugins are a suite of powerful publishing tools for WordPress. If you need to create a professional workflow in WordPress, with moderation, revisions, permissions and more... then you should try PublishPress.

=  Bug Reports =

Bug reports for PublishPress Series are welcomed in our [repository on GitHub](https://github.com/publishpress/publishpress-series). Please note that GitHub is not a support forum, and that issues that aren't properly qualified as bugs will be closed.

= Follow the PublishPress team = 

Follow PublishPress on [Facebook](https://www.facebook.com/publishpress), [Twitter](https://www.twitter.com/publishpresscom) and [YouTube](https://www.youtube.com/publishpress).


== Installation ==

### INSTALL PUBLISHPRESS SERIES FROM WITHIN WORDPRESS

* Visit the plugins page within your dashboard and select ‘Add New’;
* Search for ‘PublishPress Series’;
* Activate PublishPress Series from your Plugins page.

### INSTALL PUBLISHPRESS SERIES MANUALLY

* Upload the ‘organize-series’ folder to the /wp-content/plugins/ directory;
* Activate the PublishPress Series plugin through the ‘Plugins’ menu in WordPress;


== Frequently Asked Questions ==

**What does PublishPress Series do?**

It makes it easier for you to write a series of posts on your blog/website and have them automatically linked together. One of the best ways to build an audience on your blog/website is to write short posts about the same subject over a period of time rather than one long post.  There are numerous SEO benefits to this as well.  PublishPress Series takes the hassle out of managing the serial posts and you can just focus on writing.  Another way of saying this is, PublishPress Series takes the work out of organizing series so that you write more and your readers can EASILY follow along.

**Can I customize the archive page for series?**

You sure can! Create a file called `taxonomy-series.php` and copy the code from your themes `archive.php` file and use that as the basis for building out how the series archive page looks.  You'll also want to take a look at all the template tags you can use for series in the `orgSeries-template-tags.php` file.

**Can I customize the archive page for a single series??**

Yes. Create a file named `taxonomy-series-{series-slug}.php` file and replace `{series-slug}` with the slug of your series and you'll be good to go!

**Can I create a page that lists all the series available?**

Yes, you can In the `organize-series` folder you'll see a file called `seriestoc.php`.  It's what gets loaded when you go to your series toc url (defaults at \series-toc\ but you can change this on the series options page).  Chances are though it doesn't look great with your theme (the file included just gives you an idea of what you can put in the file). What you want to do is copy this file to your theme folder and then modify it to match your theme structure (see your themes archive page for an example).  You don't need to put any of the WordPress loop code in this file.

== Screenshots ==

1. When you're writing a post, you can easily add a post to an existing series, or start a new series.

2. Integration with the Posts List table - it's easy to keep track of what posts have been added to a series.  

3. Right out-of-the-box, the Series meta box will be automatically added to posts that are a part of a series. 

4. Add, delete, edit all your series on one handy dandy page. You can also upload/select images to associate with the series.  

5. This feature is in the Pro version of PublishPress Series and it allows you to use the plugin with any WordPress post type.

6. The Pro version of PublishPress Series provides a set of WordPress “shortcodes” that provide users a way to easily insert various series information into their posts.

7. This feature is in the Pro version of PublishPress Series and it brings the capability of adding a post to more than one series.

8. The Pro version provides additional tokens to use in the series options page for customizing various series templates.

9. The PublishPress Series core plugin groups posts together in series. The Pro version gives the ability to put series together in groups.

10. The Pro version of Series enables easy bulk publishing of all posts in a series at once.


== Changelog ==

v2.7.1- [===unreleased===]
* Fixed: PHP notices from the widget #303
* Fixed: Visual bug with the "Series Taxonomy" choice #312
* Fixed: Deprecated notices #307
* Fixed: Capability to access the "Series" menu #313
* Fixed: "Show Series Table Of Content" setting is broken in widget #308
* Fixed: Allow users to customize buttons #311

v2.7.0- 2021-11-17
* Fixed: Clarify what HTML is allowed in templates #273
* Fixed: Update the Reviews box class #274
* Fixed: Add message to draft series without part number #261
* Feature: Added %first_post% token to series navigation #232
* Fixed: Incorrect Yoast SEO attributes for Series TOC page #82
* Added: New menu link for Series #278
* Added: Post list series limit #271
* Feature: Allow users to change the navigation, metabox or post series box position #270
* Feature: Improve the "Series Table of Content" widge #238
* Fixed: wp_serieslist_display() Not Working #287
* Fixed: Issue with Seriously Simple Podcasting plugin #87
* Fixed: PHP Notice: Trying to access array offse #298

v2.6.3- 2021-09-29
* Fixed: Fix "invalid_taxonomy" WP_Error #88
* Fixed: Append to series on action Scheduled to Published #83
* Fixed: Fix "Only variable references should be returned by reference" #89
* Fixed: Edit Series screen has bold text over-run #225
* Fixed: More clarity on "Templates" tab #226
* Fixed: Template previous button arrows are in the wrong direction and not saving #233
* Fixed: Show the ID in Manage Series #237
* Fixed: Metabox is not entirely clear with Multiple Series #229
* Fixed: Larger boxes for Templates area #236
* Feature: Allow "Table of Contents" widget to show a specified series #234
* Added: Add a review request #210
* Added: Include Italian translation #253
* Fixed: Series Grouping using categories #216

v2.6.2- 2021-09-07
* Fixed: Yoast SEO issue #206
* Fixed: Move "Reset options" to uninstall tab #208
* Fixed: Improve settings option labels #205
* Fixed: 2.6.1 is causing a JavaScript error in core #213
* Fixed: Can't add icon/image to a series #187

v2.6.1- 2021-09-02
* Fixed: More room on settings pages #183
* Fixed: Clean up for icons screen #181
* Fixed: Series page shows "No tags found." #182
* Fixed: Small design issues in metabox #160
* Fixed: Multiple series list overlays fields #188
* Fixed: Make plugin translatable #186
* Fixed: Quick Edit problem with Multiples #196
* Fixed: Changes to 'General' Settings #199
* Fixed: Formatting issues with Latest Series Widget #198

v2.6.0- 2021-08-26
* Updated: New name and remove previous sidebar branding #98
* Fixed: Clean up sidebar box in post editingg screen #99
* Fixed: Fix "Use of undefined constant SERIES_QUERYVAR" error #86
* Fixed: PHP Notice: Trying to access array offset on value of type null #117
* Fixed: Edit series error #126
* Fixed: Series settings screen cleanup #129
* Fixed: PHP Notice: Undefined index notices on settings update #144
* Fixed: PHP Notice: Trying to access array offset on value of type null on series single page #147
* Fixed: Improve CSS on frontend #162
* Fixed: Use new Media Library for series icon selection #141
* Fixed: Series TOC Widget improvements #163
* Fixed: Latest Series widget doesn't load #164
* Added: Package add-ons into Pro version #101
* Added: Add Upgrade banner for Pro version #100
* Added: Add the PublishPress Footer to free version #97

For older commits, commits see the [commit list on Github](https://github.com/publishpress/publishpress-series/commits/master) or the [release tags](https://github.com/publishpress/publishpress-series/releases). You can also view the [closed milestones on github](https://github.com/publishpress/publishpress-series/milestones?state=closed) to see what changed within a release.

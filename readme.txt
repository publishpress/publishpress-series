=== Organize Series  ===
Contributors: nerrad
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7871313
Tags: category, series, organize, post, taxonomy
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 5.3
Stable tag: 2.5.13
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for managing the article series you write.

== Description ==

The *Organize Series WordPress Plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series.

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

>> **NOTE: Organize Series now requires at least WordPress 4.7.  Also, the required version of PHP is 5.6 or greater.  The plugin will automatically deactivate itself if you are using less than PHP 5.6.**

Make sure you check out the [features section in the "other notes" tab](http://wordpress.org/extend/plugins/organize-series/other_notes/) for all the neat stuff you can do!

**NOTE: Organize Series has not been designed to be multisite compatible at this time.  Some users are finding it works great for their needs but your mileage may vary. Feel free to try on multi-site but at your own risk.**

**IMPORTANT LINKS**

[Author's Website](http://unfoldingneurons.com) | [OrganizeSeries.com](http://organizeseries.com) | [OrgSeries ADDONS](http://organizeseries.com/download/) | [OrgSeries Twitter](http://twitter.com/organizeseries)

== Installation ==

**As of version 2.5.7, Organize Series *requires* WordPress 4.7+ to work. Organize Series also currently requires PHP 5.6+.  Organize Series *may* work with earlier versions of WordPress but support is not provided for earlier versions and use at your own risk.**

**ALSO**: If you are upgrading make sure you read the upgrade notes.

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File (or use the built-in updater provided by WordPress)
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. The Series Icon Feature uses the built in Media Uploader/picker in WordPress.

You can do the above or just use the new plugin install integrated in WordPress.

= Upgrading =

* **DO NOT UPGRADE TO THIS RELEASE (2.5.7) UNLESS YOUR WORDPRESS INSTALLATION IS AT VERSION 4.7 and you have PHP 5.6+ on your server**

**ALSO**

I STRONGLY recommend that you take note of any changes you may have made on the series options page for customizing the layout/look of Organize Series before upgrading.  I try to do my best to make sure any changes get imported successfully with updates but *sometimes* things happen unexpectently and the options may get reset (which can understandably be frustration - believe me, it's happened to me a couple of times over the years!).  What I usually do is open a tab with the options and then conduct the upgrade from another tab.  That way if something DOES go awry, I can just copy paste from the inputs I left open in the previous tab.  Again, I test throughly before releasing an update but I simply cannot account for every possible hosting environment out there so resets *can* and sometimes *do* happen.


= Other Notes =
If you customized the included `seriestoc.php` file (for Series Table of Contents Page) to fit your site theme better make sure you copy the file over to your theme directory so future upgrades of Organize Series won't overwrite your customizations.  The `seriestoc.php` file is already setup to work with the WordPress Twenty Twelve theme.

That's it!  You might want to read through the Series Options page (as a submenu item under 'options') and the Manage->Series Page.  You'll also notice that your "write/edit" posts page now has a handy dandy series box on it for adding your posts to a series (and creating new series on the fly).

== Frequently Asked Questions ==

**What does Organize Series do?**
It makes it easier for you to write a series of posts on your blog/website and have them automatically linked together.

**So?**
Well, one of the best ways to build an audience on your blog/website is to write short posts about the same subject over a period of time rather than one long post.  There are numerous SEO benefits to this as well.  Organize Series takes the hassle out of managing the serial posts and you can just focus on writing.  Another way of saying this is, Organize Series takes the work out of organizing series so that you write more and your readers can EASILY follow along.

**Does Organize Series work with the new editor in WordPress 5.0+?**

Short answer is yes it does.  You'll still see the series selector in the `Document` sidebar found in the editor.  However there is a current quirk where if you leave the part field empty for a series assigned to a post, it will not automatically update with the part number until the page is complete refreshed. Currently this is to be expected and the part was assigned.

**Can I customize the archive page for series?**
You sure can!  Just create a file called `taxonomy-series.php` and copy the code from your themes `archive.php` file and use that as the basis for building out how the series archive page looks.  You'll also want to take a look at all the template tags you can use for series in the `orgSeries-template-tags.php` file.

**But I want to customize the archive page for a *single* series, can I do that?**
Yup.  Create a file named `taxonomy-series-{series-slug}.php` file and replace `{series-slug}` with the slug of your series and you'll be good to go!

**Hang on, what I really want is to create a page that lists all the series available. You probably don't have that available do you?**
Hey, I've got you covered there as well!  In the `organize-series` folder you'll see a file called `seriestoc.php`.  It's what gets loaded when you go to your series toc url (defaults at \series-toc\ but you can change this on the series options page).  Chances are though it doesn't look great with your theme (the file included just gives you an idea of what you can put in the file). What you want to do is copy this file to your theme folder and then modify it to match your theme structure (see your themes archive page for an example).  You don't need to put any of the WordPress loop code in this file.

**Wow there are sure a lot of features and ways of customizing the Organize Series plugin.  However I'm wondering can you put in ___________ feature?**
Organize Series is a passion project and I don't have as much time as I'd like to build additional features requested by customers, so I focus on the things that interest me. There's a number of add-ons you can check out [here](https://organizeseries.com/downloads/) and one of those might be what you need.

If you don't see an add-on that has the feature you want for Organize Series, write up a request here on the [WordPress Support Forums](http://wordpress.org/support) (make sure you tag the post with organize-series) and I might just develop it as the next add-on!!


== Support ==

There have been some changes to how I provide support for Organize Series. Here are the different options for support:

1. Post any support questions on the WordPress Forums at [wordpress.org](http://wordpress.org/support). I will keep an eye on the forums for bug reports but will NOT be actively responding to usage/installation help.  If I don't respond to forum posts I'm not being rude or snubbing you, I just simply don't have time to respond. Organize Series is not my full-time job.  It's just a passion project.

1. Submit issues at the [Organize Series repository on github](https://github.com/roughsmoothengine/organize-series/issues). Or better yet contribute a pull request!

== Screenshots ==
1. Organize Series Options Page:  There are so many possibilities for how Organize Series can be customized. Nearly everything can be done on the built-in options page.

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
Included with WordPress out of the box are two taxonomies - "categories" and "tags".  Organize Series introduces a new taxonomy "series".  Thanks to the rich api provided to plugin authors by WordPress, Organize Series takes advantage of (as much as possible) the built-in WP goodness!  As an added bonus - plugin developers can expand on what Organize Series offers by interacting with the new series taxonomy and the built-in filters/hooks.

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
Organize series now has [it's own website](http://organizeseries.com) where you can find additional addons that bring extra functionality to the core plugin. I'm releasing as many addons as I can based on features users have requested but keep in mind this is done in my spare time.

== Usage ==
All usage instructions are available at [OrganizeSeries.com](http://organizeseries.com).

Have fun - and get writing those series!!

== Upgrade Notice ==

*AS ALWAYS - BACKUP YOUR BLOG FIRST BEFORE UPGRADING!!*


== Changelog ==

Changelog is no longer bundled with the readme.  For latest commits see the [commit list on github](https://github.com/roughsmoothengine/organize-series/commits/master) or the [release tags](https://github.com/roughsmoothengine/organize-series/releases).  You can also view the [closed milestones on github](https://github.com/roughsmoothengine/organize-series/milestones?state=closed) to see what changed within a release.

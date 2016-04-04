=== Organize Series  ===
Contributors: nerrad
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7871313
Tags: category, series, organize, post, taxonomy
Requires at least: 3.7
Tested up to: 4.5
Stable tag: 2.5.2
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A plugin for managing the article series you write.

== Description ==

The *Organize Series WordPress Plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series.

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

Make sure you check out the [features section in the "other notes" tab](http://wordpress.org/extend/plugins/organize-series/other_notes/) for all the neat stuff you can do!

**As of version 2.4.6, Organize Series *requires* WordPress 3.7+ to work. While Organize Series *may* work on PHP5.2 it is recommended (and supported) for PHP5.3+.**

**NOTE: Organize Series has not been designed to be multisite compatible at this time.  Some users are finding it works great for their needs but your mileage may vary. Feel free to try on multi-site but at your own risk.**

**IMPORTANT LINKS**

[Author's Website](http://unfoldingneurons.com) | [OrganizeSeries.com](http://organizeseries.com) | [OrgSeries ADDONS](http://organizeseries.com/download/) | [OrgSeries Twitter](http://twitter.com/organizeseries)

== Installation ==

**As of version 2.4.6, Organize Series *requires* WordPress 3.7+ to work. Organize Series also requires PHP 5.2+.  Organize Series *may* work with earlier versions of WordPress but support is not provided for earlier versions and use at your own risk.**

**ALSO**: If you are upgrading make sure you read the upgrade notes.

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File (or use the built-in updater provided by WordPress)
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. The Series Icon Feature uses the built in Media Uploader/picker in WordPress.

You can do the above or just use the new plugin install integrated in WordPress.

= Upgrading =

* **DO NOT UPGRADE TO THIS RELEASE (2.4.6) UNLESS YOUR WORDPRESS INSTALLATION IS AT VERSION 3.7**

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

**Can I customize the archive page for series?**
You sure can!  Just create a file called `taxonomy-series.php` and copy the code from your themes `archive.php` file and use that as the basis for building out how the series archive page looks.  You'll also want to take a look at all the template tags you can use for series in the `orgSeries-template-tags.php` file.

**But I want to customize the archive page for a *single* series, can I do that?**
Yup.  Create a file named `taxonomy-series-{series-slug}.php` file and replace `{series-slug}` with the slug of your series and you'll be good to go!

**Hang on, what I really want is to create a page that lists all the series available. You probably don't have that available do you?**
Hey, I've got you covered there as well!  In the `organize-series` folder you'll see a file called `seriestoc.php`.  It's what gets loaded when you go to your series toc url (defaults at \series-toc\ but you can change this on the series options page).  Chances are though it doesn't look great with your theme (the file included just gives you an idea of what you can put in the file). What you want to do is copy this file to your theme folder and then modify it to match your theme structure (see your themes archive page for an example).  You don't need to put any of the WordPress loop code in this file.

**Wow there are sure a lot of features and ways of customizing the Organize Series plugin.  However I'm wondering can you put in ___________ feature?**
Over the last couple of years I've received numerous requests for extra features for Organize Series and have done some custom development work for clients who have the resources to hire me to add stuff.  I simply have not had the time to write up all the features that people request without getting paid for it. Here's the thing though, there's been some really good ideas and I realize that not everyone is able to afford what it costs for custom development.  So I decided what I'd do is move to a pay-as-you-go model for new features.  I've created a website for [Organize Series](http://organizeseries.com) where you'll find all kinds of different add-ons to Organize Series that you can purchase and use along with the core plugin. You'll want to head on over there and see if there's anything you are looking for.  I've priced the add-ons at a low price so new features are more accessible to those of you who can't afford custom development work and yet still provides a way for me to get paid for the work I've done.  It's a win-win!!  You'll also want to check out the basic-support package available as well.

If you don't see an add-on that has the feature you want for Organize Series, write up a request here on the [WordPress Support Forums](http://wordpress.org/support) (make sure you tag the post with organize-series) and I might just develop it as the next add-on!!

**Sounds like you are going to be moving to a paid model for Organize Series...does that mean I'll eventually have to pay for all the goodness Organize Series offers?**
Nope.  The CORE Organize Series plugin WILL ALWAYS REMAIN FREE.  The only thing that I'm charging for going forward is support and the add-ons.
This will also provide extra incentive for me to keep the core plugin *rock'in* for users and always up to date with the current version of WordPress.  I will also work hard at fixing any bugs etc. that get discovered.  The core plugin has EVERYTHING a user needs to organize series on their blog and that will never change (i.e. I won't remove features from the core).  However, from time to time there are users who want something extra (e.g. the ability to "group" series) and thats where add-ons come in.  From time to time I will release free add-ons as well, you'll want to bookmark [OrganizeSeries.com](http://organizeseries.com) so you'll catch the news when that happens.

Oh and one more thing.  Organize Series will always be GPL and so will ALL the add-ons that I write.  Just thought I'd put that in here.



== Support ==

There have been some changes to how I provide support for Organize Series. Here are the different options for support:

1. Post any support questions on the WordPress Forums at [wordpress.org](http://wordpress.org/support). I will keep an eye on the forums for bug reports but will NOT be actively responding to usage/installation help.  If I don't respond to forum posts I'm not being rude or snubbing you, I just simply don't have time to respond because priority goes customers who have paid for support (see below).

1. Subscribe to the Paid Basic Support Plan I am offering for Organize Series at [organizeseries.com](http://organizeseries.com).  This will give access to the private support forums that I will be active on.

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

= to 2.2 from earlier versions =
This version is a major update to the plugin and introduces big changes to the core code and structure of the plugin.  Care has been given to make the upgrade as seamless as possible for users of previous versions but you will definitely want to read over the changelog to see if there are any changes that will impact you.

*AS ALWAYS - BACKUP YOUR BLOG FIRST BEFORE UPGRADING!!*


== Changelog ==

= 2.5.2 =

- fix deprecated use of `get_bloginfo`
- fix issues with modifying series part not sticking for posts already assigned a part.
- fix uninitialized offset errors
- ensure organize series localization is setup properly for wp.org translation.

= 2.5.1 =

- Fix "Warning: Creating default object from empty value"
- Implement hook for handling the new split term behaviour in WP (also adds some fixes for issues in the database with split terms and correct part ordering).  Thanks to Marcus Tibesar for help troubleshooting using his site.
- Make sure to overwrite empty series part if exists (previously saved empty series part would not get overwritten if updated to have a part on subsequent saves).
- Fix PHP Fatal error: Call to a member function get() on null (ht [subharanjanm](https://github.com/subharanjanm) for the initial fix)
- Fix Image modal for selecting new image not loading when editing a series in WP 4.5
- fix enqueueing scripts correctly when in admin
- fix `series_description` not returning the description for the series being viewed on a series archive page.
- tested to ensure things work with WP 4.5

= 2.5.0 =

* Drop use of PHP4 style constructors
* Enqueue styles properly. (ht [Michael Cannon](https://wordpress.org/support/profile/comprock) )

= 2.4.9 =

* Fix broken series-toc widget with list option.
* Add argument to `series_toc_paginate()` function for setting pagination type.
* escape add_query_arg()

= 2.4.8 =

This release primarily ensures Organize Series is compatible with 4.2 (especially with some of the taxonomy api changes).

* Fix broken series filter on posts page with WordPress 4.2
* Ensure compatibility with taxonomy api changes coming with WordPress 4.2

= 2.4.7 =

Along with verifying that Organize Series work with the latest WP version there are a couple bugfixes and an enhancement in this release.

**Bugfixes**

* fix bug where users without 'manage_series' capability were able to add additional series in the post editor.
* fix numerous php notices and warnings with WP_DEBUG on.


**Enhancements**

* Series can now have their parts set on draft, private, and scheduled posts (and the parts will stick).


= 2.4.6 =
**DO NOT UPGRADE TO THIS RELEASE UNLESS YOUR INSTALL OF WORDPRESS IS AT VERSION 3.7**
An important fix for WP3.7 users is included in this release. The bug affected being able to actually attach posts to a series (or update posts attached to a series).  Please upgrade asap if you are using WP3.7.  If you are on earlier installs of WordPress this version of Organize Series WILL not work - so make sure you update to WP3.7 before using this version.

I pushed this release out sooner than planned because of the above bug so there are some things that I planned for this release that didn't make it in.  However, here's all the things that did make it in:

* fix for saving posts to a series in WP3.7
* fix for editing a post with a series using quick-edit clearing series info
* some more series part numbering fixes (when series are draft or scheduled)
* fix for adding existing post to an existing series not sticking
* fix for organize series sometimes causing plugin activation errors for other plugins when they are activated
* fix for improper enqueuing of Organize Series scripts and add cache busting so any changes replicate with new versions of Organize Series.
* fix some PHP notices and warnings
* fix for series icon not saving
* remove display of series navigation on a static frontpage (ht [schneidr](https://github.com/schneidr) ) - this was [via a pull request on github](https://github.com/roughsmoothengine/organize-series/pull/4), [join the party!](https://github.com/roughsmoothengine/organize-series)

**New Feature**

* whatever is set as the "Series Custom Base" in the series options page not only becomes the series endpoint when permalinks are on but ALSO now the queryvar (`?series=`) when permalinks are off.  This gives a lot more flexibility for how "series" are referred to on your site (with the side effect of also being able to eliminate conflicts with other plugins using "series" as the query_var).

= 2.4.5 =
A few fixes in this release

**Bug Fixes**

* fix for the issue where editing a post caused the series to reorder itself (see the report [here](http://code.roughsmootheng.in/redirect?http://wordpress.org/support/topic/on-editing-a-post-the-series-reorders-it-self?replies=3#post-3806479))
* fix style issue for text field in "Post Title in Widget" series meta box.
* fixed some "undefined offset" errors when adding a new post (seen with WP_DEBUG on)
* fix for issue where a post could not be removed from a series via Quick Edit, which also fixed adding posts to a series in quick edit
* changed the method for loading orgseries css styles/scripts to properly use wp_enqueue_scripts hook.

= 2.4.4 =
Alot of fixes and a couple new features with this release!  We're also a bit late, but we've tested Organize Series with WordPress 3.5 (and 3.5.1) and verified that everything works well with the latest verison of WP. Read on for the goods.

**Bug Fixes**

* Fixed an issue with the Jetpack plugin that caused the series-meta strip to disappear on single post pages when the Jetpack sharing module was enabled.
* Fixed an issue where the Series icon (image) was not getting saved on the first save while creating a new series on the Manage series page.
* Various minor compatibility fixes for WordPress 3.5 (and 3.5.1)
* Various fixing up of PHP notice/warnings throughout the plugin. (8 fixes)
* A problem with posts losing series information when saved within the Editorial Calendar plugin exposed an issue with how series information is saved with posts.  This was fixed and will apply to any plugins that use `apply_filters('save_post')`


**Features**

* **Paging on Series Table of Contents page**  This is been a much requested feature and we're pleased to make it available.  This release introduces a new option in the series options page for setting how many series are displayed in the table of contents.  As well, we've released a new template function `series_toc_paginate()` that allows you to indicate where you want the pagination links displayed.  You can see an example of the new function in the updated `seriestoc.php` file included with Organize Series.  Note: if you are using permalinks, make sure you regenerate your permalinks either by saving the series options page or visiting the WordPress permalinks setting page and saving the permalinks.  That will flush the wp_rewrite array and make sure paging works as expected.
*  **Ability to change "Other Posts in Series..." text in Series TOC widget** This is another requested feature from users.  Basically in the Series Table of Contents widget, you now have the ability to modify the text that is displayed in the widget when a single post belonging to a series is viewed.  Some users have been using Organize Series for "Issues", or "Chapters" and this allows you to keep the language consistent.
* **More css styling options** Eventually we're going to have an addon available that allows you to style series elements in the backend.  In the meantime, we've dropped in a couple of .css options for you to try with your theme.  One is for dark themes, and one is for light themes, and then there remains the default one that has always been included with Organize Series.  You can try out and select the new .css options via the Series Options page.

= 2.4.3 =
Sorry folks... I released 2.4.2 with some bugs that needed fixed.  If you're not using the Organize Series Custom Post Type addon you don't need to upgrade but if you plan on purchasing and using it then you need this version of Organize Series.

= 2.4.2 =

* added filters and modifications to allow the new Organize Series Custom Post Type addon to work (you can obtain the addon at OrganizeSeries.com).  The addon makes it possible to use Organize Series with custom post types.

= 2.4.1 =

This release is primarily a "spit-and-polish" release that fixes up nagging little bugs in Organize Series.  However, we did slip in a few new features!

* multiple php warnings, error notices were fixed.
* fixed bug with add new button in Organize Series not working (on edit posts page)
* properly escaped html on series navigation characters
* in Series Options page, added in a link for helping with translating Organize Series.
* NEW FEATURE: Added in an "orderby random" option for the Latest Series widget.
* converted all variable language domain references to concrete string (proper usage).
* fixed up some usage of deprecated wp functions.
* using `plugin_dir_path()` instead of `plugin_basename()` in certain cases.
* changed xlmns namespace for the "series" term from "unfoldingneurons.com" to "organizeseries.com"
* NEW FEATURE: Series part ordering now tracks unpublished posts in a series with helpful ui on the administration side.  Coming in a future release we'll also provide a way for you to show readers all the planned posts in a series.

= 2.4.0 =
Bugfix

- WP 3.0 introduced new way of adding meta boxes. This release adopts that format.

= 2.3.9 =

Bugfixes and WP 3.3 compatible.  I was hoping to get a new feature in this release but ran out of time and thought it more important to get a bugfix out.  Hopefully a new feature by Christmas :)

* fixed a bug with improper inclusion of series id. This primarily surfaced when using %token_replace% for certain items (with add ons) but may have caused problems elsewhere as well. ht to [ldsphilosopher]( http://www.ldsphilosopher.com/) for the catch.
* fixed a .css error that messed up tiny-mce editor
* changed the way series_icons get uploaded so the url text field is disabled (makes less likely for mistakes).
* fix to make sure js in series.js is loaded on document ready
* fix for series_icon not getting properly saved in some circumstances (when creating a new series)
* selected series icon is cleared when the submit button is pressed for the "Add Series" form.
* changed wording of "Upload Image" to "Select Image" for the add new series form.  Better reflects what happens.
* minor fix for pue-client.php to hopefully fix PHP errors in certain cases.
* switched usage of jquery `.live()` to `.on()` because `.live()` is deprecated in jquery 1.7
* adding 'series' custom column via 'manage_posts_columns' hook will only fire when the displayed post list is for the "post" post_type (since by default Organize Series is only setup for the "post" post_type);

= 2.3.8 =

* major fix to a flaw on the automatic upgrades backend for addons.  Current implementation has your site firing off the api to ping OrganizeSeries.com far to frequently than necessary and thus creating unneccessary overhead for your site.

= 2.3.7 =

* ensure full compatibility with WordPress 3.2

= 2.3.6 =

Bugfixes and backend changes

 * OrganizeSeries addons are using a new framework I developed for automatic upgrades which are now served from Amazon S3 (reducing load on OrganizeSeries.com).  This affects the core plugin because some of the framework is included with the core.
 * New Organize Series API key field displayed for all users of Organize Series.  If you have purchased an addon and at least the basic support purchased you will receive an API key that you enter in this field to enable automatic updates via the WordPress backend of purchased addons (which are not hosted on the WordPress repository).
 * changed the way the series_navigation templates work so the &lacquo and &racquo aren't hardcoded. In the process I made things more flexible...
 * %previous_post_custom% and %next_post_custom% tokens are now deprecated (however existing users of those tokens will still find they work - for painless upgrades).
 * %next_post% and %previous_post% tokens now take their values from the Custom Next Post Text field and the Custom Previous Post text field. If those fields are empty then the corresponding post titles are automatically added.
 * The "Custom Next Post Text" and "Custom previous navigation text" fields are now token enabled. Default settings show '%post_title% >>' and '<< %post_title%' for corresponding fields. The entire contents of these fields will still be linked to the next and previous post.

= 2.3.5 =

Note that Organize Series requirements have been bumped to WordPress 3.1 from WordPress 3.0.  It should still work on WP3.0 but it will no longer be supported for versions prior to WP3.1.

Bugfixes

 * fixed SSL error when Organize Series is used on a WordPress install using https:// ( props [Scott Elkin](http://scottelkin.com/) )
 * Removed the hardcoded "<<" for the series_toc browser title.  This leaves it up to the user to control the characters used via the "Series Table of Contents Title" field in the Series Options page
 * fixed scheduled, pending, and draft posts not updating the total parts in a series when being published.  This bug has resurfaced various times with different versions of Organize Series.  I think I nailed it's coffin shut this time!
 * The Organize Series news feed in the Series Options page was using a deprecated file in WordPress.  It's now using the simple-pie class.  Also of note is I changed the location of the news feed in the sidebar along with adding a plug to rate Organize Series on WordPress.org (if you're reading this, please help promote Organize Series by rating it or indicating it works for your WordPress version.  Your help with this will help gain more users and in turn help with development incentive.  Thanks!)
 * Image Uploader for Series Icons (via the "manage series" page) should now properly display the "Use as Series Image" button.  If it still doesn't appear fixed, make sure you refresh your browser caches so the new javascript file gets loaded.

Minor New Feature

 * A settings link has been added to the Organize Series listing on the Plugins admin page.  This makes it much easier to get to the Series Options page after activating/updating the plugin.


= 2.3.4 =

Bugfixes

* fix for series-toc-title not sticking for the page title on the series-toc page.

* fixed php warnings on series-options page related to undefined/isset variables.

* fixed a typo affecting automatic upgrades for Organize Series Addons.

* fixed a few deprecated functions hooking into WordPress.

= 2.3.3 =

Bugfix

* fixed bug where filtering the posts on the edit post screen excluding series did not return expected results

* Also fixed the reported bug where site searches only returned results in series.  Searches should work as expected.

= 2.3.2 =

Bugfix release

* fixed typo with update routine that could affect some updates from earlier versions.

* fixed `wp_postlist_display()` so if it's being used manually it respects the setting for `auto_tag_toggle` in the series_options page. (ht charles)

* changed the required capabability for viewing the series options page to 'manage_options' from the custom 'manage_series'.  'manage_series' capability was assigned to editors along with administrators.  The options page should really be limited to administrators as usual.

* fixed potential 401's happening with search engine robots on pages containing the series widget if the series_url was changed from the default 'series' via the series options page.  (ht [tang](http://www.tangsworld.de/) )

* fixed a bug with `get_series_posts()` that in some cases results php errors.

= 2.3.1 =

Bugfix release

* version 2.3 resulted in [some problems](http://wordpress.org/support/topic/organize-series-23-upgrade-messes-things-up?replies=18) in certain cases for people upgrading from an earlier version of Organize Series.  This release *should* take care of that for those of you with the problems.

* fix for potential function name collision with other plugins. ([see report here](http://wordpress.org/support/topic/514065))

* removal of [wphelp info](http://wphelpcenter.com/) since it is [being discontinued](http://alexking.org/blog/2011/01/25/helpcenter-shutting-down).

= 2.3 =

Bugfixes and Behind the scenes updates:

* One of the major changes in this version of Organize Series is that the "series_part" custom field is now hidden when creating or editing a post.  The series meta-box is still there, and series parts still use custom fields but I've taken advantage of a little known feature in WordPress to clean things up on that page.

* This version brings in a couple more hooks and filters and also lays the groundwork in other areas for some more future addons planned for Organize Series.

* This version also fixes some minor bugs and streamlines the way things work behind the scenes (especially the Quick-edit feature on the `edit.php` page).

* Improved the usability of the "add new series" input in the series-meta box on the create/edit post screen.  When adding a new series it will appear at the top of the list of series instead of at the bottom as before.

* For those of you using template tags for custom implementation of Organize Series (i.e not having it automatically done via the series options) the following changes have been made that are important to take note of:

 * `wp_postlist_count()`: Previously you could just use `wp_postlist_count()` in the loop and get the number of posts in the series that a post belonged to without passing the series id (i.e. `wp_postlist_count('39')` where "39" is the id of the series)).  Now if you can still have Organize Series to dynamically figure out the series id but to do it you need to do this -> `wp_postlist_count(false,true)`;

 * `wp_series_part()`: Previously you could just use `wp_series_part()` in the loop and get the part the post was in a series that a post belonged to withouth including the series id (see how it worked for wp_postlist_count() above).  Now, to do this you need to use the following format -> `wp_series_part(0,0,true)`

 * `wp_series_nav()`: Same changes as above two functions.  To have the nav dynamically generated without knowing the series_id for the series a post belonged to, use the following format (leaving other options at defaults) -> `wp_series_nav(null,true,false,false,true)`

* You can view all the specifics of the changes and cleanup that's been done via [this changeset](http://plugins.trac.wordpress.org/changeset/335684/organize-series)


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

=== Organize Series WordPress Plugin ===
Contributors: nerrad
Tags: category, series, organize, post
Requires at least: 2.3
Tested up to: 2.5
Stable tag: 2.0.1

A plugin for managing the article series you write.

== Description ==

The *Organize Series plugin* helps with the organization and presentation of articles/posts you write as part of a series. It helps make it easier for readers of your blog to discover all the series you've written and also to easily find post that are part of the same series. 

*Organize Series* actually **adds** a new taxonomy to the WordPress taxonomy structure so your blog would have "categories", "tags", and "series" as the taxonomy types.

Make sure you check out the features section in the "other notes" tab for all the neat stuff you can do!

== Installation ==

**NOTE: For upgrading from earlier versions of Organize Series (1.6.3 and earlier) make sure you check out the upgrade section in the "other notes" tab. These instructions are for fresh installs of Organize Series.**

1. MAKE SURE YOU BACKUP YOUR WORDPRESS DATABASE (that's all in caps for a reason - nothing *should* go wrong but it's a good precaution nevertheless!!)
1. Download the File
1. Extract to a folder in `../wp-content/plugins/`. The orgSeries folder can be named whatever you want but the default is "organize-series".  The final structure would be something like this: `../wp-content/plugins/organize-series/--and all the orgSeries plugin files/folders--`
1. Activate orgSeries on your WordPress plugins page.
1. The Series-Icons feature uploads to the default upload directory you've set for your WordPress installation (in Options/Settings->Miscellaneous).  If you want to use series-icons its **important** that you uncheck the "Organize my uploads into month- and year-based folders" box.  
1. Visit the "Options/Settings->Series Options" page to initialize all the default settings.

That's it!  You might want to read through the Series Options page (in the "settings" menu of WP 2.5 or "options" in earlier WP versions) and the Manage->Series Page.  You'll also notice that your "write/edit" posts page now has a handy dandy series box on it for adding your posts to a series (and creating new series on the fly).

For support please visit [the support forums](http://unfoldingneurons.com/forums/forum/organize-series-wordpress-plugin "For help click here!").

== Frequently Asked Questions ==
You know what?  You ask the questions, I'll put the most common ones here.

== Upgrading ==
If you are visiting this section then you are probably a user of one of the older versions of Organize Series and have been waiting for me to finally get something out the door that is compatible with WP 2.3+ First, thanks for being patient - I've been frustrated myself with the time it's taken but I think it's been worth it!  The good news is that you can import all your series structures from your old setup into the new series taxonomy!!  Just follow these instructions carefully.

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

The screenshots section hasn't been working properly for me.  Feel free to visit the [Organize Series Plugin Page](http://unfoldingneurons.com/neurotic-plugins/organize-series-wordpress-plugin) for the goods.

== Features ==

The following is a brief overview of all the features in Organize Series.  For more indepth coverage of the features of Organize Series you can follow the [Organize Series Usage Tips Series](http://unfoldingneurons.com/series/organize-series-usage-tips) on my blog. Better yet, why not suscribe to the [usage feed](http://unfoldingneurons.com/series/organize-series-usage-tips/feed)?

#### Integration with WordPress taxonomy system
Beginning with version 2.3, WordPress introduced a database and core change that resulted in a new taxomony system.  The core WordPress has two taxonomies - "categories" and "tags".  Organize Series introduces a new taxomony "series".  Thanks to the rich api provided to plugin authors Organize Series takes advantage of (as much as possible) the built-in WP goodness!  As an added bonus - plugin developers can expand on what Organize Series offers by interacting with the new series taxonomy and the built-in filters/hooks.

#### More "organize" for taking care of your series...
Check these additions to your WordPress administration:

+	A "Manage Series" page.
  Add, delete, edit all your series on one handy dandy page.  Functioning much like the "Manage Category" page, Organize Series makes it easy to keep track of all the series you are writing.
+	Integration with the Manage->Posts page.
	Now it's easier than ever to keep track of what posts have been added to series in a familiar setting.  Even better, you can also filter the post list by series (and in combination with other filters in WP 2.5) giving you a quick way to see all the posts you've already added to the series.
  
#### Add "prettify" recognition to your series...
Easily associate images with your Series via the Manage->Series page.
 
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

== ChangeSet ==
2.0.1 - some fixes so that changing the `SERIES_URL` definition in orgSeries.php actually works (you can use this to pick how you want the series-archive permalinks to look - important for avoiding conflicts with existing category permalinks...)
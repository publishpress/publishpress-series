=== PublishPress Series Publisher ===
Contributors: nerrad
Donate link:https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=7871313
Tags: category, series, organize, mass publish, post, taxonomy, issues, magazine
Requires at least: 3.7
Tested up to: 3.7
Stable Tag: 2.2.4
License: GPLv2 or later

An ADD-ON for the PublishPress Series Plugin that enables easy bulk publishing of all posts in a series at once.

== Description ==
**Important** PublishPress Series Publisher is an ADD-ON for the [PublishPress Series Plugin](http://wordpress.org/extend/plugins/organize-series) and thus REQUIRES PublishPress Series.

**NOTE: PublishPress Series Publisher 2.2.4 has a bug fix in it for WP3.7.  In order to use PublishPress Series Publisher 2.2.4 your site MUST be running WordPress 3.7**

This plugin used to be bundled with PublishPress Series but it makes it easier for maintenance and updatings with it being on it's own.  

PublishPress Series Publisher is the result of some custom work I did for Amanda Giles who graciously agreed to make this available to all PublishPress Series users. This plug-in allows you to quickly publish all posts linked to a Series. The plug-in works by adding a Status to each Series. If the status for a Series is "Unpublished", all posts tied to that Series can be individually published, but those posted will be "held" until the Series itself is published. Once the Series is "Published", all previously published posts in that series will be immediately published and visible on your site. Any posts in a Series still in the Draft status will be left as such and will not be published (until individually published). If you have a large number of articles, this saves time and provides a clean "publish" with no accidental broken links from unpublished posts.

== Installation ==

Be sure the [PublishPress Series plug-in](http://wordpress.org/extend/plugins/organize-series) is activated first, then activate the PublishPress Series Publisher plug-in. If you have any Series already created, they will default to the "Ignored" state which means all posts are left in their current post status (Draft, Published). If you want to remove all posts in a Series from your site, you can "Unpublish" the Series through the Settings > Publish Series. Likewise, if you want them to reappear on your site, you can “Publish” the series from the same screen.

== Frequently Asked Questions ==

**Why would I want to use this plugin?**
The main feature is the ability for a editor to easily publish a series of posts all at once rather than holding back a series from publishing and then having to publish each post individually when it's time to publish.

== Support ==

Here are the different options for support of this plugin:

1. Post any support questions on the WordPress Forums at [wordpress.org](http://wordpress.org/support). I will keep an eye on the forums for bug reports but will not be actively responding to usage/installation help.

1. Subscribe to the Paid Basic Support Plan I am offering for PublishPress Series and all add-ons at [organizeseries.com](http://organizeseries.com).  This will give access to the private support forums that I will be active on.

== Usage ==

The following are some basic usage instructions

= Using the Plug-In for the First Time =

Be sure the PublishPress Series plug-in is activated first, then activate the PublishPress Series Publisher plug-in. If you have any Series already created, they will default to the "Ignored" state which is means all posts are left in their current post status (Draft, Published). If you want to remove all posts in a Series from your site, you can "Unpublish" the Series through the Settings > Publish Series. Likewise, if you want them to reappear on your site, you can "Publish" the series from the same screen.

= Creating a Series for Later Publishing =

With this plug-in activated, you will have an additional check box to allow creation of a Series in an "Unpublished" state. This will allow you to create posts tied to Series without the posts actually being published to your site yet. See below.

= Creating a new Series =

* Go to Posts > Manage Series
* Begin typing on left side to create a new series
* Decide whether the Series should be "Unpublished" (defaults to Ignored [unchecked])
* Click Add Series to create the Series

Note: If you create a Series on the fly via the Add New or Edit Posts screen, you will not have the option of creating an "Unpublished" series. This means your posts will appear on your website as soon as they are individually published - unless you "Unpublish" Your Series immediately (see below).

= Publish/Unpublish a Series =

A Series, unless specified as "Unpublished" starts in an "Ignored" state. This is essentially the same as "Published" as posts which are published will appear on your site normally (if the posts have been individually published). You can change the status of a Series by going to the Posts menu and selecting Publish Series. On this screen you will see all Series listed and you have the ability to Publish or Unpublish an issue by clicking on the text link to the right of the Series name.

When you "Publish" a Series, only posts already marked for publishing are now actually published and visible on the site; posts in draft status will stay as [unpublished] drafts although they still can be published at a later time. If you wish to Publish, there is an additional step. Clicking Publish will take you to the Publish Series screen which will allow you a chance to reorder the posts. Reordering posts sets the series_part custom field value which is used for sorting within some PublishPress Series template tags. You must then click "Publish Issue" on the right side of the screen to complete the process. Once you do this all posts in the Series will be published no matter what their previous status (draft, pending review, or published).

== Changelog ==

= 2.2.4 =

**NOTE: PublishPress Series Publisher 2.2.4 has a bug fix in it for WP3.7.  In order to use PublishPress Series Publisher 2.2.4 your site MUST be running WordPress 3.7**


= 2.2.3 =

* ensure full compatibility with WordPress 3.4.2
* fixes some php warnings/notices
* made sure language domain is a concrete string instead of variable reference in all translated strings.

= 2.2.2 =

* ensure full compatibility with WordPress 3.2

= 2.2.1 =
bugfixes

* removed/updated deprecated functions and general cleanup.

= 2.2 =

- PublishPress Series Publisher is removed from being bundled with PublishPress Series and is released as it's own download on WordPress.org.
- modifications made to the plugin for hosting on WordPress.org.
- modifications made for working with PublishPress Series 2.2
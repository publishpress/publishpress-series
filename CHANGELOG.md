The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

v2.17.1- 2025-12-10
* Fixed: Patch for XSS Vulnerability

v2.17.0- 2025-11-19
* Added: Series Post Navigation editor #998
* Added: Add a View link from the Series Order screen #984
* Added: Add a checkbox to migrate terms to new taxonomies
* Fixed: Border color does not match on Post List Box
* Update: ES-FR-IT Translation #1002

v2.16.0- 2025-10-29
* Added: Series Post Details editor #969
* Fixed: Resolve PHP warning on Post List Box editor #944
* Fixed: Custom template on Post List Box not working #948
* Fixed: Fix domain in some strings (credit @DAnn2012)
* Changed: Increase shortcode size on Post List Box #951
* Changed: Remove description, image, order on series creation #949
* Changed: Remove shortcode link in Post List Box editor #954
* Changed: Allow to choose different fallback featured image #953

v2.15.0- 2025-09-12
* Added: New Post List Box Editor #933
* Changed: Remove Old translation file #912
* Changed: Remove "Bulk Actions" from Series Order #660
* Changed: Update promo banner using new style #915
* Changed: Clean up "Series" screen whitespace #940
* Changed: Enable Multiple Series by default #937
* Changed: Update "Templates" tab #939
* Fixed: Bulk edit is not available for Series on custom post type #731
* Fixed: Excerpt not returned on single series post #897
* Fixed: Missing translationg strings in Series Free #879
* Fixed: Only public post type to show #730
* Fixed: Scheduled posts publish with no part, #821
* Fixed: Update banner library #914

v2.14.0- 2025-07-14
* Added: New Promo for Pro version #901
* Fixed: Function _load_textdomain_just_in_time #900
* Fixed: Warning when SiteOrigin active #866
* Fixed: Hide the metabox if there are no Series #902
* Fixed: Hide Filter on CPT #875

v2.13.0- 2025-02-19
* Fixed: WordPress database error on install, #831
* Fixed: Undefined variable $current_post_id, #877
* Fixed: PHP Warning Undefined array key "post_series", #865
* Fixed: Deprecated message on EDD Vendor, #878
* Update: Update WordPress Reviews library to 1.1.20, 860

v2.12.0- 2023-08-17
* Changed: Replaced Pimple library with a prefixed version of the library to avoid conflicts with other plugins;
* Changed: Replaced Psr/Container library with a prefixed version of the library to avoid conflicts with other plugins;
* Changed: Change min PHP version to 7.2.5. If not compatible, the plugin will not execute;
* Changed: Change min WP version to 5.5. If not compatible, the plugin will not execute;
* Changed: Updated internal libraries to latest versions;
* Changed: Refactor some occurrences of "plugins_loaded" replacing it by a new action: "plublishpress_<name>_loaded" which runs after the requirements and libraries are loaded, but before the plugin is initialized;

v2.11.5- 2023-07-20
* Update: Allow users to re-order drafts series, #815
* Update: Add series metabox order settings, #814
* Update: Add series upgrade action button to settings, #810
* Fixed: Scheduled posts publish with no part, #821
* Update: Small text change for migration, #816
* Update: French translation update for Series Free - June 2023, #812
* Update: ES-IT Translation updates June 2023, #809
* Update: Series PRO ES-FR-IT Translation Updates July 2023, #825

v2.11.4- 2023-06-15
* Update: Remove auto series part feature
* Fixed: Series Order is broken with Multiple Series, #774
* Fixed: Editing Series order not updating, #746
* Fixed: Navigation is broken by draft posts, #783
* Update: Remove unpublished series parts in frontend, #782
* Fixed: Text in missing for Multiple Series, #773
* Fixed: Add a link to Manage Series if no series are available, #728
* Fixed: Remove the banner for Blocks, #770

v2.11.2- 2023-05-23
* Update: Fix issue with v2.10.1 and re-release update with below changes:
* Update: Added new token %post_title_short% and %post_title_short_linked%, #523
* Fixed: %post_title_list_short% not working, #523
* Fixed: Broken Post list display for series box style, #691
* Fixed: Classic Widget "Series Table of Content" sorting not working, #715
* Fixed: Taxonomy change not working, #701
* Fixed: Invalid form control with name=’series_part[0]’ is focusable when series part is disabled, #694
* Fixed: PHP Warning error in server log, #693
* Update: Add 'category_ids' and 'series_ids' parameters to [series_post_categories], #709
* Fixed: Sorting series is not working for [series_toc] shortcode, #696
* Fixed: Series Order not working for multiple series, #706
* Fixed: Manual series part not working when Automatic Numbering is enabled, #702
* Update: Series-v2.10.0-ES-IT_TranslationUpdate-5_October2022, #665

v2.11.1- 2023-05-18
* Update: Rollback 2.11.0

v2.11.0 - 2023-05-18
* Changed: Replaced Pimple library with a prefixed version of the library to avoid conflicts with other plugins;
* Changed: Replaced Psr/Container library with a prefixed version of the library to avoid conflicts with other plugins;
* Changed: Change min PHP version to 7.2.5. If not compatible, the plugin will not execute;
* Changed: Change min WP version to 5.5. If not compatible, the plugin will not execute;
* Changed: Updated internal libraries to latest versions;

v2.10.1- 2023-01-04
* Update: Added new token %post_title_short% and %post_title_short_linked%, #523
* Fixed: %post_title_list_short% not working, #523
* Fixed: Broken Post list display for series box style, #691
* Fixed: Classic Widget "Series Table of Content" sorting not working, #715
* Fixed: Taxonomy change not working, #701
* Fixed: Invalid form control with name=’series_part[0]’ is focusable when series part is disabled, #694
* Fixed: PHP Warning error in server log, #693
* Update: Add 'category_ids' and 'series_ids' parameters to [series_post_categories], #709
* Fixed: Sorting series is not working for [series_toc] shortcode, #696
* Fixed: Series Order not working for multiple series, #706
* Fixed: Manual series part not working when Automatic Numbering is enabled, #702
* Update: Series-v2.10.0-ES-IT_TranslationUpdate-5_October2022, #665

v2.10.0- 2022-10-04
* Feature: Add Series and Series Group re-order field with term drag and drop feature #584
* Feature: Add series order link to series and new post selected series #612
* Feature: Add series Groups(Categories) overview page shortcode [publishpress_series_categories] #591
* Feature: Add search box to series metabox #239
* Feature: Add settings to limit series meta display to single page only #588
* Fixed: Only allow valid numbers in series part input #634
* Update: Set series part if empty irrespective of Automatic Numbering settings #636
* Feature: Add filter, search and pagination to "Publish Series" screen #510
* Update: Enable "Show "Series Part"" by default #617
* Fixed: Font styles and font sizes inconsistency on 'Series Table of Contents' widget page #604
* Fixed: Syntax Error on 'Edit Series' page #607
* Update: Simplify the Publish Series screen #614
* Update: Change the menu links inside Series #615
* Fixed: TOC template header and footer deprecated in FSE themes #623
* Fixed: Groups template header and footer deprecated in FSE themes #416
* Fixed: Series taxonomy template header and footer deprecated in FSE themes #409
* Feature: Add "Series Groups" into a Free feature #613
* Fixed: Remove new taxonomy metabox #595
* Fixed: Float left for next post navigation #586
* Update: Series-Translation updates for Spanish July 21 #578
* Update: Series-Translation updates for French-Spanish-Italian #567
* Update: Remove "Create as unpublished"? #655
* Fixed: Series link in "Posts" table doesn't go anywhere #651

v2.9.2- 2022-07-12
* Fixed: Problem with series navigation and List #568
* Fixed: Define plugin addon path from primary plugin file to fix include error #570

v2.9.1- 2022-07-06
* Feature: Added series Table of Content shortcode [publishpress_series_toc], #559
* Update: Disable automatic numbering for new installs #515
* Fixed: %post_date% doesn't work #525
* Fixed: Exclude trash post from publish series "Unpublish all" #527
* Fixed: Small cleanup for "Publish Series" box #497
* Added: New Free vs Pro library #529
* Fixed: Multiple "No Series" in Quick Edit when no series is available #538
* Fixed: Issue with "Update Order" when posts are unpublished #528
* Added: Add a Promo for PublishPress Blocks #541
* Fixed: Issue with scheduled posts and part #552
* Update: Show "Post status" instead of "Last Modified" on the publish series screen #553
* Update: Updated translations #560
* Update: Translation check #557
* Fixed: Error in PHP 5.6.39 #535

v2.9.0.1- 2022-05-17
* Fixed: Series not enable in rest api #531

v2.9.0- 2022-04-21
* Fixed: Better way to re-order posts in a series #377
* Fixed: Improved publish Series display #494
* Fixed: Include a sidebar for Pro #462
* Fixed: Small warning about plain permalinks #496
* Fixed: Update "Series Groups" Menu title #495
* Fixed: Remove publish series menu promo from series menu #498
* Fixed: Re-numbering of posts within the series #477
* Fixed: Publish Series screen in Free version #478

v2.8.2- 2022-04-12
* Fixed: Issue with series menu permission #488
* Fixed: Remove lines and paddings from Series Metabox #484

v2.8.1- 2022-04-11
* Fixed: Custom Post Type Archive not working anymore #474
* Fixed: Issue with Post Title in Widget #473
* Fixed: Small changes to "Display" settings #466
* Fixed: "Series" should be "Settings" #470
* Fixed: Two issues with Series Table of Contents #465

v2.8.0- 2022-03-15
* Fixed: New theme with better design #272
* Fixed: Improved design for "Publish Series" screen #436
* Fixed: Improved design for Series overview page #309
* Fixed: Using "id="primary" for series overview page messes up with primary widget area #353
* Fixed: Improve design for Series taxonomy #395
* Fixed: PHP 8 issue in settings #400
* Fixed: Latest Series widget text #402
* Fixed: New area for Series Table of Contents #403
* Fixed: Series Meta (with excerpts): #404
* Fixed: Small text cleanup in "Display" #406
* Fixed: Improved design for Groups page #394
* Fixed: Move the "Overview" tab #405
* Fixed: Need spacing in Navigation template #418
* Fixed: Styling not working for group and toc pages #421
* Fixed: Simplify the metabox #408
* Fixed: Series Post List Post Title (unpublished) Template: #401
* Fixed: Issue with "the_content" hook #306
* Fixed: Move series order to overview section #424
* Fixed: Disable Series Free when Series Pro is installed #440
* Fixed: "Publish Series" feature only works with "Pending Review" #437
* Fixed: Using "Publish posts in series" produces a 0 for part number #454
* Fixed: Scheduled posts marked with no Part #452
* Fixed: Consistent design for Settings #434
* Fixed: Move series "Add New" metabox to html #433

v2.7.5- 2022-02-07
* Fixed: Stop automatic re-numbering of posts within the series #376
* Fixed: WPDebug shows a bunch of errors in the Settings #381
* Fixed: Notice: Undefined index: series_custom_base #380
* Fixed: Problem with Series Post List box and %series_part% #382

v2.7.4- 2022-01-20
* Fixed: Series option update message

v2.7.3- 2022-01-20
* Fixed: HTML tags being stripped when saving #362
* Fixed: PHP Warning: Undefined array key #354
* Fixed: Design for boxes is missing #352
* Fixed: Issue with translation string #366
* Fixed: Updated Italian, Spanish and French translations #361
* Fixed: Issues with new series layout #360
* Fixed: Don't override taxonomy template when layout is default #349



v2.7.2- 2022-01-05
* Fixed: Make text easier to read #348
* Fixed: Overview page layout description #346
* Fixed: Don't override taxonomy template when layout is default #349
* Fixed: Add capability check for saving options
* Fixed: Santize all form entries
* Fixed: Add and validate all form nonce

v2.7.1- 2021-12-09
* Fixed: PHP notices from the widget #303
* Fixed: Visual bug with the "Series Taxonomy" choice #312
* Fixed: Deprecated notices #307
* Added: Capability to access the "Series" menu #313
* Fixed: "Show Series Table Of Content" setting is broken in widget #308
* Added: Allow users to customize buttons #311
* Added: "Click to view" for "Series Table of Contents URL" #320
* Added: Add series group template to PRO version group addon #202
* Fixed: Sidebar warning for theme without sidebar.php on custom template #335
* Fixed: Improved design for Series overview page #309
* Added: Bulk edit for series #338

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

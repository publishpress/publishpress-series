---
title: "The Series Post Navigation Template"
order: 24
doc_category: series
section: Series Templates
---

# The Series Post Navigation Template

Table of Contents

- [The Post Navigation Template](#0-toc-title)
- [Template tags and Functions for Post Navigation](#h-template-tags-and-functions-for-the-navigation)

One feature in PublishPress Series is a Navigation strip that gets displayed with posts that are part of a series containing links to the next and previous posts in the series. This provides another way for users to click through to other posts in the series.

## The Post Navigation Template [#](#0-toc-title)

This screenshot below shows an example of the navigation template with forward and back arrows.

![Series Navigation](https://docs-media.publishpress.com/publishpress-series/post-navigation-1-b0a614d0df.webp)

*Series Nav Shortcode Output*

You can control the design of this box by going to Series > Settings > Post Navigation, and choosing the “Custom Template” option.

![Post Navigation template](https://docs-media.publishpress.com/publishpress-series/post-navigation-template-02060a075a.webp)

*Nav Options*

You control the output by inserting various tokens, text, and HTML markup.

You can automatically insert the series navigation strip into your blog by using the `%postcontent%` token. In this image above, the `%postcontent%` token is before the rest of the template telling the plugin that you want the Series Navigation Strip to show up after the post-content.

Removing the `%postcontent%` token would keep your post content from displaying. If you don’t want the series navigation strip to show up just remove everything except for the `%postcontent%` token.

`%postcontent%` controls automatic insertion into the full post page views of your blog theme (on `is_single();`pages).

## Template tags and Functions for Post Navigation [#](#h-template-tags-and-functions-for-the-navigation)

Here are the details for customizing the template files:

- `wp_series_nav()` – gets the links for previous OR next posts in a series. This function takes the arguments: series_ID.
- `wp_assemble_series_nav()` – this calls the series navigation template and replaces all the `%tokens%` with the related series information.

For developers who want to dig more deeply into the code, detailed explanations are included in the `orgSeries-template-tags.php` file:

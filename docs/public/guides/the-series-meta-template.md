---
title: "The Series Post Details Template"
order: 26
doc_category: series
section: Series Templates
---

# The Series Post Details Template

Table of Contents

- [Introducing the Series Post Details Layout](#0-toc-title)
- [Series Post Details with PHP](#h-how-to-show-the-series-meta-with-php)

The Series Post Details is an information strip that shows on single posts. This an overview of what part the post is, a total count of the posts in the series, and the name of the series the post belongs to.

It is possible to create a custom template for this display.

## Introducing the Series Post Details Layout [#](#0-toc-title)

You can see the Series Post Details layout in this image below:

![Post Details display](https://docs-media.publishpress.com/publishpress-series/series-post-details-05fd71aee6.webp)

*Series Meta 1*

You can control the design of this box by going to Series > Settings > Post Details, and choosing the “Custom Template” option.

![Post Details template](https://docs-media.publishpress.com/publishpress-series/post-details-template-348a7e78d6.webp)

*Series Meta*

## Series Post Details with PHP [#](#h-how-to-show-the-series-meta-with-php)

By default, the series post details appears wherever the post that is a part of a series is displayed.

Here’s the function you use to call the Series Meta template:

```
wp_seriesmeta_write() 
```

To use the Series Post Details feature in your theme make sure you echo it. For example:

```
<?php echo wp_seriesmeta_write(); ?>)
```

For developers who want to dig more deeply into the code, detailed explanations are included in the `orgSeries-template-tags.php` file:

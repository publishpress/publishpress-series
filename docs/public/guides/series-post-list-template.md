---
title: "The Post List Box Template"
order: 16
doc_category: series
---

# The Post List Box Template

The [Post List Box](https://publishpress.com/links/post-list-boxes) is used to display all posts belonging to a series.

You can design your own layout of this feature via the user interface thanks to [the Post List Boxes screen](https://publishpress.com/knowledge-base/the-post-list-box/). You can customize the design of this box by going to Series > Settings > [Post List Boxes](https://publishpress.com/links/post-list-boxes), and choosing the “Custom Template” option. The Custom Template feature accepts tokens.

![Post List Boxes template](https://docs-media.publishpress.com/publishpress-series/post-list-boxes-beda3f406d.webp)

*[Post List Box](https://publishpress.com/links/post-list-boxes) Shortcode Customize*

Notice the `%tokens%` that are used:

- `%series_icon_linked%` – If the series has an image associated with it then it will be displayed in acontainer with the class “center”
- `%postcontent%` – the location of this tag tells Organize Series that I want the Series PostList Template contents to be placed before the post content of the post.
- `%post_title_list%` – is a marker token that tells organize series where I want the list of posts in that series to be placed.

Notice how `%post_title_list%` is surround by `<ul>` and `</ul>` tags? The Series Post List Title Template indicates how each post in the list of posts will be output..

The functions (“template tags”) that call these templates are:

- `wp_postlist_display()` – calls the Series Post List Template and replaces the tokens with series related information.
- `get_series_posts()` – alls the contents of the “Series Post List Post Title Template” and “Series Post List Current Post Title Template” and replaces the `%tokens%` in them. It does have arguments that can be passed to it and if you must get into it now, it is documented in the `orgSeries-template-tags.php` file

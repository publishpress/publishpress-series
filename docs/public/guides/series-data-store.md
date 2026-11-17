---
title: "Where is PublishPress Series Data Stored?"
order: 10
doc_category: series
section: Series for Developers
---

# Where is PublishPress Series Data Stored?

This guide explains how [PublishPress Series](https://publishpress.com/links/series) data is stored in the WordPress database. Let’s take a look at a site that has three series.

![Three Series](https://docs-media.publishpress.com/publishpress-series/three-series-711f4f54c3.webp)

*Three Series*

You will find these series stored in the `wp_terms` table in your site’s database. Series uses the default WordPress taxonomy system to organize content. If you are using the Series Groups feature, you will the groups are all stored in this table.

![Series Terms](https://docs-media.publishpress.com/publishpress-series/series-terms-57cd56467f.webp)

*Series Terms*

You will find more information about each series stored in the `wp_term_taxonomy` table.

![Series Taxonomy](https://docs-media.publishpress.com/publishpress-series/series-taxonomy-89d35c864a.webp)

*Series Taxonomy*

When you are editing a post and choosing a series, you are looking at a taxonomy metabox:

![Series Taxonomy Metabox](https://docs-media.publishpress.com/publishpress-series/series-taxonomy-metabox-b0875d2f6a.webp)

*Series Taxonomy Metabox*

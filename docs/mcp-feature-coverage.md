# Series MCP feature coverage

| Native area | Discovery and operations | Access and scope |
| --- | --- | --- |
| Series and Series Categories | `list-series`, `get-series`, `save-series`, `delete-series` | Taxonomy capabilities, category hierarchy, native icon storage and group assignments; deletion preserves posts. |
| Classic/Gutenberg Series metabox | `get-post-series`, `update-post-series`, `bulk-update-post-series` | Post read/edit, taxonomy assignment, peer numbering permissions, native short title and part setters. |
| Series Order | `list-series-posts`, `manage-series-posts` | Complete counted/pending sets; native date-order tools; all affected posts authorized before changes; Trash is excluded from pending order and unpublish. |
| Publish Series | `manage-series-posts` | Native publication/scheduling, unpublish, explicit post trash and published marker; actual publication/deletion permissions. |
| Menu-order projection | `manage-series-posts` | Shared native target query/persistence, Series token and eligible-post edit permissions. Skipped native targets are unchanged. |
| Automatic displays and legacy templates | Get/update plugin settings | Native defaults/sanitizers, typed API defaults, strict scalar types and partial preservation. Reads use Series management; saves use the filtered native Settings API capability. |
| Series/category URL and archive display | Get/update plugin settings | URL bases, TOC, category theme/plugin rendering and display controls; taxonomy identity migration remains native. |
| Metabox, ordering, CSS, icons and uninstall policy | Get/update plugin settings | Native choices; multisite uninstall policy requires network permission and Super Admin status. |
| Post List Boxes | Layout discovery/create/update/lifecycle | Native field metadata, sanitizers, stored representation and Free/Pro locks. |
| Post Details | Layout discovery/create/update/lifecycle | Native text, position, display, styling and optional per-side spacing. |
| Post Navigation | Layout discovery/create/update/lifecycle | Native link, image, arrow and style fields, including Pro locks and transparent backgrounds. |
| Series Gutenberg blocks | `get-content-choices`, `get-post-series`, `update-series-block` | Native registered Series attributes, supported post types, editor availability and layout access. |
| Pro custom post types | Get/update plugin settings; native taxonomy discovery | Active CPT addon; exact native public/UI post-type choices. |
| Pro Multiple Series | Membership and native transitions | Active addon; per-membership tokens, permissions and numbering lifecycle. |
| Pro Shortcodes and Extra Tokens | Native feature toggles, templates and layouts | Active addons and native rendering; no arbitrary shortcode execution tool. |
| Frontend widgets, archives, feeds and output | Native rendering over configured data | No duplicate MCP rendering implementation or arbitrary widget-option editor. |

Taxonomy migration, storage upgrades, licensing, credentials and whole-site reset/maintenance are native administration boundaries. Lifecycle actions that operate on multiple objects are not transactional. Lists and affected sets are bounded as described in the usage guide. Coverage does not imply that every WordPress or third-party editor operation is a Series action.

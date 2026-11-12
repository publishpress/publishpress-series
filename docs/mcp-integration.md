# Series MCP integration

PublishPress Series registers private actions under `publishpress-series/`. Use PublishPress Hub with the WordPress MCP Adapter to connect an AI client. Enable the required actions in Hub; write access is controlled separately from native Series and WordPress permissions. On WordPress installations without the Abilities API, Series continues to work without registering these actions. PHP 8.2 or newer is required.

## Discovery

Start with `get-content-choices`, `get-plugin-settings` and `list-series`. Use `list-layouts` and `get-layout` to inspect Post List Box, Post Details and Post Navigation layouts. `list-series-posts` returns readable post IDs, part numbers, status and dates; `get-post-series` provides editable membership and Series block state. Generic content search belongs to Hub's WordPress tools.

Settings discovery includes exact keys, JSON schemas and native defaults in API types. Feature discovery includes native Pro control names and enabled states. Checkboxes use JSON booleans, numeric controls and attachment IDs use integers, select controls use the declared strings, and enabled Pro features and post types use lists of strings. Optional per-side spacing accepts an integer or an empty string to inherit the all-sides value. `series_perp_toc` is an integer in the API and a numeric string after native sanitization. Layout color controls use native hexadecimal colors; navigation backgrounds also support `transparent`. Unknown keys, numeric-string IDs, string booleans, invalid choices and malformed nested objects are rejected.

## Safe writes

Read an object's `expected_hash` immediately before editing and supply it to the write operation. Conflicts require a fresh read. Use `dry_run: true` to preview changes without saving. Omitted settings and fields remain unchanged; explicit empty lists clear assignments. An empty settings object is a no-op patch. Template HTML must respect the account's `unfiltered_html` capability.

`save-series` creates or edits Series and Series Categories. `icon_id` uses an existing readable image attachment, and zero clears the icon. `category_ids` replaces the Series category assignments. Deleting a term does not delete its content posts. It requires access to posts with leftover native part metadata, and category deletion requires permission to reparent its children. Categories accept native hierarchical parent IDs; Series do not.

`update-post-series` replaces memberships when `items` is supplied. Omit it to change only the widget short title. For a newly assigned Series, include its current token in `series_tokens`. Native numbering can affect counted peer posts, so those posts must also be readable and editable. A short-title-only patch does not resave memberships or renumber peers. Multiple memberships require the active Pro Multiple Series addon. Part zero requests native placement; unpublished posts remain unpublished. Previews show the native planned part numbers.

`bulk-update-post-series` accepts up to 20 explicit post changes in `items`, with each row using its original post token and new-Series tokens. All rows and the combined affected-post set are validated before writing. The top-level `dry_run` previews the entire batch. Duplicate post targets and malformed mixed patches are rejected. Native numbering is not transactional; inspect `complete`, `applied_ids` and `failed_id` and re-read affected objects after a partial result.

`manage-series-posts` supports complete counted-post ordering, pending-post ordering, publication-date ordering, publishing/scheduling selected draft or pending posts, unpublishing eligible published/scheduled posts in a Series, trashing explicit posts, marking a Series published without changing posts, and projecting part numbers to WordPress menu order. Menu-order sync uses only native published posts with a stored part number; drafts, private posts and unnumbered posts remain unchanged. Pending ordering uses native draft, future and pending posts; unpublish never restores Trash or auto-drafts. Ordering never implicitly publishes drafts. Discovery and Series reads include the site timezone. Schedule dates use the site's timezone and `YYYY-MM-DD HH:MM`. Read `get-series` for the operation token. When status changes affect other Series memberships, supply those Series tokens too. Collections and affected-post sets are bounded at 1,000; use native administration for larger jobs.

`save-layout` creates or patches a native layout using the fields returned by `get-layout`. Pro fields retain their native edition locks. Layout lifecycle actions support trash, restore and permanent deletion; permanent deletion requires prior trash. `update-series-block` adds a native Series block or updates one by its zero-based nested path. Only Series-specific attributes are exposed; unrelated blocks and existing attributes are preserved. Native editor block availability and layout permissions apply.

Bulk operations preflight targets, but native publishing and membership operations are not database transactions. Inspect `complete`, `applied_ids` and any failed target before retrying. An error can follow a partial save; read the current state first.

## Editions and access

Pro extends the shared Free implementation through native hooks. Feature toggles, custom post types, multiple Series, unpublished-post templates and Pro layout controls require their active native dependencies. Feature and post-type changes take effect on the next request. Pro must contain a compatible shared Free dependency.

Series management and settings reads require `manage_publishpress_series`. Settings writes additionally require the native `option_page_capability_orgseries_options` capability, which defaults to `manage_options`. Taxonomy, post, attachment, publication, deletion and editor-lock checks apply to each operation. Network uninstall policy is visible only to native Super Admins with `manage_network_options`, and writes require both checks. Administrator role membership does not override an explicit capability denial. Application-password capability restrictions remain effective.

Connected clients receive authorized Series descriptions, layout configuration and readable post summaries. Credentials, license keys, arbitrary post metadata and complete post bodies are not exported by these actions. Keep sensitive information out of text and template fields you choose to share.

Licensing, credential management, taxonomy-identity migration, historical storage upgrades, and whole-site reset/maintenance remain in native administration. Widgets, shortcodes and frontend rendering use native Series output; the MCP tools configure their data and layouts rather than providing a separate rendering engine. General post restoration and permanent content deletion belong to native WordPress administration or separately authorized core tools.

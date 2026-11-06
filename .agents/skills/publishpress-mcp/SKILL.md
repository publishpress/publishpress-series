---
name: publishpress-mcp
description: Implement or audit PublishPress Series Free and Pro MCP abilities, settings, layouts and editorial workflows.
---

# PublishPress Series MCP integration

Hub owns connection, authentication transport and per-action exposure. Series registers private abilities in `includes-core/Mcp/bootstrap.php` under `publishpress-series/`, guarded by Abilities API availability. Register independently of Hub and outside admin-only paths; category registration must coexist with other PublishPress plugins.

Follow Series' PHP 8.2 requirement and native coding style. Pro bundles Free through `lib/composer.json`; update the dependency requirement normally and never modify vendor copies. Pro extends shared layout definitions through hooks, so edition support belongs in those native definitions rather than a second implementation.

Trace native code before extending a tool:

- Settings: `orgSeries-setup.php::get_default_settings()`, `inc/settings/settings-validation.php`, native tab controls and Pro `orgseries_options` filters. `orgseries_validate($input, false)` sanitizes without persistence side effects; preserve omitted and unrelated values.
- Membership/numbering: `orgSeries-taxonomy.php::ppseries_set_post_series_data()`, native normalization and status-transition hooks. One edit can renumber peers and multiple Series. Authorize every affected object and retain current tokens.
- Publishing/order: `addons/publisher/series_issue_manager.php`. Reuse native publishing, pending order, date sorting and marker helpers. These are not transactions; verify read-back and report partial completion.
- Layouts: native field classes, utility readers, edition-lock filters and `pps_sanitize_layout_settings()`. Derive schemas from control types/options/defaults. Optional spacing permits an integer or empty string; checkboxes use JSON booleans, media IDs integers, select choices strings.
- Series/categories: native taxonomy capabilities and term hooks, icon functions and grouping helpers. Use `orgseries_group_id($id, false)` for inspection without lazy writes. Do not clear omitted category assignments.
- Gutenberg: native registered Series attributes and `Blocks::getSeriesListAttributes()`. Validate editor availability and referenced layout objects; preserve unrelated content and attributes.

Use actual capabilities, object permissions, locks, active addons and application-password restrictions. Administrator role membership never overrides denied capabilities. Settings reads need Series management; saves also need the filtered `option_page_capability_orgseries_options` capability (default `manage_options`); network uninstall policy also requires native Super Admin status and network permission.

Use strict JSON validation before sanitization. Bulk memberships preflight every source and token before writes and preserve honest partial outcomes. Short-title-only updates do not resave memberships. Reject malformed/mixed patches before writes; preserve previews, stale tokens, bounded reads and native read-back. Credentials, licensing, arbitrary metadata and generic shortcode execution are outside these tools.

Run `tests/Mcp/smoke.php` through a released Adapter on disposable Free and Pro fixtures and native numbering regressions after shared-helper changes. Distinguish assembled distributions, dependency components and external HTTP tests honestly. Keep usage documentation and prompt examples current without development narratives or audit-report artifacts.

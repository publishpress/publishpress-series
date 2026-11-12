# Series MCP regression tests

Run `smoke.php` only in a disposable WordPress database named `series_mcp_*`, with the plugin activated and user 1 as the fixture administrator:

```sh
SERIES_MCP_MODE=free php tests/Mcp/smoke.php /path/to/wordpress/wp-load.php /path/to/mcp-adapter
```

Use `SERIES_MCP_MODE=pro` for a Pro distribution containing the current shared Free source. Initialize native activation/defaults before the test process. Use the released Adapter (tested with 0.7.1), intercept `pre_wp_mail` and block external HTTP calls. Fixtures create synthetic content, users and settings. Tests execute `McpTool` and verify native storage; they do not establish an external client HTTP session.

Optional `SERIES_MCP_CAPABILITIES_FILE` points to Capabilities' native `includes/application-passwords.php` for an actual restricted-password component test. This does not substitute for full dependency-distribution or HTTP authentication coverage. Multisite fixtures must network-activate the plugin and exercise network policy separately.

Run `tests/regression/series-part-numbering.php` with `wp eval-file` in the same isolated environment to verify the native numbering workflows. The block preview PHP fixture and block editor spacing JavaScript fixture also run independently.

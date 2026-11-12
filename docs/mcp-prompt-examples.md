# Series MCP test prompts

Use a disposable site and synthetic content for writes. Ask for discovery and a preview before saving.

- “Show the Series settings, supported post types and available Pro features.”
- “Turn off automatic Series navigation without changing Post Details or Post List Boxes.”
- “Create a Series called Learning WordPress, and assign it to the Tutorials Series Category.”
- “Give that Series the existing image attachment 123 as its featured image.”
- “Show the posts and part numbers in Learning WordPress. Move the last published post to the beginning.”
- “Add post 123 to Learning WordPress at part 2, preserving every other post's relative order.”
- “Preview adding posts 123 and 456 to Learning WordPress as a single validated batch.”
- “Set the Series widget title for post 123 to Quick Start without changing its memberships.”
- “Schedule the draft posts in that Series for 09:30 tomorrow in the site's timezone.”
- “Preview unpublishing that Series, then show which posts would change.”
- “Create a Post List Box layout with a blue title and no excerpt. Assign it as the automatic layout.”
- “Show the native Post Details padding fields. Set only left padding to 12 and leave the other sides inherited.”
- “Make the navigation background transparent and keep its other settings.”
- “Insert the native Series Post List Box block in post 123 using that layout. Preserve the rest of the post.”
- Pro: “Enable Custom Post Type Support, then enable Series for Pages on the next request.”
- Pro: “Assign post 123 to two Series, keeping their independent part numbers.”
- Pro: “Customize the unpublished-post title template without changing the other templates.”

Permission checks: retry a preview using a subscriber; an Administrator denied `manage_publishpress_series`; a denied `edit_post` on a peer whose part would change; denied `publish_posts`/`publish_post`; and an application password restricted from Series management. Each must be refused before writes. A post locked by another editor must not be edited. Retry a stale token after changing native content and verify that a fresh read is required.

Format checks: try `"false"` instead of false, numeric-string attachment/layout IDs, unknown fields, invalid select values and a valid field mixed with an invalid one. Verify no part of the invalid patch saves. Check that previews leave native settings, posts and metadata unchanged, and read saved values back through both the tools and native administration.

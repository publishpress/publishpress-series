---
name: Release the Free version (team only)
about: Describes default checklist for the plugin's release process.
title: Release v[VERSION]
labels: release
assignees: ''
---

To release the Free plugin please make sure to check all the checkboxes below.

### Pre-release Checklist
- [ ] Create the release branch as `release-<version>` based on the development branch.
- [ ] Make sure to directly merge or use Pull Requests to merge hotfixes or features branches into the release branch.
- [ ] Run `composer update --no-dev --dry-run` and check if there is any relevant update on any requirement. This won't change the code, just show a simulation of running the update command. Evaluate the need of releasing with this library updated, or if we can add that for a next release.
- [ ] If any update should be included on this release (from previous step) make sure to run the update command only for the specific dependeny: `composer update the/lib:version-constraint`. Make sure to check compatibility with the plugin and what version we should be using. Check if you need to lock the current version for any dependency using exact version numbers instead of relative version constraints. Make sure to add any change of dependencies to the changelog.
- [ ] Check Github's Dependabot warnings or pull requests, looking for any relevant report. Remove any false-positive first. Fix and commit the fix for the issues you find.
- [ ] Build JS files to production running `composer build:js` and commit (if your plugin uses any compiled JS file).
- [ ] Run WP VIP scan to make sure no warnings or errors > 5 exists: `composer phpcs`.
- [ ] Update the changelog - make sure all the changes are there with a user-friendly description and that the release date is correct.
- [ ] Update the version number to the next stable version in the main plugin file and `readme.txt`. Commit the changes to the release branch.
- [ ] Commit the changes to the release branch.
- [ ] Build the zip package, running `composer build`. It should create a package in the `./dist` dir.
- [ ] Send the new package to the team for testing.

### Release Checklist
- [ ] Create a Pull Request and merge the release branch it into the `master` branch.
- [ ] Merge the `master` branch into the `development` branch.
- [ ] Create the GitHub release (make sure it is based on the `master` branch and correct tag). This will trigger a Github action for automatic deployment on the WordPress SVN repo.

### Post-release Checklist

- [ ] Follow the action's result on the [repository actions page](https://github.com/publishpress/publishpress-series/actions).
- [ ] Go to the [WordPress.org plugin page](https://wordpress.org/plugins/organize-series/) double check the information confirming the release finished successfully.
- [ ] Make a final test updating the plugin in a staging site.

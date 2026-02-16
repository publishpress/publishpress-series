The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

[3.0.4] 04 Feb, 2026

- Fixed: Remove dev-workspace folder from the exported code

[3.0.3] 19 January, 2026

- Changed: Update alledia/edd-sl-plugin-updater to version 1.6.24
- Changed: Bump plugin API version to 2.9.0 in composer files
- Fixed: Remove unused container assignment from Language constructor

[3.0.2] 25 July, 2025

- Fixed: Fix creation of dynamic properties in the settings fields base class (#3)

[3.0.1] 30 May, 2023

- Changed: Added include.php to composer autoloader

[3.0.0] 28 April, 2023

- Added: Add new version loader manager files
- Changed: Refactored the namespace to PublishPress\WordPressEDDLicense
- Changed: Removed the Core \ segment in the namespace
- Changed: Stop using composer's autoload, implementing a custom autoloader
- Fixed: Fix composer based setup compatibility issue with non public vendor folders

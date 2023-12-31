# ACF local JSON locator

“ACF local JSON locator” allows a user to specify the save and load points for Advanced Custom Fields local JSON files. By default, the plugin will set the save and load path to `wp-content/acf-json`, but any writable location on disk could be used.

## Description

Requires Advanced Custom Fields (ACF) version 5+.

Local JSON is a featured added in ACF version 5 that stores ACF settings in JSON files. This not only provides a performance boost, but it allows storing field settings in version control (which one defintely wants; code goes up, content goes down).

ACF stores the local JSON in the active theme directory by default, _but_ I believe the data model should be independent of the theme (it makes changing themes that much easier). This plugin changes the save and load point for the local ACF to be `wp-content/acf-json` (or any writable location on disk a user specifies).

There is a similar plugin out there that sets the save and load point to the `wp-content/uploads` directory, but that directory should not be versioned, defeating one of the primary points of using local JSON in the first point.

### Links
* [ACF](https://www.advancedcustomfields.com/)
* [ACF Local JSON](https://www.advancedcustomfields.com/resources/local-json/)

## Installation

1. Ensure Advanced Custom Fields or Advanced Custom Fields Pro is installed.
2. Upload the plugin files to the `wp-content/plugins/acf-json-locator` directory or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin from the “Plugins” page.
4. Update all the field groups to generate JSON files.

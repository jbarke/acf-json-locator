=== ACF local JSON locator ===
Contributors: jeffwbarke
Tags: acf, acf fields, local-json, cache
Requires at least: 4.7
Tested up to: 6.7.2
Stable tag: 2.0.1
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

“ACF local JSON locator” allows a user to specify the save and load points for Advanced Custom Fields local JSON files. By default, the plugin will set the save and load path to `wp-content/acf-json`, but any writable location on disk could be used.

== Description ==

Requires Advanced Custom Fields (ACF) version 5+.

Local JSON is a featured added in ACF version 5 that stores ACF settings in JSON files. This not only provides a performance boost, but it allows storing field settings in version control (which one defintely wants; code goes up, content goes down).

ACF stores the local JSON in the active theme directory by default, _but_ I believe the data model should be independent of the theme (it makes changing themes that much easier). This plugin changes the save and load point for the local ACF to be `wp-content/acf-json` (or any writable location on disk a user specifies).

There is a similar plugin out there that sets the save and load point to the `wp-content/uploads` directory, but that directory should not be versioned, defeating one of the primary points of using local JSON in the first point.

= Links =
* [ACF](https://www.advancedcustomfields.com/)
* [ACF Local JSON](https://www.advancedcustomfields.com/resources/local-json/)

== Installation ==

1. Ensure Advanced Custom Fields or Advanced Custom Fields Pro is installed.
2. Upload the plugin files to the `wp-content/plugins/acf-json-locator` directory or install the plugin through the WordPress plugins screen directly.
3. Activate the plugin from the “Plugins” page.
4. Update all the field groups to generate JSON files.

== Changelog ==

= 2.0.1 =
Release date: 13 April 2025

* Bumped “Tested up to” field to 6.7.2.

= 2.0.0 =
Release date: 31 December 2023

* Added a settings page so users can specify the ACF local JSON save path.
* Refactored the plugin to use classes and Composer’s autoloader.
* Bumped “Tested up to” field to 6.4.2.

= 1.0.1 =
Release date: 18 September 2022

* Bumped “Tested up to” field to 6.0.2.

= 1.0.0 =
Release date: 27 March 2022

* Initial release.

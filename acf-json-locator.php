<?php
/**
 * “ACF local JSON locator” plugin
 *
 * @wordpress-plugin
 * Plugin Name: ACF local JSON locator
 * Description: Set the save and load points for Advanced Custom Fields local JSON files. Defaults to `wp-content/acf-json`.
 * Version: 2.0.1
 * Author: Jeffrey Barke
 * License: GPLv2 or later
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 * @see https://www.advancedcustomfields.com/resources/local-json/
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// Plugin constants.
define('ACF_LOCAL_JSON_LOCATOR_BASENAME', plugin_basename(__FILE__));

// Require Composer autoloader.
require __DIR__ . '/vendor/autoload.php';

// Alias a poorly named class.
use acfLocalJsonLocator\Plugin as AcfLocalJsonLocator;

// Run plugin activation hook.
register_activation_hook(__FILE__, [
  '\acfLocalJsonLocator\Plugin',
  'activate',
]);

// After all plugins have been loaded, run checks (since we depend on
// ACF being installed) and then do things.
add_action('plugins_loaded', function () {
  // Get the instance.
  $acfLocalJsonLocator = AcfLocalJsonLocator::getInstance();
  // Ensure ACF is still loaded.
  $acfLocalJsonLocator->checkForActivationErrors();
  // Add the ACF filters.
  $acfLocalJsonLocator->addFilters();
}, 10);

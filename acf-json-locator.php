<?php
/**
 * Plugin Name: ACF local JSON locator
 * Description: Sets the save and load points for Advanced Custom Fields local JSON files to be `wp-content/acf-json`.
 * Version: 1.0.0
 * Author: Jeffrey Barke
 * License: GPLv2 or later
 * @license https://www.gnu.org/licenses/gpl-2.0.html
 * @see https://www.advancedcustomfields.com/resources/local-json/
 */

namespace acfLocalJsonLocator;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

// @NOTE--The `WP_CONTENT_DIR` constant isn’t supposed to be used, but there
// doesn’t appear to be a good way to get the `wp-content` directory.
const ACF_LOCAL_PATH = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'acf-json';

/**
 * If there’s a problem, deactivate this plugin and notify the user.
 */
function deactivateAndNotify ($msg)
{
  add_action('admin_notices', function () use ($msg)
  {
    echo '<div class="notice notice-error"><p>' . __($msg) . '</p></div>';
  });

  deactivate_plugins(plugin_basename(__FILE__));
}

/**
 * Check to see if either ACF or ACF pro is installed. Return true if it is.
 *
 * @return boolean
 */
function isAcfInstalled ()
{
  $acf = array_filter(
    apply_filters('active_plugins', get_option('active_plugins')),
    function ($val) {
      return ($val === 'advanced-custom-fields/acf.php' ||
          $val === 'advanced-custom-fields-pro/acf.php');
    }
  );

  if (count($acf) > 0) {
    return true;
  }

  return false;
}

register_activation_hook(__FILE__, function ()
{
  // Check to see if ACF is installed. It if isn’t, we’re going to bail.
  if (!isAcfInstalled()) {
    set_transient('acf-json-locator-bail',
        'The “Advanced Custom Fields” plugin is not installed and the ' .
        '“ACF local JSON locator” plugin requires it.', 10);

    return;
  }

  // Check to see if the desired ACF local JSON directory exists and
  // if it’s writable.
  if (is_dir(ACF_LOCAL_PATH)) {
    if (!is_writable(ACF_LOCAL_PATH)) {
      set_transient('acf-json-locator-bail',
          'The <code>acf-json</code> directory in the WP content ' .
          'directory is not writable.', 10);

      return;
    }
  } else {
    if (!mkdir(ACF_LOCAL_PATH, 0755)) {
      set_transient('acf-json-locator-bail',
          'The <code>acf-json</code> directory in the WP content ' .
          'directory doesn’t exist and cannot be created.', 10);

      return;
    }
  }
});

/**
 * If ACF has been deactivated, set an admin notice and deactivate this plugin.
 */
add_action('plugins_loaded', function ()
{
  if (is_admin() && current_user_can('activate_plugins')) {
    // Check for any transients set during activation.
    $msg = get_transient('acf-json-locator-bail');

    if ($msg) {
      deactivateAndNotify($msg);

      // Clean up.
      delete_transient('acf-json-locator-bail');

      return;
    }

    // If ACF has been deactivated, set an admin notice and
    // deactivate this plugin.
    if (!isAcfInstalled()) {
      deactivateAndNotify('The “Advanced Custom Fields” plugin is not ' .
          'installed and the “ACF local JSON locator” plugin requires it.');
    }
  }
}, 10);

/**
 * Set the ACF local JSON save point to the `wp-content/acf-json` directory.
 * We don’t want this in the theme, because, imho, the data model should
 * be theme-independent.
 */
add_filter('acf/settings/save_json', function ($path)
{
    $path = WP_CONTENT_DIR . '/acf-json';

    return $path;
});

/**
 * Set the ACF local JSON load point to be the same as the save point.
 * ACF loads all JSON files from multiple load points.
 */
add_filter('acf/settings/load_json', function ($paths)
{
  // Remove original path.
  unset($paths[0]);

  // Append new path.
  $paths[] = WP_CONTENT_DIR . '/acf-json';

  return $paths;
});

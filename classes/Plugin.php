<?php
namespace acfLocalJsonLocator;

/**
 * Class Plugin
 * @package jbarke/acf-json-locator
 */
class Plugin extends Singleton
{
  /**
   * The default path to store the ACF local JSON files.
   *
   * @NOTE--The `WP_CONTENT_DIR` constant isn’t supposed to be used, but
   * there doesn’t appear to be a good way to get the `wp-content` directory.
   */
  private static $localJsonPath = WP_CONTENT_DIR . DIRECTORY_SEPARATOR .
      'acf-json';

  private static $localJsonPathOptionName = 'acf-local-json-locator-path';

  private $settingsSectionId = 'acf-local-json-locator-settings';

  // Private utility methods -------------------------------------------------

  /**
   * Add an error notification to the WordPress admin.
   *
   * @return void
   */
  private function adminNotify ($msg)
  {
    add_action('admin_notices', function () use ($msg) {
      echo '<div class="notice notice-error"><p>' . __($msg) . '</p></div>';
    });
  }

  /**
   * If there’s a problem, deactivate this plugin and notify the user.
   */
  private function deactivateAndNotify ($msg)
  {
    $this->adminNotify($msg);

    deactivate_plugins(ACF_LOCAL_JSON_LOCATOR_BASENAME);
  }

  /**
   * Get the ACF local JSON path, either from the saved WP options
   * or from the default.
   *
   * @return string
   */
  private static function getLocalJsonPath ()
  {
    $localJsonPath = get_option(self::$localJsonPathOptionName);

    if (!$localJsonPath) {
      $localJsonPath = self::$localJsonPath;
    }

    return $localJsonPath;
  }

  /**
   * Check to see if either ACF or ACF pro is installed.
   * Return true if it is.
   *
   * @return boolean
   */
  private static function isAcfInstalled ()
  {
    $acf = array_filter(
      apply_filters('active_plugins', get_option('active_plugins')),
      function ($val) {
        return (
          $val === 'advanced-custom-fields/acf.php' ||
          $val === 'advanced-custom-fields-pro/acf.php'
        );
      }
    );

    if (count($acf) > 0) {
      return true;
    }

    return false;
  }

  /**
   * Check to see if the desired ACF local JSON directory exists and
   * if it’s writable.
   *
   * @return boolean
   */
  private static function isDirectoryWritable ($dir, $setTransient = true)
  {
    if (is_dir($dir)) {
      if (!is_writable($dir)) {
        if ($setTransient) {
          set_transient('acf-json-locator-bail',
              'The <code>acf-json</code> directory in the WP content ' .
              'directory is not writable.', 10);
        }

        return false;
      }

    } else {
      if (!mkdir($dir, 0755)) {
        if ($setTransient) {
          set_transient('acf-json-locator-bail',
              'The <code>acf-json</code> directory in the WP content ' .
              'directory doesn’t exist and cannot be created.', 10);
        }

        return false;
      }
    }

    return true;
  }

  // Constructor -------------------------------------------------------------

  /**
   * This probably could have all just gone in the constructor, but it seemed
   * cleaner(?) to have all the hooks here(?).
   *
   * @return void
   */
  private function add_hooks ()
  {
    add_action('admin_init',
    [
      $this,
      'adminTemplateRedirect',
    ]);

    add_action('admin_init', [
      $this,
      'adminInit',
    ], 20);

    add_action('admin_menu', [
      $this,
      'adminMenu',
    ]);

    add_filter('plugin_action_links_' . ACF_LOCAL_JSON_LOCATOR_BASENAME, [
      $this,
      'pluginActionLinks',
    ]);
  }

  /**
   * Constructor
   *
   * Since this extends the Singleton base class, need to ensure this
   * cannot be called with `new`.
   */
  protected function __construct ()
  {
    $this->add_hooks();
  }

  // Plugin activation and plugins_loaded callbacks --------------------------

  /**
   * On plugin activation, run checks to see if ACF is installed and if the
   * desired save location exists and can be written to.
   *
   * @return void
   */
  public static function activate ()
  {
    // Check to see if ACF is installed. It if isn’t, we’re going to bail.
    if (!self::isAcfInstalled()) {
      set_transient('acf-json-locator-bail',
          'The “Advanced Custom Fields” plugin is not installed and the ' .
          '“ACF local JSON locator” plugin requires it.', 10);

      return;
    }

    // Check to see if the desired ACF local JSON directory exists and
    // if it’s writable. If not, we’re going to bail.
    self::isDirectoryWritable(self::getLocalJsonPath(), true);
  }

  /**
   * On `plugins_loaded`, check for any error messages and, if there are any,
   * set an admin notice and deactivate this plugin. If ACF has been
   * deactivated _after_ this plugin was activated, set an admin notice.
   *
   * @return void
   */
  public function checkForActivationErrors ()
  {
    if (is_admin() && current_user_can('activate_plugins')) {
      // Check for any transients set during activation.
      $msg = get_transient('acf-json-locator-bail');

      if ($msg) {
        $this->deactivateAndNotify($msg);

        // Clean up.
        delete_transient('acf-json-locator-bail');

        return;
      }

      // If ACF has been deactivated, set an admin notice.
      if (!$this->isAcfInstalled()) {
        $this->adminNotify('The “Advanced Custom Fields” plugin is not ' .
            'installed and the “ACF local JSON locator” plugin requires it.');
      }
    }
  }

  // Admin settings ----------------------------------------------------------

  /**
   * Set up the admin options page and fields.
   *
   * @return void
   */
  public function adminInit ()
  {
    $pageName = 'general';
    $sectionName = 'acf-local-json-locator-section';

    add_settings_section(
      $sectionName,
      'ACF local JSON locator',
      [
        $this,
        'renderSettingsSectionDescription',
      ],
      $pageName
    );

    add_settings_field(
      self::$localJsonPathOptionName,
      '<label for="' . self::$localJsonPathOptionName .
          '">' . __('ACF local JSON path') . '</label>',
      [
        $this,
        'renderSettingsField'
      ],
      $pageName,
      $sectionName
    );

    register_setting(
      $pageName,
      self::$localJsonPathOptionName,
      [
        'type' => 'string',
        'sanitize_callback' => [
          $this,
          'validateSettingsField',
        ],
        'default' => self::$localJsonPath
      ]
    );
  }

  /**
   * Add a link to the admin settings page to the primary admin nav
   * settings menu.
   *
   * @return void
   */
  public function adminMenu ()
  {
    add_options_page(
      __('ACF local JSON locator'),
      __('ACF local JSON'),
      'manage_options',
      $this->settingsSectionId,
      [
        $this,
        'renderSettingsPage'
      ]
    );
  }

  /**
   * If we try to access our fake options page, redirect to the actual
   * options section of the general settings page.
   *
   * @return void
   * @see adminMenu
   * @see renderSettingsPage
   */
  public function adminTemplateRedirect ()
  {
    if (
      !empty($_GET) &&
      isset($_GET['page']) &&
      $_GET['page'] ===  $this->settingsSectionId
    ) {
      wp_redirect(admin_url('options-general.php#' .
          $this->settingsSectionId));
      exit;
    }
  }

  /**
   * Add a link to the admin settings page underneath the plugin name on the
   * plugins page.
   */
  public function pluginActionLinks ($links)
  {
    $settingsLink = '<a href="' .
        admin_url('options-general.php#' . $this->settingsSectionId) .
        '">' . __( 'Settings') . '</a>';

    array_unshift($links,  $settingsLink);

    return $links;
  }

  /**
   * Output an options field.
   * Callback for `adminInit`.
   *
   * @return void
   */
  public function renderSettingsField ()
  {
    echo '<input type="text" id="' . self::$localJsonPathOptionName .
        '" name="' . self::$localJsonPathOptionName .
        '" class="large-text" value="' . $this->getLocalJsonPath() . '">';
    echo '<p id="' . self::$localJsonPathOptionName . '-description" ' .
        'class="description">Enter the absolute path where you’d like ' .
        'to save your ACF local JSON files. If in doubt, the default ' .
        'should suffice.</p>';
  }

  /**
   * Empty by design. There is no options page, there is an options section
   * on the general settings page. However, to add a menu item to the admin
   * nav, we have to pretend we have an options page and then template
   * redirect.
   *
   * @return void
   */
  public function renderSettingsPage ()
  {
  }

  /**
   * Output the content between the setting section heading and the
   * setting section fields.
   * Callback for `adminInit`.
   *
   * @return void
   */
  public function renderSettingsSectionDescription ()
  {
    echo '<div id="' . $this->settingsSectionId . '"></div>';
  }

  /**
   * Sanitize and validate the options field.
   * Callback for `adminInit`.
   *
   * @return boolean
   */
  public function validateSettingsField ($value)
  {
    // Trim and then test for an empty value.
    $value = trim($value);

    // We gotta have a value, so if they tried to clear it, simply
    // restore the default.
    if (empty($value)) {
      $value = self::$localJsonPath;
    }

    $value = sanitize_text_field($value);

    // Take whatever we now have and see if we can create it and write to it.
    if (!$this->isDirectoryWritable($value, false)) {
      // We can’t, so display an error and restore the previous value.
      add_settings_error(
        self::$localJsonPathOptionName,
        self::$localJsonPathOptionName . '-invalid',
        'The specified ACF local JSON path is not a writable directory.'
      );

      $value = $this->getLocalJsonPath();
    }

    return $value;
  }

  // Actual plugin functionality ---------------------------------------------

  /**
   * Update the location where ACF JSON files are saved and loaded.
   *
   * @return void
   */
  public function addFilters ()
  {
    /**
     * Set the ACF local JSON save point to the `wp-content/acf-json`
     * directory. We don’t want to store these in the theme directory
     * because, imho, the data model should be theme-independent.
     */
    add_filter('acf/settings/save_json', function ($path) {
      return $this->getLocalJsonPath();
    });

    /**
     * Set the ACF local JSON load point to be the same as the save point.
     * ACF loads all JSON files from multiple load points.
     */
    add_filter('acf/settings/load_json', function ($paths) {
      // Remove original path.
      unset($paths[0]);

      // Append new path.
      $paths[] = $this->getLocalJsonPath();

      return $paths;
    });
  }
}

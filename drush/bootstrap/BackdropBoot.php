<?php
/**
 * @file
 * Backdrop Boot class for Drush.
 */

namespace Drush\Boot;

class BackdropBoot extends DrupalBoot {

  /**
   * Bootstrap Drush with a valid Drupal Directory.
   *
   * In this function, the pwd will be moved to the root
   * of the Drupal installation.
   *
   * The DRUSH_DRUPAL_ROOT context, DRUSH_DRUPAL_CORE context, DRUPAL_ROOT, and the
   * DRUSH_DRUPAL_CORE constants are populated from the value that we determined during
   * the validation phase.
   *
   * We also now load the drushrc.php for this specific Drupal site.
   * We can now include files from the Drupal Tree, and figure
   * out more context about the platform, such as the version of Drupal.
   */
  function bootstrap_drupal_root() {
    // Load the config options from Drupal's /drush and sites/all/drush directories.
    drush_load_config('backdrop');

    $drupal_root = drush_set_context('DRUSH_DRUPAL_ROOT', drush_bootstrap_value('drupal_root'));
    chdir($drupal_root);

    // Needed for Drush.
    define('DRUPAL_ROOT', $drupal_root);
    // Drupal version equivilent.
    define('VERSION', '7.0-dev');
    // Needed for Backdrop.
    define('BACKDROP_ROOT', $drupal_root);

    // Needed to bootstrap.
    require_once $drupal_root . '/core/includes/bootstrap.inc';
    require_once $drupal_root . '/core/includes/config.inc';

    $core = $this->bootstrap_drupal_core($drupal_root);

    // DRUSH_DRUPAL_CORE should point to the /core folder in Drupal 8+ or to DRUPAL_ROOT
    // in prior versions.
    drush_set_context('DRUSH_DRUPAL_CORE', $core);
    define('DRUSH_DRUPAL_CORE', $core);

    _drush_preflight_global_options();

    drush_log(dt("Initialized Backdrop root directory at !drupal_root", array('!drupal_root' => $drupal_root)));
  }

  /**
   * Validate the DRUSH_BOOTSTRAP_DRUPAL_ROOT phase.
   *
   * In this function, we will check if a valid Backdrop directory is available.
   * We also determine the value that will be stored in the DRUSH_DRUPAL_ROOT
   * context and DRUPAL_ROOT constant if it is considered a valid option.
   */
  function bootstrap_drupal_root_validate() {
    $drupal_root = drush_get_context('DRUSH_SELECTED_DRUPAL_ROOT');

    if (empty($drupal_root)) {
      return drush_bootstrap_error('DRUSH_NO_DRUPAL_ROOT', dt("A Backdrop installation directory could not be found"));
    }
    if (!$signature = drush_valid_root($drupal_root)) {
      return drush_bootstrap_error('DRUSH_INVALID_DRUPAL_ROOT', dt("The directory !drupal_root does not contain a valid Backdrop installation", array('!drupal_root' => $drupal_root)));
    }

    drush_bootstrap_value('drupal_root', realpath($drupal_root));
    define('DRUSH_DRUPAL_SIGNATURE', $signature);

    return TRUE;
  }

  /**
   * VALIDATE the DRUSH_BOOTSTRAP_DRUPAL_SITE phase.
   *
   * In this function we determine the URL used for the command,
   * and check for a valid settings.php file.
   *
   * To do this, we need to set up the $_SERVER environment variable,
   * to allow us to use conf_path to determine what Drupal will load
   * as a configuration file.
   */
  function bootstrap_drupal_site_validate() {
    // Define the selected conf path as soon as we have identified that
    // we have selected a Drupal site.  Drush used to set this context
    // during the drush_bootstrap_drush phase.
    $drush_uri = _drush_bootstrap_selected_uri();
    drush_set_context('DRUSH_SELECTED_DRUPAL_SITE_CONF_PATH', drush_conf_path($drush_uri));

    $this->bootstrap_drupal_site_setup_server_global($drush_uri);
    return $this->bootstrap_drupal_site_validate_settings_present();
  }

  /**
   * Validate that the Drupal site has all of the settings that it
   * needs to operated.
   */
  function bootstrap_drupal_site_validate_settings_present() {
    $site = drush_bootstrap_value('site', $_SERVER['HTTP_HOST']);

    if (file_exists(BACKDROP_ROOT . '/settings.php')) {
      $conf_file = BACKDROP_ROOT . '/settings.php';
    }
    else {
      $conf_path = drush_bootstrap_value('conf_path', \conf_path(TRUE, TRUE));
      $conf_file = "$conf_path/settings.php";
    }

    if (!file_exists($conf_file)) {
      return drush_bootstrap_error('DRUPAL_SITE_SETTINGS_NOT_FOUND', dt("Could not find a Backdrop settings.php file at !file.",
         array('!file' => $conf_file)));
    }

    return TRUE;
  }

  /**
   * Called by bootstrap_drupal_site to do the main work
   * of the drush drupal site bootstrap.
   */
  function bootstrap_do_drupal_site() {
    $drush_uri = drush_get_context('DRUSH_SELECTED_URI');
    drush_set_context('DRUSH_URI', $drush_uri);
    $site = drush_set_context('DRUSH_DRUPAL_SITE', drush_bootstrap_value('site'));
    $conf_path = drush_set_context('DRUSH_DRUPAL_SITE_ROOT', drush_bootstrap_value('conf_path'));

    if ($conf_path == '') {
      drush_log(dt("Initialized Backdrop site !site using settings from site root.", array('!site' => $site)));
    }
    else {
      drush_log(dt("Initialized Backdrop site !site at !site_root", array('!site' => $site, '!site_root' => $conf_path)));
    }

    _drush_preflight_global_options();
  }

  function valid_root($path) {
    // Site root determined by index.php.
    if (!empty($path) && is_dir($path) && file_exists($path . '/index.php')) {
      // Drupal-like site determined by common.inc.
      $candidate = 'core/includes/common.inc';
      // Backdrop specifically determined by backdrop.js.
      if (file_exists($path . '/' . $candidate) && file_exists($path . '/core/misc/backdrop.js')) {
        return $candidate;
      }
    }
  }

  function get_profile() {
    return backdrop_get_profile();
  }

  function add_logger() {
    // If needed, prod module_implements() to recognize our system_watchdog() implementation.
    $dogs = drush_module_implements('watchdog');
    if (!in_array('system', $dogs)) {
      // Note that this resets module_implements cache.
      drush_module_implements('watchdog', FALSE, TRUE);
    }
  }

  function contrib_modules_paths() {
    return array(
      conf_path() . 'modules',
      '/modules',
    );
  }

  function contrib_themes_paths() {
    return array(
      conf_path() . 'themes',
      '/themes',
    );
  }

  function bootstrap_drupal_core($drupal_root) {
    $core = DRUPAL_ROOT . '/core';

    return $core;
  }

  function bootstrap_drupal_database_validate() {
    return parent::bootstrap_drupal_database_validate() && $this->bootstrap_drupal_database_has_table('cache_layout_path');
  }

  function bootstrap_drupal_database() {
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_DATABASE);
    parent::bootstrap_drupal_database();
  }

  function bootstrap_drupal_configuration() {
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_CONFIGURATION);

    // Unset drupal error handler and restore drush's one.
    restore_error_handler();

    parent::bootstrap_drupal_configuration();
  }

  function bootstrap_drupal_full() {
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_start();
    }
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_FULL);
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_end_clean();
    }

    parent::bootstrap_drupal_full();
  }
}

<?php
/**
 * @file
 * The magic Drush loader for backdrop.
 */

/**
 * Implements hook_bootstrap_candidates().
 * Lists Boot loaders for Drush to try.
 */
function backdrop_bootstrap_candidates() {
  return 'Drush\Boot\BackdropBoot';
}

include __DIR__ . '/BackdropBoot.php';

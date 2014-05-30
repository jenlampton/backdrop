<?php

/**
 * Tests a theme overriding a suggestion of a base theme hook.
 */
function test_theme_theme_test__suggestion($variables) {
  return 'Theme hook implementor=test_theme_theme_test__suggestion(). Foo=' . $variables['foo'];
}

/**
 * Tests a theme implementing an alter hook.
 *
 * The confusing function name here is due to this being an implementation of
 * the alter hook invoked when the 'theme_test' module calls
 * backdrop_alter('theme_test_alter').
 */
function test_theme_theme_test_alter_alter(&$data) {
  $data = 'test_theme_theme_test_alter_alter was invoked';
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function test_theme_theme_suggestions_theme_test_suggestions_alter(array &$suggestions, array $variables) {
  // Theme alter hooks run after module alter hooks, so add this theme
  // suggestion to the beginning of the array so that the suggestion added by
  // the theme_suggestions_test module can be picked up when that module is
  // enabled.
  array_unshift($suggestions, 'theme_test_suggestions__' . 'theme_override');
}

/**
 * Implements hook_theme_suggestions_HOOK_alter().
 */
function test_theme_theme_suggestions_theme_test_function_suggestions_alter(array &$suggestions, array $variables) {
  // Theme alter hooks run after module alter hooks, so add this theme
  // suggestion to the beginning of the array so that the suggestion added by
  // the theme_suggestions_test module can be picked up when that module is
  // enabled.
  array_unshift($suggestions, 'theme_test_function_suggestions__' . 'theme_override');
}

/**
 * Returns HTML for a theme function suggestion test.
 *
 * Implements the theme_test_function_suggestions__theme_override suggestion.
 */
function test_theme_theme_test_function_suggestions__theme_override($variables) {
  return 'Theme function overridden based on new theme suggestion provided by the test_theme theme.';
}

/**
 * Returns HTML for a theme function suggestion test.
 *
 * Implements the theme_test_function_suggestions__module_override suggestion.
 */
function test_theme_theme_test_function_suggestions__module_override($variables) {
  return 'Theme function overridden based on new theme suggestion provided by a module.';
}

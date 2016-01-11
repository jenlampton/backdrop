<?php
/**
 * @file
 * Contains a theme's functions to manipulate or override the default markup.
 */

/**
 * Prepares variables for maintenance page templates.
 *
 * @see maintenance_page.tpl.php
 */
function bartik_preprocess_maintenance_page(&$variables) {
  backdrop_add_css(backdrop_get_path('theme', 'bartik') . '/css/maintenance-page.css');
}

/**
 * Prepares variables for page templates.
 *
 * @see page.tpl.php
 */
function bartik_preprocess_page(&$variables) {
  $variables['classes'][] = theme_get_setting('main_menu_tabs', 'bartik');
  if (config_get('bartik.settings', 'legacy')) {
    $path = backdrop_get_path('theme', 'bartik') . '/css/colors-legacy.css';
    backdrop_add_css($path, array('group' => CSS_THEME, 'weight' => 100));
  }
}

/**
 * Prepares variables for layout template files.
 *
 * @see layout.tpl.php
 */
function bartik_preprocess_layout(&$variables) {
  if ($variables['content']['header']) {
    $variables['content']['header'] = '<div class="l-header-inner">' . $variables['content']['header'] . '</div>';
  }
}

/**
 * Overrides theme_menu_tree().
 */
function bartik_menu_tree($variables) {
  return '<ul class="menu clearfix">' . $variables['tree'] . '</ul>';
}

/**
 * Overrides theme_field__FIELD_TYPE().
 */
function bartik_field__taxonomy_term_reference($variables) {
  $output = '';

  // Render the label, if it's not hidden.
  if (!$variables['label_hidden']) {
    $output .= '<h3 class="field-label">' . $variables['label'] . ': </h3>';
  }

  // Render the items.
  $output .= ($variables['element']['#label_display'] == 'inline') ? '<ul class="links inline">' : '<ul class="links">';
  foreach ($variables['items'] as $delta => $item) {
    $item_attributes = (isset($variables['item_attributes'][$delta])) ? backdrop_attributes($variables['item_attributes'][$delta]) : '';
    $output .= '<li class="taxonomy-term-reference-' . $delta . '"' . $item_attributes . '>' . backdrop_render($item) . '</li>';
  }
  $output .= '</ul>';

  // Render the surrounding DIV with appropriate classes and attributes.
  if (!in_array('clearfix', $variables['classes'])) {
    $variables['classes'][] = 'clearfix';
  }
  $output = '<div class="' . implode(' ', $variables['classes']) . '"' . backdrop_attributes($variables['attributes']) . '>' . $output . '</div>';

  return $output;
}

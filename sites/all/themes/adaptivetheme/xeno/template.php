<?php // $Id: template.php,v 1.1.2.6 2009/12/24 01:47:01 jmburnz Exp $
// xenos.com

/**
 * @file template.php
 */

// Don't include custom functions if the database is inactive.
if (db_is_active()) {
  // Include base theme custom functions.
  include_once(drupal_get_path('theme', 'adaptivetheme') .'/inc/template.custom-functions.inc');
}

/**
 * Add the color scheme stylesheet if color_enable_schemes is set to 'on'.
 * Note: you must have at minimum a color-default.css stylesheet in /css/theme/
 */
if (theme_get_setting('color_enable_schemes') == 'on') {
  drupal_add_css(drupal_get_path('theme', 'xeno_subtheme') .'/css/theme/'. get_at_colors(), 'theme');
}

// Ingreso de librerias de js en el juego , solo las necesarias.
if (drupal_is_front_page()) {
  drupal_add_js('game/lib/jquery/jquery.hints.js','theme');
  drupal_add_js('game/lib/js/json_parse_state.js','theme');
  drupal_add_js('game/lib/js/index.js','theme');
}

//Evitar que aparezca en la galeria el nombre y autor de esto
function xeno_node_submitted($node) {
  return t('!datetime — !username',
    array(
      '!username' => theme('username', $node),
      '!datetime' => format_date($node->created),
    ));
}
/**
 * USAGE
 * 1. Rename each function to match your subthemes name,
 *    e.g. if you name your theme "themeName" then the function
 *    name will be "themeName_preprocess_hook".
 * 2. Uncomment the required function to use. You can delete the
 *    "sample_variable".
 */

/**
 * Override or insert variables into all templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered.
 */
/*
function xeno_subtheme_preprocess(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
*/

/**
 * Override or insert variables into the page templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered.
 */
/*
function xeno_subtheme_preprocess_page(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
*/

/**
 * Override or insert variables into the node templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered.
 */
/*
function xeno_subtheme_preprocess_node(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
*/

/**
 * Override or insert variables into the comment templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered.
 */
/*
function xeno_subtheme_preprocess_comment(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
*/

/**
 * Override or insert variables into the block templates.
 *
 * @param $vars
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered.
 */
/*
function xeno_subtheme_preprocess_block(&$vars, $hook) {
  $vars['sample_variable'] = t('Lorem ipsum.');
}
*/

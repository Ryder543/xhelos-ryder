<?php
// $Id: views_showcase.views.inc,v 1.6 2010/04/29 16:40:36 khaledzaidan Exp $

/**
 * @file
 *  Provide the views showcase plugin definition.
 */

function views_showcase_views_plugins() {
  return array(
    'module' => 'views_showcase',
    'style' => array(
      'views_showcase' => array(
        'title' => t('Views Showcase'),
        'theme' => 'views_showcase_view',
        'help' => t('Display a view like a showcase.'),
        'handler' => 'views_showcase_style_plugin',
        'uses row plugin' => FALSE,
        'uses fields' => TRUE,
        'uses options' => TRUE,
        'type' => 'normal',
      ),
    ),
  );
}

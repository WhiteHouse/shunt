<?php

/**
 * @file
 * Drush commands for the Shunt module.
 */

/**
 * Implements hook_drush_command().
 */
function shunt_drush_command() {
  $items = array();

  $items['shunt-enable'] = array(
    'description' => 'Enable one or more shunts.',
    'arguments' => array(
      'name' => '(optional) The machine name of a shunt to enable, or "all" for all shunts. Omit to interactively choose from a list of available shunts.',
    ),
    'examples' => array(
      'drush se all' => 'Enable all shunts.',
      'drush se example' => 'Enable the "example" shunt.',
      'drush se' => 'Choose from a list of shunts to enable at the prompt.',
    ),
    'aliases' => array('se'),
  );

  $items['shunt-disable'] = array(
    'description' => 'Disable one or more shunts.',
    'arguments' => array(
      'name' => '(optional) The machine name of a shunt to disable, or "all" for all shunts. Omit to interactively choose from a list of available shunts.',
    ),
    'examples' => array(
      'drush sd all' => 'Disable all shunts.',
      'drush sd example' => 'Disable the "example" shunt.',
      'drush sd' => 'Choose from a list of shunts to disable at the prompt.',
    ),
    'aliases' => array('sd'),
  );

  $items['shunt-get-enabled'] = array(
    'description' => "Get a list of enabled (\"tripped\") shunts.",
    'examples' => array(
      'drush shunt-get-enabled' => 'Get a list of enabled shunt trips.',
      'drush sge' => '',
    ),
    'aliases' => array('sge'),
  );

  $items['shunt-get-disabled'] = array(
    'description' => "Get a list of disabled shunt trips.",
    'examples' => array(
      'drush shunt-get-disabled' => 'Get a list of disabled shunt trips.',
      'drush sgd' => '',
    ),
    'aliases' => array('sgd'),
  );

  $items['shunt-is-enabled'] = array(
    'description' => "Determine if shunt is tripped/enabled.",
    'argument' => array(
      'name' => '(optional) The machine name of the shunt to check. Defaults to "shunt".',
    ),
    'examples' => array(
      'drush shunt-is-enabled' => 'Determine if sandwich shunt is enabled',
      'drush shunt-is-enabled sandwich' => 'Determine if sandwich shunt is enabled',
      'drush sie sandwich' => '',
    ),
    'aliases' => array('sie', 'sit', 'shunt-is-tripped'),
  );

  return $items;
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_disable($shunt = NULL) {
  // Prompt the user to choose a shunt if none is specified.
  $choice = ($shunt) ? $shunt : _shunt_prompt_for_shunt_choice();

  // Return if cancelled.
  if ($choice === FALSE) {
    return;
  }

  // Disable the selection.
  if ($choice == 'all') {
    shunt_disable_shunt();
  }
  else {
    shunt_disable_shunt($choice);
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_enable($shunt = NULL) {
  // Prompt the user to choose a shunt if none is specified.
  $choice = ($shunt) ? $shunt : _shunt_prompt_for_shunt_choice();

  // Return if cancelled.
  if ($choice === FALSE) {
    return;
  }

  // Disable the selection.
  if ($choice == 'all') {
    shunt_enable_shunt();
  }
  else {
    shunt_enable_shunt($choice);
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_get_disabled() {
  $shunts = shunt_get_disabled();
  if (count($shunts) > 0) {
    $text = t('These shunts are currently disabled:');
    foreach ($shunts as $name) {
      $text .= "\n$name";
    }
  }
  else {
    $text = t('No shunts are currently disabled. (All shunts are tripped.)');
  }

  drush_print($text);
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_get_enabled() {
  $shunts = shunt_get_enabled();
  if (count($shunts) > 0) {
    $text = t('These shunts are currently "tripped" or enabled:');
    foreach ($shunts as $name) {
      $text .= "\n$name";
    }
  }
  else {
    $text = t('No shunts are currently "tripped" or enabled.');
  }

  drush_print($text);
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_is_enabled($shunt = '') {
  $args = array('@name' => $shunt);
  if (shunt_is_enabled($shunt)) {
    drush_print(dt('Shunt "@name" IS enabled.', $args));
  }
  else {
    drush_print(dt('Shunt "@name" is NOT enabled.', $args));
  }
}

/**
 * Prompts the user to choose from a list of available shunts.
 *
 * @return string|false
 *   The value of the option (i.e., the shunt machine name) chosen, or FALSE if
 *   cancelled.
 */
function _shunt_prompt_for_shunt_choice() {
  // Populate the option list with available shunts.
  $shunt_names = array_keys(shunt_get_shunt_definitions());
  $options = drupal_map_assoc($shunt_names);

  // Prepend an "all" option.
  array_unshift($options, array('all' => 'All'));

  $prompt = t('Choose a shunt');

  $choice = drush_choice($options, $prompt);

  return $choice;
}

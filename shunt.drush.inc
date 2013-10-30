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

  $items['shunt-list'] = array(
    'description' => 'Show a list of available shunts with their statuses.',
    'options' => array(
      'status' => array(
        'description' => 'Filter by shunt status. Choices: "enabled", "disabled".',
        'example-value' => 'enabled',
      ),
    ),
    'examples' => array(
      'drush shunt-list' => 'Show all shunts.',
      'drush shunt-list --status=enabled' => 'Show only enabled shunts.',
    ),
    'outputformat' => array(
      'default' => 'table',
      'pipe-format' => 'list',
      'field-labels' => array(
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
      ),
      'output-data-type' => 'format-table',
    ),
    'aliases' => array('shl'),
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
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_list() {
  // Prepare and validate "status" filter.
  $all_statuses = array('enabled', 'disabled');
  $status_filter = strtolower(drush_get_option('status'));
  if (!empty($status_filter)) {
    $status_filter = explode(',', $status_filter);
  }
  else {
    $status_filter = $all_statuses;
  }
  if (count($status_filter) == 1) {
    drush_hide_output_fields('status');
  }
  foreach ($status_filter as $status) {
    if (!in_array($status, $all_statuses)) {
      return drush_set_error('SHUNT_INVALID_STATUS', dt('"!status" is not a valid shunt status.', array(
        '!status' => $status,
      )));
    }
  }

  // Build result table.
  $result = array();
  $shunts = shunt_get_shunt_definitions();
  foreach ($shunts as $name => $description) {
    // Filter by status.
    $status = (shunt_is_enabled($name)) ? 'enabled' : 'disabled';
    if (!in_array($status, $status_filter)) {
      continue;
    }

    // Build row.
    $result[$name] = array(
      'name' => $name,
      'description' => $description,
      'status' => ucfirst($status),
    );
  }

  return $result;
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

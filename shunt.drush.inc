<?php

/**
 * @file
 * Drush commands for the Shunt module.
 */

use Drupal\shunt\Shunt;

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
      'drush shunt-enable all' => 'Enable all shunts.',
      'drush shunt-enable example' => 'Enable the "example" shunt.',
      'drush shunt-enable' => 'Choose from a list of shunts to enable at the prompt.',
    ),
    'aliases' => array('she'),
  );

  $items['shunt-disable'] = array(
    'description' => 'Disable one or more shunts.',
    'arguments' => array(
      'name' => '(optional) The machine name of a shunt to disable, or "all" for all shunts. Omit to interactively choose from a list of available shunts.',
    ),
    'examples' => array(
      'drush shunt-disable all' => 'Disable all shunts.',
      'drush shunt-disable example' => 'Disable the "example" shunt.',
      'drush shunt-disable' => 'Choose from a list of shunts to disable at the prompt.',
    ),
    'aliases' => array('shd'),
  );

  $items['shunt-info'] = array(
    'description' => 'Show detailed info for one or more shunts.',
    'arguments' => array(
      'names' => 'A list of shunt machine names. You can use the wildcard (*) at the end of shunt names to show info for multiple matches. If no argument is provided it will show info for all available shunts.',
    ),
    'examples' => array(
      'drush shunt-info example' => 'Show info for the "example" shunt.',
      'drush shunt-info example\*' => 'Show info for all shunts whose names begin with "example".',
    ),
    'aliases' => array('shi'),
    'outputformat' => array(
      'default' => 'key-value-list',
      'pipe-format' => 'json',
      'field-labels' => array(
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
      ),
      'output-data-type' => 'format-table',
    ),
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
  _shunt_enable_or_disable($shunt, 'disable');
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_enable($shunt = NULL) {
  _shunt_enable_or_disable($shunt, 'enable');
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_info() {
  $shunt_info = Shunt::getDefinitions();

  $args = _convert_csv_to_array(func_get_args());
  _shunt_expand_shunt_names($args);

  // If no shunts are specified, show all.
  if (count($args) == 0) {
    $args = array_keys($shunt_info);
  }

  $result = array();

  foreach ($args as $shunt) {
    if (!isset($shunt_info[$shunt])) {
      drush_log(dt('No such shunt "@name".', array(
        '@name' => $shunt,
      )), 'warning');
      continue;
    }

    $result[$shunt] = array(
      'name' => $shunt,
      'description' => $shunt_info[$shunt],
      'status' => Shunt::isEnabled($shunt) ? 'enabled' : 'disabled',
    );
  }

  return $result;
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
  $shunts = Shunt::getDefinitions();
  foreach ($shunts as $name => $description) {
    // Filter by status.
    $status = (Shunt::isEnabled($name)) ? 'enabled' : 'disabled';
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
 * Handles shunt selection and status operation for enable/disable commands.
 *
 * @param string $selection
 *   The user-supplied command argument. May be the machine name of a shunt,
 *   "all", or NULL.
 * @param string $operation
 *   The status value. Accepted values are "enable" and "disable".
 *
 * @see drush_shunt_disable()
 * @see drush_shunt_enable()
 */
function _shunt_enable_or_disable($selection, $operation) {
  // Make sure specified operation is valid.
  $valid_operations = array('enable', 'disable');
  if (!in_array($operation, $valid_operations)) {
    return;
  }

  // Prompt the user to choose a shunt if none is specified.
  $choice = ($selection) ? $selection : _shunt_prompt_for_shunt_choice();

  // Return if cancelled.
  if ($choice === FALSE) {
    return;
  }

  // Prepare the shunt list.
  if ($choice == 'all') {
    $shunt_list = array_keys(Shunt::getDefinitions());
  }
  else {
    $shunt_list = array($choice);
  }

  // Perform the operation on the selection.
  $method = "{$operation}Multiple";
  Shunt::$method($shunt_list);
}

/**
 * Performs shunt name expansion for trailing wildcards (*).
 *
 * Given a list of shunt names, this function identifies names with a trailing
 * wildcard (*) and replaces them with actual shunt names that match the
 * expansion pattern.
 *
 * @param array $shunts
 *   An indexed array of shunt names, by reference.
 */
function _shunt_expand_shunt_names(array &$shunts) {
  $shunt_info = Shunt::getDefinitions();
  foreach ($shunts as $key => $shunt) {
    $wildcard = rtrim($shunt, '*');
    if ($wildcard !== $shunt) {
      foreach (array_keys($shunt_info) as $shunt_name) {
        if (substr($shunt_name, 0, strlen($wildcard)) == $wildcard) {
          $shunts[] = $shunt_name;
        }
      }
      unset($shunts[$key]);
      continue;
    }
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
  $shunt_names = array_keys(Shunt::getDefinitions());
  $options = drupal_map_assoc($shunt_names);

  // Prepend an "all" option.
  array_unshift($options, array('all' => 'All'));

  $prompt = t('Choose a shunt');

  $choice = drush_choice($options, $prompt);

  return $choice;
}

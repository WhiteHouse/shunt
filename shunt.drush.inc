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
      'shunts' => 'A list of shunt machine names, or "all" for all shunts. You can use the wildcard (*) at the end of shunt names to show info for multiple matches.',
    ),
    'required-arguments' => TRUE,
    'examples' => array(
      'drush shunt-enable all' => 'Enable all shunts.',
      'drush shunt-enable example' => 'Enable the "example" shunt.',
      'drush shunt-enable example\*' => 'Enable all shunts whose names begin with "example".',
    ),
    'aliases' => array('she'),
  );

  $items['shunt-disable'] = array(
    'description' => 'Disable one or more shunts.',
    'arguments' => array(
      'shunts' => 'A list of shunt machine names, or "all" for all shunts. You can use the wildcard (*) at the end of shunt names to show info for multiple matches.',
    ),
    'required-arguments' => TRUE,
    'examples' => array(
      'drush shunt-disable all' => 'Disable all shunts.',
      'drush shunt-disable example' => 'Disable the "example" shunt.',
      'drush shunt-disable example\*' => 'Disable all shunts whose names begin with "example".',
    ),
    'aliases' => array('shd'),
  );

  $items['shunt-info'] = array(
    'description' => 'Show detailed info for one or more shunts.',
    'arguments' => array(
      'shunts' => 'A list of shunt machine names. You can use the wildcard (*) at the end of shunt names to show info for multiple matches. If no argument is provided it will show info for all available shunts.',
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
function drush_shunt_disable() {
  $args = _convert_csv_to_array(func_get_args());
  _shunt_drush_enable_or_disable($args, 'disable');
}

/**
 * Command argument complete callback.
 *
 * @see shunt_drush_command()
 */
function shunt_shunt_disable_complete() {
  return _shunt_drush_complete_shunts();
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_enable() {
  $args = _convert_csv_to_array(func_get_args());
  _shunt_drush_enable_or_disable($args, 'enable');
}

/**
 * Command argument complete callback.
 *
 * @see shunt_drush_command()
 */
function shunt_shunt_enable_complete() {
  return _shunt_drush_complete_shunts();
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_info() {
  $shunt_info = Shunt::getDefinitions();

  $args = _convert_csv_to_array(func_get_args());
  _shunt_drush_expand_shunt_names($args);

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
 * Command argument complete callback.
 *
 * @see shunt_drush_command()
 */
function shunt_shunt_info_complete() {
  return _shunt_drush_complete_shunts();
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
 * Gets a list of shunt names for command completion.
 *
 * @return array
 *   An indexed array of available shunt names.
 */
function _shunt_drush_complete_shunts() {
  if (drush_bootstrap_max(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
    $shunt_info = Shunt::getDefinitions();
    $shunt_names = array_keys($shunt_info);
    return array('values' => $shunt_names);
  }
}

/**
 * Handles shunt selection and status changing for enable/disable commands.
 *
 * @param array $args
 *   The user-supplied command arguments.
 * @param string $operation
 *   The status value. Accepted values are "enable" and "disable".
 *
 * @see drush_shunt_disable()
 * @see drush_shunt_enable()
 */
function _shunt_drush_enable_or_disable(array $args, $operation) {
  // Make sure specified operation is valid.
  $valid_operations = array('enable', 'disable');
  if (!in_array($operation, $valid_operations)) {
    return;
  }

  $args = _convert_csv_to_array($args);
  _shunt_drush_expand_shunt_names($args);

  // Prepare the shunt list.
  if (in_array('all', $args)) {
    $shunt_list = array_keys(Shunt::getDefinitions());
  }
  else {
    $shunt_list = $args;
  }

  // Prompt the user for confirmation before proceeding.
  $message_args = array('!shunts' => implode(', ', $shunt_list));
  if ($operation == 'enable') {
    $message = dt('The following shunts will be enabled: !shunts.', $message_args);
  }
  else {
    $message = dt('The following shunts will be disabled: !shunts.', $message_args);
  }
  drush_print($message);
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
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
function _shunt_drush_expand_shunt_names(array &$shunts) {
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

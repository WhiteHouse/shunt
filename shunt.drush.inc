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
      'shunts' => 'A list of shunt machine names. You can use a wildcard (*) at the end of shunt names to show info for multiple matches.',
    ),
    'options' => array(
      'all' => array(
        'description' => dt('Enable all shunts. Supersedes the "shunts" argument.'),
      ),
    ),
    'examples' => array(
      'drush shunt-enable example' => 'Enable the "example" shunt.',
      'drush shunt-enable example\*' => 'Enable all shunts whose names begin with "example".',
      'drush shunt-enable --all' => 'Enable all shunts.',
    ),
    'aliases' => array('she'),
  );

  $items['shunt-disable'] = array(
    'description' => 'Disable one or more shunts.',
    'arguments' => array(
      'shunts' => 'A list of shunt machine names. You can use a wildcard (*) at the end of shunt names to show info for multiple matches.',
    ),
    'options' => array(
      'all' => array(
        'description' => dt('Disable all shunts. Supersedes the "shunts" argument.'),
      ),
    ),
    'examples' => array(
      'drush shunt-disable example' => 'Disable the "example" shunt.',
      'drush shunt-disable example\*' => 'Disable all shunts whose names begin with "example".',
      'drush shunt-disable --all' => 'Disable all shunts.',
    ),
    'aliases' => array('shd'),
  );

  $items['shunt-info'] = array(
    'description' => 'Show detailed info for one or more shunts.',
    'arguments' => array(
      'shunts' => 'A list of shunt machine names. You can use a wildcard (*) at the end of shunt names to show info for multiple matches. If no argument is provided it will show info for all available shunts.',
    ),
    'examples' => array(
      'drush shunt-info example' => 'Show info for the "example" shunt.',
      'drush shunt-info example\*' => 'Show info for all shunts whose names begin with "example".',
      'drush shunt-info' => 'Show info for all shunts.',
    ),
    'aliases' => array('shi'),
    'outputformat' => array(
      'default' => 'key-value-list',
      'pipe-format' => 'json',
      'field-labels' => array(
        'name' => 'Name',
        'provider' => 'Provider',
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
      'provider' => array(
        'description' => 'Filter by provider, i.e., the module that defines the shunt.',
        'example-value' => 'shunt',
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
        'provider' => 'Provider',
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
  $shunt_manager = \Drupal::service('plugin.manager.shunt');
  $shunt_list = _shunt_drush_parse_shunt_list(func_get_args());

  // Discard shunts that are already disabled.
  foreach ($shunt_list as $key => $name) {
    if (!$shunt_manager->shuntIsEnabled($name)) {
      drush_log(dt('Shunt "!name" is already disabled.', array(
        '!name' => $name,
      )), 'ok');
      unset($shunt_list[$key]);
    }
  }

  // Inform the user which shunts will finally be disabled.
  if (empty($shunt_list)) {
    return drush_log(dt('There were no shunts that could be disabled.'), 'ok');
  }
  drush_print(dt('The following shunts will be disabled: !names', array(
    '!names' => implode(', ', $shunt_list),
  )));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  $shunt_manager->disableShuntMultiple($shunt_list);
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
  $shunt_manager = \Drupal::service('plugin.manager.shunt');
  $shunt_list = _shunt_drush_parse_shunt_list(func_get_args());

  // Discard shunts that are already enabled.
  foreach ($shunt_list as $key => $name) {
    if ($shunt_manager->shuntIsEnabled($name)) {
      drush_log(dt('Shunt "!name" is already enabled.', array(
        '!name' => $name,
      )), 'ok');
      unset($shunt_list[$key]);
    }
  }

  // Inform the user which shunts will finally be enabled.
  if (empty($shunt_list)) {
    return drush_log(dt('There were no shunts that could be enabled.'), 'ok');
  }
  drush_print(dt('The following shunts will be enabled: !names', array(
    '!names' => implode(', ', $shunt_list),
  )));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  $shunt_manager->enableShuntMultiple($shunt_list);
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
  $result = array();

  $shunt_manager = \Drupal::service('plugin.manager.shunt');
  $definitions = $shunt_manager->getDefinitions();

  $args = func_get_args();
  // If a list of shunts is supplied, parse it.
  if (!empty($args)) {
    $shunt_list = _shunt_drush_parse_shunt_list(func_get_args());
  }
  // Otherwise supply the list of all available shunts.
  else {
    $shunt_list = array_keys($definitions);
  }

  foreach ($shunt_list as $shunt) {
    $result[$shunt] = array(
      'name' => $shunt,
      'provider' => $definitions[$shunt]['provider'],
      'description' => $definitions[$shunt]['description'],
      'status' => $shunt_manager->shuntIsEnabled($shunt) ? 'Enabled' : 'Disabled',
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

  // Prepare and validate "provider" filter.
  $provider_filter = strtolower(drush_get_option('provider'));
  if (!empty($provider_filter)) {
    $provider_filter = explode(',', $provider_filter);
  }
  else {
    $provider_filter = array();
  }
  if (count($provider_filter) == 1) {
    drush_hide_output_fields('provider');
  }

  // Build result table.
  $result = array();
  $shunt_manager = \Drupal::service('plugin.manager.shunt');
  $definitions = $shunt_manager->getDefinitions();
  foreach ($definitions as $id => $definition) {
    // Filter by status.
    $status = ($shunt_manager->shuntIsEnabled($id)) ? 'enabled' : 'disabled';
    if (!in_array($status, $status_filter)) {
      continue;
    }

    // Filter by provider.
    if (!empty($provider_filter) && !in_array($definition['provider'], $provider_filter)) {
      continue;
    }

    // Build row.
    $result[$id] = array(
      'name' => $id,
      'provider' => $definition['provider'],
      'description' => $definition['description'],
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
    $definitions = \Drupal::service('plugin.manager.shunt')->getDefinitions();
    $shunt_names = array_keys($definitions);
    return array('values' => $shunt_names);
  }
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
  $definitions = \Drupal::service('plugin.manager.shunt')->getDefinitions();
  foreach ($shunts as $key => $shunt) {
    $wildcard = rtrim($shunt, '*');
    if ($wildcard !== $shunt) {
      foreach (array_keys($definitions) as $shunt_name) {
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
 * Parses a list of shunt names passed in as a command argument.
 *
 * @param array $raw_args
 *   The raw arguments array passed into drush_hook_COMMAND(), as accessed there
 *   via func_get_args().
 *
 * @return array
 *   An indexed array of shunt machine names.
 */
function _shunt_drush_parse_shunt_list(array $raw_args) {
  $shunt_manager = \Drupal::service('plugin.manager.shunt');

  // Convert comma separated values.
  $args_array = _convert_csv_to_array($raw_args);

  // The "all" option supersedes the "shunts" argument. Return early.
  if (drush_get_option('all', FALSE)) {
    $definitions = $shunt_manager->getDefinitions();
    return array_keys($definitions);
  }

  // Expand wildcards.
  _shunt_drush_expand_shunt_names($args_array);

  // Remove duplicates.
  $unique_args = array_unique($args_array);

  // Discard non-existent shunts.
  $shunt_list = array();
  foreach ($unique_args as $name) {
    if ($shunt_manager->shuntExists($name)) {
      $shunt_list[] = $name;
    }
    else {
      drush_log(dt('No such shunt "!name".', array(
        '!name' => $name,
      )), 'warning');
    }
  }

  return $shunt_list;
}

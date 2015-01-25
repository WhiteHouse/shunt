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
      'ids' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches.',
    ),
    'options' => array(
      'all' => array(
        'description' => dt('Enable all shunts. Supersedes the "shunts" argument.'),
      ),
    ),
    'examples' => array(
      'drush shunt-enable example' => 'Enable the "example" shunt.',
      'drush shunt-enable example\*' => 'Enable all shunts whose IDs begin with "example".',
      'drush shunt-enable --all' => 'Enable all shunts.',
    ),
    'aliases' => array('she'),
  );

  $items['shunt-disable'] = array(
    'description' => 'Disable one or more shunts.',
    'arguments' => array(
      'ids' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches.',
    ),
    'options' => array(
      'all' => array(
        'description' => dt('Disable all shunts. Supersedes the "shunts" argument.'),
      ),
    ),
    'examples' => array(
      'drush shunt-disable example' => 'Disable the "example" shunt.',
      'drush shunt-disable example\*' => 'Disable all shunts whose IDs begin with "example".',
      'drush shunt-disable --all' => 'Disable all shunts.',
    ),
    'aliases' => array('shd'),
  );

  $items['shunt-info'] = array(
    'description' => 'Show detailed info for one or more shunts.',
    'arguments' => array(
      'shunts' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches. If no argument is provided it will show info for all available shunts.',
    ),
    'examples' => array(
      'drush shunt-info example' => 'Show info for the "example" shunt.',
      'drush shunt-info example\*' => 'Show info for all shunts whose IDs begin with "example".',
      'drush shunt-info' => 'Show info for all shunts.',
    ),
    'aliases' => array('shi'),
    'outputformat' => array(
      'default' => 'key-value-list',
      'pipe-format' => 'json',
      'field-labels' => array(
        'id' => 'Shunt',
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
        'id' => 'Shunt',
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
  $ids = _shunt_drush_parse_shunt_list(func_get_args());
  /** @var \Drupal\shunt\Entity\Shunt[] $shunts */
  $shunts = entity_load_multiple('shunt', $ids);

  // Discard shunts that are already disabled.
  foreach ($shunts as $id => $shunt) {
    if (!$shunt->isShuntEnabled()) {
      drush_log(dt('Shunt @id is already disabled.', array(
        '@id' => $id,
      )), 'ok');
      unset($shunts[$id]);
    }
  }

  // Inform the user which shunts will finally be disabled.
  if (empty($shunts)) {
    return drush_log(dt('There were no shunts that could be disabled.'), 'ok');
  }
  drush_print(dt('The following shunts will be disabled: @ids', array(
    '@ids' => implode(', ', array_keys($shunts)),
  )));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  // Disable shunts.
  foreach ($shunts as $shunt) {
    $shunt->disableShunt();
  }
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
  $ids = _shunt_drush_parse_shunt_list(func_get_args());
  /** @var \Drupal\shunt\Entity\Shunt[] $shunts */
  $shunts = entity_load_multiple('shunt', $ids);

  // Discard shunts that are already enabled.
  foreach ($shunts as $id => $shunt) {
    if ($shunt->isShuntEnabled()) {
      drush_log(dt('Shunt @id is already enabled.', array(
        '@id' => $id,
      )), 'ok');
      unset($shunts[$id]);
    }
  }

  // Inform the user which shunts will finally be disabled.
  if (empty($shunts)) {
    return drush_log(dt('There were no shunts that could be enabled.'), 'ok');
  }
  drush_print(dt('The following shunts will be enabled: @ids', array(
    '@ids' => implode(', ', array_keys($shunts)),
  )));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  // Enable shunts.
  foreach ($shunts as $shunt) {
    $shunt->enableShunt();
  }
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
  $info = _shunt_drush_get_info();

  $args = func_get_args();
  // If a list of shunts is supplied, parse it.
  if (!empty($args)) {
    $shunt_list = _shunt_drush_parse_shunt_list(func_get_args());
  }
  // Otherwise supply the list of all available shunts.
  else {
    $shunt_list = _shunt_drush_get_all_shunt_ids();
  }

  $result = array();
  foreach ($shunt_list as $shunt) {
    $result[$shunt] = array(
      'id' => $shunt,
      'description' => $info[$shunt]['description'],
      'status' => ($info[$shunt]['is_enabled']) ? dt('Enabled') : dt('Disabled'),
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
      return drush_set_error('SHUNT_INVALID_STATUS', dt('@status is not a valid shunt status.', array(
        '@status' => $status,
      )));
    }
  }

  // Build result table.
  $result = array();
  $info = _shunt_drush_get_info();
  foreach ($info as $id => $properties) {
    // Filter by status.
    $status = ($properties['is_enabled']) ? 'enabled' : 'disabled';
    if (!in_array($status, $status_filter)) {
      continue;
    }

    // Build row.
    $result[$id] = array(
      'id' => $id,
      'description' => $properties['description'],
      'status' => ($properties['is_enabled']) ? dt('Enabled') : dt('Disabled'),
    );
  }

  return $result;
}

/**
 * Gets all shunt entities.
 *
 * @return Drupal\shunt\Entity\Shunt[]
 *   An array of Shunt objects.
 */
function _shunt_drush_get_all_shunts() {
  static $all_shunts;
  if (!isset($all_shunts)) {
    $all_shunts = entity_load_multiple('shunt', _shunt_drush_get_all_shunt_ids());
  }
  return $all_shunts;
}

/**
 * Gets all shunt IDs.
 *
 * @return array
 *   An indexed array of shunt IDs.
 */
function _shunt_drush_get_all_shunt_ids() {
  static $all_shunt_ids;
  if (!isset($all_shunt_ids)) {
    $all_shunt_ids = array_keys(\Drupal::entityQuery('shunt')->execute());
  }
  return $all_shunt_ids;
}

/**
 * Gets all available shunt info.
 *
 * @return array
 *   An associative array of shunt info whose keys are shunt IDs and whose
 *   values are an associative array of properties for the corresponding shunt.
 */
function _shunt_drush_get_info() {
  $shunts = _shunt_drush_get_all_shunts();
  $info = array();
  foreach ($shunts as $id => $shunt) {
    $info[$id] = array(
      'id' => $id,
      'description' => $shunt->getDescription(),
      'is_enabled' => $shunt->isShuntEnabled(),
    );
  }
  return $info;
}

/**
 * Gets a list of shunt IDs for command completion.
 *
 * @return array
 *   An indexed array of available shunt IDs.
 */
function _shunt_drush_complete_shunts() {
  if (drush_bootstrap_max(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
    return array('values' => _shunt_drush_get_all_shunt_ids());
  }
}

/**
 * Performs shunt ID expansion for trailing wildcards (*).
 *
 * Given a list of shunt ID patterns, this function identifies IDs with a
 * trailing wildcard (*) and replaces them with actual shunt IDs that match the
 * expansion pattern.
 *
 * @param array $patterns
 *   An indexed array of shunt ID patterns, by reference.
 */
function _shunt_drush_expand_shunt_ids(array &$patterns) {
  $all_ids = _shunt_drush_get_all_shunt_ids();
  foreach ($patterns as $key => $pattern) {
    $pattern_minus_wildcard = rtrim($pattern, '*');
    if ($pattern_minus_wildcard !== $pattern) {
      foreach ($all_ids as $id) {
        if (substr($id, 0, strlen($pattern_minus_wildcard)) == $pattern_minus_wildcard) {
          $patterns[] = $id;
        }
      }
      unset($patterns[$key]);
      continue;
    }
  }
}

/**
 * Parses a list of shunt IDs passed in as a command argument.
 *
 * @param array $raw_args
 *   The raw arguments array passed into drush_hook_COMMAND(), as accessed there
 *   via func_get_args().
 *
 * @return array
 *   An indexed array of shunt IDs.
 */
function _shunt_drush_parse_shunt_list(array $raw_args) {
  $all_shunt_ids = _shunt_drush_get_all_shunt_ids();

  // Convert comma separated values.
  $args_array = _convert_csv_to_array($raw_args);

  // The "all" option supersedes the "shunts" argument. Return early.
  if (drush_get_option('all', FALSE)) {
    return $all_shunt_ids;
  }

  // Expand wildcards.
  _shunt_drush_expand_shunt_ids($args_array);

  // Remove duplicates.
  $unique_args = array_unique($args_array);

  // Discard non-existent shunts.
  $shunt_list = array();
  foreach ($unique_args as $id) {
    if (in_array($id, $all_shunt_ids)) {
      $shunt_list[] = $id;
    }
    else {
      drush_log(dt('No such shunt @id.', array(
        '@id' => $id,
      )), 'warning');
    }
  }

  return $shunt_list;
}

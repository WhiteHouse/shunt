<?php

/**
 * @file
 * Drush commands for the Shunt module.
 */

/**
 * Implements hook_drush_command().
 */
function shunt_drush_command() {
  $items = [];

  $items['shunt-trip'] = [
    'description' => 'Trip one or more shunts.',
    'arguments' => [
      'ids' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches.',
    ],
    'options' => [
      'all' => [
        'description' => dt('Trip all shunts. Supersedes the "shunts" argument.'),
      ],
    ],
    'examples' => [
      'drush shunt-trip example' => 'Trip the "example" shunt.',
      'drush shunt-trip example\*' => 'Trip all shunts whose IDs begin with "example".',
      'drush shunt-trip --all' => 'Trip all shunts.',
    ],
    'aliases' => ['sht'],
  ];

  $items['shunt-reset'] = [
    'description' => 'Reset one or more shunts.',
    'arguments' => [
      'ids' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches.',
    ],
    'options' => [
      'all' => [
        'description' => dt('Reset all shunts. Supersedes the "shunts" argument.'),
      ],
    ],
    'examples' => [
      'drush shunt-reset example' => 'Reset the "example" shunt.',
      'drush shunt-reset example\*' => 'Reset all shunts whose IDs begin with "example".',
      'drush shunt-reset --all' => 'Reset all shunts.',
    ],
    'aliases' => ['shr'],
  ];

  $items['shunt-info'] = [
    'description' => 'Show detailed info for one or more shunts.',
    'arguments' => [
      'shunts' => 'A list of shunt IDs. You can use a wildcard (*) at the end of IDs to show info for multiple matches. If no argument is provided it will show info for all available shunts.',
    ],
    'examples' => [
      'drush shunt-info example' => 'Show info for the "example" shunt.',
      'drush shunt-info example\*' => 'Show info for all shunts whose IDs begin with "example".',
      'drush shunt-info' => 'Show info for all shunts.',
    ],
    'aliases' => ['shi'],
    'outputformat' => [
      'default' => 'key-value-list',
      'pipe-format' => 'json',
      'field-labels' => [
        'id' => 'ID',
        'label' => 'Label',
        'description' => 'Description',
        'status' => 'Status',
      ],
      'output-data-type' => 'format-table',
    ],
  ];

  $items['shunt-list'] = [
    'description' => 'Show a list of available shunts with their statuses.',
    'options' => [
      'status' => [
        'description' => "Filter by shunt status. Choices: tripped and/or 'not tripped'.",
        'example-value' => 'tripped',
      ],
    ],
    'examples' => [
      'drush shunt-list' => 'Show all shunts.',
      'drush shunt-list --status=tripped' => 'Show only tripped shunts.',
    ],
    'outputformat' => [
      'default' => 'table',
      'pipe-format' => 'list',
      'field-labels' => [
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
      ],
      'output-data-type' => 'format-table',
    ],
    'aliases' => ['shl'],
  ];

  return $items;
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_reset() {
  $ids = _shunt_drush_parse_shunt_list(func_get_args());
  /** @var \Drupal\shunt\Entity\Shunt[] $shunts */
  $shunts = entity_load_multiple('shunt', $ids);

  // Discard shunts that are not tripped.
  foreach ($shunts as $id => $shunt) {
    if (!$shunt->isTripped()) {
      drush_log(dt('Shunt @id is not tripped.', [
        '@id' => $id,
      ]), 'ok');
      unset($shunts[$id]);
    }
  }

  // Inform the user which shunts will finally be reset.
  if (empty($shunts)) {
    return drush_log(dt('There were no shunts that could be reset.'), 'ok');
  }
  drush_print(dt('The following shunts will be reset: @ids', [
    '@ids' => implode(', ', array_keys($shunts)),
  ]));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  // Reset shunts.
  foreach ($shunts as $shunt) {
    $shunt->reset();
  }
}

/**
 * Command argument complete callback.
 *
 * @see shunt_drush_command()
 */
function shunt_shunt_reset_complete() {
  return _shunt_drush_complete_shunts();
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_trip() {
  $ids = _shunt_drush_parse_shunt_list(func_get_args());
  /** @var \Drupal\shunt\Entity\Shunt[] $shunts */
  $shunts = entity_load_multiple('shunt', $ids);

  // Discard shunts that are already tripped.
  foreach ($shunts as $id => $shunt) {
    if ($shunt->isTripped()) {
      drush_log(dt('Shunt @id is already tripped.', [
        '@id' => $id,
      ]), 'ok');
      unset($shunts[$id]);
    }
  }

  // Inform the user which shunts will finally be tripped.
  if (empty($shunts)) {
    return drush_log(dt('There were no shunts that could be tripped.'), 'ok');
  }
  drush_print(dt('The following shunts will be tripped: @ids', [
    '@ids' => implode(', ', array_keys($shunts)),
  ]));
  if (!drush_confirm(dt('Do you want to continue?'))) {
    return drush_user_abort();
  }

  // Trip shunts.
  foreach ($shunts as $shunt) {
    $shunt->trip();
  }
}

/**
 * Command argument complete callback.
 *
 * @see shunt_drush_command()
 */
function shunt_shunt_trip_complete() {
  return _shunt_drush_complete_shunts();
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @see shunt_drush_command()
 */
function drush_shunt_info() {
  $args = func_get_args();
  // If a list of shunts is supplied, parse it.
  if (!empty($args)) {
    $shunt_list = _shunt_drush_parse_shunt_list(func_get_args());
  }
  // Otherwise supply the list of all available shunts.
  else {
    $shunt_list = _shunt_drush_get_all_shunt_ids();
  }

  $shunts = _shunt_drush_get_all_shunts();
  $result = [];
  foreach ($shunt_list as $id) {
    $result[$id] = [
      'id' => $id,
      'label' => $shunts[$id]->label(),
      'description' => $shunts[$id]->getDescription(),
      'status' => ($shunts[$id]->isTripped()) ? dt('Tripped') : dt('Not tripped'),
    ];
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
  $all_statuses = ['tripped', 'not tripped'];
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
      return drush_set_error('SHUNT_INVALID_STATUS', dt('@status is not a valid shunt status.', [
        '@status' => $status,
      ]));
    }
  }

  // Build result table.
  $result = [];
  foreach (_shunt_drush_get_all_shunts() as $shunt) {
    // Filter by status.
    $status = ($shunt->isTripped()) ? 'tripped' : 'not tripped';
    if (!in_array($status, $status_filter)) {
      continue;
    }

    // Build row.
    $result[$shunt->id()] = [
      'name' => dt('!label (@id)', [
        '!label' => $shunt->label(),
        '@id' => $shunt->id(),
      ]),
      'description' => $shunt->getDescription(),
      'status' => ($shunt->isTripped()) ? dt('Tripped') : dt('Not tripped'),
    ];
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
 * Gets a list of shunt IDs for command completion.
 *
 * @return array
 *   An indexed array of available shunt IDs.
 */
function _shunt_drush_complete_shunts() {
  if (drush_bootstrap_max(DRUSH_BOOTSTRAP_DRUPAL_FULL)) {
    return ['values' => _shunt_drush_get_all_shunt_ids()];
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
  $shunt_list = [];
  foreach ($unique_args as $id) {
    if (in_array($id, $all_shunt_ids)) {
      $shunt_list[] = $id;
    }
    else {
      drush_log(dt('No such shunt @id.', [
        '@id' => $id,
      ]), 'warning');
    }
  }

  return $shunt_list;
}

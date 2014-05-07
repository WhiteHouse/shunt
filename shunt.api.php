<?php

/**
 * @file
 * Hooks provided by the Shunt module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * React to a shunt being changed.
 *
 * Perform one-time actions after a shunt gets enabled or disabled. This hook
 * will be invoked once for each change in a changeset as it happens. If you
 * want to react only once to the whole set, use hook_shunt_post_changeset()
 * instead.
 *
 * @param string $shunt
 *   The machine name of shunt that was changed.
 * @param bool $change
 *   The change that took place: either "enabled" or "disabled".
 */
function hook_shunt_post_change($shunt, $change) {
  // React to a particular shunt being changed.
  if ($shunt == 'example') {

    // React differently based on operation.
    if ($change == 'enabled') {
      drupal_set_message(t('You just enabled "example" shunt!'));
    }
    else {
      drupal_set_message(t('You just disabled "example" shunt!'));
    }
  }

  // React to a change to ANY shunt--whether it's defined in your module or not.
  drupal_set_message(t('You just changed "@name"!', array('@name' => $shunt)));
}

/**
 * React to a batch of shunt changes.
 *
 * Perform one-time actions after an entire batch of shunt changes is completed.
 * If you want to react to each individual shunt change, use
 * hook_shunt_post_change() instead.
 *
 * This hook is only invoked if the status of at least one shunt is actually
 * changed.
 *
 * @param array $changes
 *   An array of shunt/change pairs for shunts whose statuses changed, where
 *   each key is a shunt machine name and its corresponding value is the change
 *   that took place: either "enabled" or "disabled".
 */
function hook_shunt_post_changeset($changes) {
  // This hook provides great flexibility to test for complex conditions so as
  // to avoid performing expensive operations more often than absolutely
  // necessary.
  if (isset($changes['example_feature1']) && isset($changes['example_feature2'])) {
    if ($changes['example_feature1'] == 'enabled') {
      // Avoid scheduling expensive operations for shunt enable, when your site
      // is probably already under strain.
    }
    elseif ($changes['example_feature1'] == 'disabled') {
      // Clear caches, send an "all clear" email, etc.
    }
  }
}

/**
 * Alter shunt definitions.
 *
 * @param array $shunts
 *   An associative array of shunt definitions, where each item has a key of a
 *   shunt machine and a value of the corresponding shunt definition array.
 */
function hook_shunts_alter(&$shunts) {
  // You can change arbitrary shunt definition details.
  $shunts['shunt']['description'] = t('A different description');

  // Or remove undesired shunts altogether.
  unset($shunts['shuntexample']);
}

/**
 * @} End of "addtogroup hooks".
 */

/**
 * Demonstrates how to use shunts to make a module fail gracefully.
 */
function shunt_demonstrate_shunt_use() {
  // Get the state of the shunts.
  $module_exists = \Drupal::moduleHandler()->moduleExists('shunt');
  $shunt_manager = \Drupal::service('plugin.manager.shunt');
  $default_shunt_is_enabled = ($module_exists && $shunt_manager->shuntIsEnabled('shunt')) ? TRUE : FALSE;
  $specific_shunt_is_enabled = ($module_exists && $$shunt_manager->shuntIsEnabled('example')) ? TRUE : FALSE;

  // Depend on both shunts.
  if ($default_shunt_is_enabled || $specific_shunt_is_enabled) {
    // One of the shunts is enabled. Fail gracefully.
  }
  else {
    // The shunts are disabled. Continue.
  }
}

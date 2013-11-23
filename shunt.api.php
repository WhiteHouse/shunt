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
 * Define shunts.
 *
 * For more granular control than the default, master shunt provides, you can
 * define any number of additional shunts and react to them individually. By
 * depending on these, you can choose on a case-by-case basis which pieces of
 * functionality to disable rather than having to disable all or none.
 *
 * Shunt machine names can be any valid PHP label except for "all". They should
 * be prefixed with the name of the module that defines them in order to avoid
 * namespace conflicts. Beyond that, names have have no intrinsic meaning--the
 * effect a given shunt has is entirely dependent on the application code that
 * uses it.
 *
 * Shunt descriptions should be plain text (no HTML). Anything else will be
 * stripped out.
 *
 * @return array
 *   An array of shunts. Each shunt item is keyed by its machine name and has a
 *   value of a translated description string.
 */
function hook_shunt_info() {
  return array(
    // It can be helpful to define a "master" shunt that toggles ALL
    // functionality for your module as well as individual shunts for particular
    // pieces of functionality.
    'example' => t('The master shunt for the Example module. This toggles ALL module functionality.'),
    'example_feature1' => t('This toggles feature 1 of the Example module.'),
    'example_feature2' => t('This toggles feature 2 of the Example module.'),
  );
}

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
      drupal_set_message(t('You just enabled "example"!'));
    }
    else {
      drupal_set_message(t('You just disabled "example"!'));
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
 * @} End of "addtogroup hooks".
 */

/**
 * Demonstrates how to use shunts to make a module fail gracefully.
 */
function shunt_demonstrate_shunt_use() {
  // Get the state of the shunts.
  $master_shunt_is_enabled = (module_exists('shunt') && shunt_is_enabled()) ? TRUE : FALSE;
  $specific_shunt_is_enabled = (module_exists('shunt') && shunt_is_enabled('example')) ? TRUE : FALSE;

  // Depend on both shunts.
  if ($master_shunt_is_enabled || $specific_shunt_is_enabled) {
    // One of the shunts is enabled. Fail gracefully.
  }
  else {
    // The shunts are disabled. Continue.
  }
}

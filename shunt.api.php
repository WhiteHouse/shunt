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
 * Shunt machine names should be prefixed with the name of the module that
 * defines them in order to avoid namespace conflicts. Beyond that, names have
 * have no intrinsic meaning--the effect a given shunt has is entirely dependent
 * on the application code that uses it.
 *
 * @return array
 *   An array of shunts. Each shunt item is keyed by its machine name and has a
 *   value of a translated description string.
 */
function hook_shunt() {
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
 * React to a shunt being enabled.
 *
 * Perform one-time actions in the event that a shunt gets enabled.
 *
 * @param string $shunt
 *   The machine name of shunt that was just enabled.
 */
function hook_shunt_enable($shunt) {
  // React to a particular shunt being enabled.
  if ($shunt == 'example') {
    drupal_set_message(t('You just enabled "example"!'));
    return;
  }

  // React to ANY shunt being enabled, whether it's defined in your module or
  // not.
  drupal_set_message(t('You just enabled "%name"!', array('%name' => $shunt)));
}

/**
 * React to a shunt being disabled.
 *
 * Perform one-time actions in the event that a shunt gets disabled.
 *
 * @param string $shunt
 *   The machine name of shunt that was just disabled.
 */
function hook_shunt_disable($shunt) {
  // React to a particular shunt being disabled.
  if ($shunt == 'example') {
    drupal_set_message(t('You just disabled "example"!'));
    return;
  }

  // React to ANY shunt being disabled, whether it's defined in your module or
  // not.
  drupal_set_message(t('You just disabled "%name"!', array('%name' => $shunt)));
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

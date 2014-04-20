<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntHandlerInterface.
 */

namespace Drupal\shunt;

/**
 * An interface for classes that manage shunts.
 */
interface ShuntHandlerInterface {

  /**
   * Disables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to disable.
   */
  public static function disable($shunt);

  /**
   * Disables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to disable.
   */
  public static function disableMultiple($shunts);

  /**
   * Enables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to enable.
   *
   * @see Drupal\shunt\ShuntHandler::disable()
   */
  public static function enable($shunt);

  /**
   * Enables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to enable.
   */
  public static function enableMultiple($shunts);

  /**
   * Determines whether a given shunt exists.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt exists or FALSE if it doesn't.
   */
  public static function exists($shunt);

  /**
   * Gets an array of available shunt definitions.
   *
   * @return array
   *   An array of shunts. Each shunt item is keyed by its machine name and has
   *   a value of a translated description string.
   *
   * @see hook_shunt_info()
   */
  public static function getDefinitions();

  /**
   * Determines whether a given shunt is enabled or not.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
   */
  public static function isEnabled($shunt);

  /**
   * Sets a given set of shunt/status pairs.
   *
   * @param array $statuses
   *   An array of shunt/status pairs where each key is a shunt machine name and
   *   its corresponding value is the new status value: TRUE for enabled or
   *   FALSE for disabled.
   */
  public static function setStatusMultiple($statuses);

}

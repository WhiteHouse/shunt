<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntInterface.
 */

namespace Drupal\shunt\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining a shunt entity.
 */
interface ShuntInterface extends ConfigEntityInterface {

  /**
   * Returns the description of the shunt.
   *
   * @return string
   *   The description of the shunt.
   */
  public function getDescription();

  /**
   * Determines whether the shunt is enabled or not.
   *
   * @return bool
   *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
   */
  public function isShuntEnabled();

  /**
   * Enables the shunt.
   */
  public function enableShunt();

  /**
   * Disables the shunt.
   */
  public function disableShunt();

}

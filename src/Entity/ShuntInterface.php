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
   * Determines whether the shunt is protected or not.
   *
   * A protected shunt cannot be deleted via the UI.
   *
   * @return bool
   *   Returns TRUE if the shunt is protected or FALSE if not.
   */
  public function isProtected();

  /**
   * Determines whether the shunt is tripped or not.
   *
   * @return bool
   *   Returns TRUE if the shunt is tripped or FALSE if not.
   */
  public function isTripped();

  /**
   * Trips the shunt.
   */
  public function trip();

  /**
   * Resets the shunt.
   */
  public function reset();

  /**
   * Deletes the shunt's stored state.
   */
  public function deleteState();

}

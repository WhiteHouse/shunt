<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntInterface.
 */

namespace Drupal\shunt;

/**
 * An interface for a Shunt class.
 */
interface ShuntInterface {

  /**
   * Shunt constructor.
   *
   * @param string $name
   *   The shunt machine name.
   * @param string $description
   *   The shunt description.
   */
  public function __construct($name, $description);

  /**
   * Gets the shunt name.
   *
   * @return string
   *   The shunt name.
   */
  public function getName();

  /**
   * Gets the shunt description.
   *
   * @return string
   *   The shunt description.
   */
  public function getDescription();

}

<?php

/**
 * @file
 * Contains \Drupal\shunt\Shunt.
 */

namespace Drupal\shunt;

use Drupal;

/**
 * Defines the shunt object.
 */
class Shunt {

  /**
   * Disables one or all shunts.
   *
   * @param string $shunt
   *   (optional) The machine name of the shunt to disable. Defaults to NULL
   *   (all).
   */
  public static function disable($shunt = NULL) {
    if ($shunt) {
      static::setStatus($shunt, FALSE);
    }
    else {
      $shunts = self::getDefinitions();
      foreach ($shunts as $name => $description) {
        static::setStatus($name, FALSE);
      }
    }
  }

  /**
   * Enables one or all shunts.
   *
   * @param string $shunt
   *   (optional) The machine name of the shunt to enable. Defaults to NULL for
   *   all.
   */
  public static function enable($shunt = NULL) {
    if ($shunt) {
      static::setStatus($shunt, TRUE);
    }
    else {
      $shunts = self::getDefinitions();
      foreach ($shunts as $name => $description) {
        static::setStatus($name, TRUE);
      }
    }
  }

  /**
   * Determines whether a given shunt exists.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt exists or FALSE if it doesn't.
   */
  public static function exists($shunt) {
    // Make sure shunt name is valid first.
    if (!(is_string($shunt) && strlen($shunt))) {
      return FALSE;
    }

    $shunts = self::getDefinitions();
    return array_key_exists($shunt, $shunts);
  }

  /**
   * Gets an array of available shunt definitions.
   *
   * @return array
   *   An array of shunts. Each shunt item is keyed by its machine name and has
   *   a value of a translated description string.
   *
   * @see hook_shunt()
   */
  public static function getDefinitions() {
    $shunts = &drupal_static(__FUNCTION__);
    if (!isset($shunts)) {
      // Get definitions.
      $shunts = module_invoke_all('shunt');
      ksort($shunts);
    }
    return $shunts;
  }

  /**
   * Determines whether a given shunt is enabled or not.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
   */
  public static function isEnabled($shunt) {
    // A non-existant shunt may be considered to be disabled.
    if (!self::exists($shunt)) {
      return FALSE;
    }

    return Drupal::state()->get("shunt.{$shunt}", FALSE);
  }

  /**
   * Sets the status of a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   * @param bool $status
   *   TRUE to enable or FALSE to disable.
   */
  public static function setStatus($shunt, $status) {
    self::setStatusMultiple(array($shunt => $status));
  }

  /**
   * Sets the status of a given set of shunts.
   *
   * @param array $shunts
   *   An array of shunt statuses. Each item key is a shunt machine name, and
   *   its corresponding value is the new status for that shunt.
   * @param bool $warn_when_same
   *   (optional) Whether or not a warning should be issued when refusing to set
   *   a shunt status because the new status is the same as the old one. TRUE if
   *   it should or FALSE if it should not. Defaults to TRUE.
   */
  public static function setStatusMultiple($shunts, $warn_when_same = TRUE) {
    foreach ($shunts as $shunt => $status) {
      // Store arguments for t() reused below.
      $args = array('@name' => $shunt);

      // Make sure the shunt exists.
      if (!self::exists($shunt)) {
        drupal_set_message(t('No such shunt "@name".', $args), 'error');
        continue;
      }

      // Type cast the new value for strict comparison.
      $new_status = (bool) $status;

      // Find out if the new status is actually different from the current one
      // and don't invoke hooks unless it is.
      $current_status = self::isEnabled($shunt);
      if ($new_status === $current_status) {
        // Conditionally warn the user.
        if ($warn_when_same) {
          if ($new_status) {
            $message = t('Shunt "@name" is already enabled.', $args);
          }
          else {
            $message = t('Shunt "@name" is already disabled.', $args);
          }
          drupal_set_message($message, 'warning');
        }

        continue;
      }

      // Update the status.
      Drupal::state()->set("shunt.{$shunt}", $new_status);

      if ($new_status) {
        // Fire hook_shunt_enable().
        module_invoke_all('shunt_enable', $shunt);

        // Report success.
        drupal_set_message(t('Shunt "@name" has been enabled.', $args));
      }
      else {
        // Fire hook_shunt_disable().
        module_invoke_all('shunt_disable', $shunt);

        // Report success.
        drupal_set_message(t('Shunt "@name" has been disabled.', $args));
      }
    }
  }

}

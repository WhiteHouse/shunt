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
   * Disables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to disable.
   *
   * @see Drupal\shunt\Shunt::enable()
   */
  public static function disable($shunt) {
    self::disableMultiple(array($shunt));
  }

  /**
   * Disables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to disable.
   *
   * @see Drupal\shunt\Shunt::disable()
   * @see Drupal\shunt\Shunt::enableMultiple()
   */
  public static function disableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, FALSE);
    self::setStatusMultiple($statuses);
  }

  /**
   * Enables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to enable.
   *
   * @see Drupal\shunt\Shunt::disable()
   */
  public static function enable($shunt) {
    self::enableMultiple(array($shunt));
  }

  /**
   * Enables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to enable.
   *
   * @see Drupal\shunt\Shunt::enable()
   * @see Drupal\shunt\Shunt::disableMultiple()
   */
  public static function enableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, TRUE);
    self::setStatusMultiple($statuses);
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
      $shunts = Drupal::moduleHandler()->invokeAll('shunt');
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
   * This is an internal-only method. Consumer code should use
   * Drupal\shunt\Shunt::enable() or Drupal\shunt\Shunt::disable() instead.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   * @param bool $status
   *   TRUE to enable or FALSE to disable.
   * @param bool $warn_when_same
   *   (optional) Whether or not a warning should be issued when refusing to set
   *   a shunt status because the new status is the same as the old one. TRUE if
   *   it should or FALSE if it should not. Defaults to TRUE.
   *
   * @return bool
   *   Returns TRUE if the status was changed or FALSE if not.
   *
   * @see Drupal\shunt\Shunt::setStatusMultiple()
   */
  protected static function setStatus($shunt, $status, $warn_when_same = TRUE) {
    // Store arguments for t() reused below.
    $args = array('@name' => $shunt);

    // Make sure the shunt exists.
    if (!self::exists($shunt)) {
      drupal_set_message(t('No such shunt "@name".', $args), 'error');
      return FALSE;
    }

    // Type cast the new value for strict comparison.
    $bool_status = (bool) $status;

    // Find out if the new status is actually different from the current one
    // and don't invoke hooks unless it is.
    $current_status = self::isEnabled($shunt);
    if ($bool_status === $current_status) {
      if ($warn_when_same) {
        $warning_message['enabled'] = t('Shunt "@name" is already enabled.', $args);
        $warning_message['disabled'] = t('Shunt "@name" is already disabled.', $args);
        drupal_set_message($warning_message[$bool_status ? 'enabled' : 'disabled'], 'warning');
      }

      return FALSE;
    }

    // Set the status.
    Drupal::state()->set("shunt.{$shunt}", $bool_status);

    // Report success.
    $success_message['enabled'] = t('Shunt "@name" has been enabled.', $args);
    $success_message['disabled'] = t('Shunt "@name" has been disabled.', $args);
    drupal_set_message($success_message[$bool_status ? 'enabled' : 'disabled']);

    $change = $bool_status ? 'enabled' : 'disabled';
    Drupal::moduleHandler()->invokeAll('shunt_post_change', array($shunt, $change));

    return TRUE;
  }

  /**
   * Sets a given set of shunt/status pairs.
   *
   * @param array $statuses
   *   An array of shunt/status pairs where each key is a shunt machine name and
   *   its corresponding value is the new status value: TRUE for enabled or
   *   FALSE for disabled.
   * @param bool $warn_when_same
   *   (optional) Whether or not a warning should be issued when refusing to set
   *   a shunt status because the new status is the same as the old one. TRUE if
   *   it should or FALSE if it should not. Defaults to TRUE.
   */
  public static function setStatusMultiple($statuses, $warn_when_same = TRUE) {
    // Iterate over statuses.
    $changes = array();
    foreach ($statuses as $shunt => $status) {
      $bool_status = (bool) $status;
      $changed = self::setStatus($shunt, (bool) $bool_status, $warn_when_same);
      if ($changed) {
        $changes[$shunt] = $bool_status ? 'enabled' : 'disabled';
      }
    }

    // Only invoke hooks if changes actually took place.
    if (!empty($changes)) {
      Drupal::moduleHandler()->invokeAll('shunt_post_changeset', array($changes));
    }
  }

}

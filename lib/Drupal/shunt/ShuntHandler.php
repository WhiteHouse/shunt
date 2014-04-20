<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntHandler.
 */

namespace Drupal\shunt;

use Drupal;
use Drupal\Component\Utility\String;

/**
 * Defines a class for managing shunts.
 */
class ShuntHandler implements ShuntHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public static function disable($shunt) {
    static::disableMultiple(array($shunt));
  }

  /**
   * {@inheritdoc}
   */
  public static function disableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, FALSE);
    static::setStatusMultiple($statuses);
  }

  /**
   * {@inheritdoc}
   */
  public static function enable($shunt) {
    static::enableMultiple(array($shunt));
  }

  /**
   * {@inheritdoc}
   */
  public static function enableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, TRUE);
    static::setStatusMultiple($statuses);
  }

  /**
   * {@inheritdoc}
   */
  public static function exists($shunt) {
    if (!static::isValidName($shunt)) {
      return FALSE;
    }

    $shunt_info = static::getDefinitions();
    return array_key_exists($shunt, $shunt_info);
  }

  /**
   * {@inheritdoc}
   */
  public static function getDefinitions() {
    $definitions = &drupal_static(__FUNCTION__);
    if (!isset($definitions)) {
      // Get definitions.
      $definitions = Drupal::moduleHandler()->invokeAll('shunt_info');

      foreach ($definitions as $name => $description) {
        // Reject invalid shunt names.
        if (!static::isValidName($name)) {
          throw new ShuntException("Invalid shunt name \"{$name}\"");
        }

        // Sanitize descriptions.
        $definitions[$name] = String::checkPlain($description);
      }

      // Sort by machine name.
      ksort($definitions);
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function isEnabled($shunt) {
    // A non-existant shunt may be considered to be disabled.
    if (!static::exists($shunt)) {
      return FALSE;
    }

    return Drupal::state()->get("shunt.{$shunt}", FALSE);
  }

  /**
   * Determines whether a given shunt name is valid or not.
   *
   * Any valid PHP label is a valid shunt name--except for "all", which is
   * reserved for use with Drush.
   *
   * @param string $name
   *   The name to test.
   *
   * @return bool
   *   Returns TRUE if the given name is valid or FALSE if it is not.
   */
  public static function isValidName($name) {
    if (!is_string($name)) {
      return FALSE;
    }

    $reserved_words = array('all');
    if (in_array($name, $reserved_words)) {
      return FALSE;
    }

    // @see http://php.net/manual/en/language.variables.basics.php
    $pattern = '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/';
    return (bool) preg_match($pattern, $name);
  }

  /**
   * Sets the status of a given shunt.
   *
   * This is an internal-only method. Consumer code should use
   * Drupal\shunt\ShuntHandler::enable() or Drupal\shunt\ShuntHandler::disable()
   * instead.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   * @param bool $status
   *   TRUE to enable or FALSE to disable.
   *
   * @return bool
   *   Returns TRUE if the status was changed or FALSE if not.
   */
  protected static function setStatus($shunt, $status) {
    // Store arguments for t() reused below.
    $args = array('@name' => $shunt);

    // Make sure the shunt exists.
    if (!static::exists($shunt)) {
      drupal_set_message(t('No such shunt "@name".', $args), 'error');
      return FALSE;
    }

    // Type cast the new value for strict comparison.
    $bool_status = (bool) $status;

    // Find out if the new status is actually different from the current one
    // and don't invoke hooks unless it is.
    $current_status = static::isEnabled($shunt);
    if ($bool_status === $current_status) {
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
   * {@inheritdoc}
   */
  public static function setStatusMultiple($statuses) {
    // Iterate over statuses.
    $changes = array();
    foreach ($statuses as $shunt => $status) {
      $bool_status = (bool) $status;
      $changed = static::setStatus($shunt, (bool) $bool_status);
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

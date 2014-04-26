<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntHandlerInterface.
 */

namespace Drupal\shunt;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * An interface for classes that manage shunts.
 */
interface ShuntHandlerInterface {

  /**
   * Constructs a ShuntHandler object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation_manager
   *   The translation manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, StateInterface $state, TranslationInterface $translation_manager);

  /**
   * Disables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to disable.
   */
  public function disable($shunt);

  /**
   * Disables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to disable.
   */
  public function disableMultiple($shunts);

  /**
   * Enables a given shunt.
   *
   * @param string $shunt
   *   The machine name of the shunt to enable.
   *
   * @see Drupal\shunt\ShuntHandler::disable()
   */
  public function enable($shunt);

  /**
   * Enables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to enable.
   */
  public function enableMultiple($shunts);

  /**
   * Determines whether a given shunt exists.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt exists or FALSE if it doesn't.
   */
  public function exists($shunt);

  /**
   * Gets an array of available shunt definitions.
   *
   * @return array
   *   An array of shunts. Each shunt item is keyed by its machine name and has
   *   a value of a translated description string.
   *
   * @see hook_shunt_info()
   */
  public function getDefinitions();

  /**
   * Determines whether a given shunt is enabled or not.
   *
   * @param string $shunt
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
   */
  public function isEnabled($shunt);

  /**
   * Sets a given set of shunt/status pairs.
   *
   * @param array $statuses
   *   An array of shunt/status pairs where each key is a shunt machine name and
   *   its corresponding value is the new status value: TRUE for enabled or
   *   FALSE for disabled.
   */
  public function setStatusMultiple($statuses);

}

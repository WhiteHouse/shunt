<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntHandler.
 */

namespace Drupal\shunt;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Defines a class for managing shunts.
 */
class ShuntHandler implements ShuntHandlerInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandlerInterface $module_handler, StateInterface $state, TranslationInterface $translation_manager) {
    $this->moduleHandler = $module_handler;
    $this->state = $state;
    $this->translationManager = $translation_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function disable($shunt) {
    $this->disableMultiple(array($shunt));
  }

  /**
   * {@inheritdoc}
   */
  public function disableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, FALSE);
    $this->setStatusMultiple($statuses);
  }

  /**
   * {@inheritdoc}
   */
  public function enable($shunt) {
    $this->enableMultiple(array($shunt));
  }

  /**
   * {@inheritdoc}
   */
  public function enableMultiple($shunts) {
    $statuses = array_fill_keys($shunts, TRUE);
    $this->setStatusMultiple($statuses);
  }

  /**
   * {@inheritdoc}
   */
  public function exists($shunt) {
    if (!Shunt::isValidName($shunt)) {
      return FALSE;
    }

    $shunt_info = $this->getDefinitions();
    return array_key_exists($shunt, $shunt_info);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = &drupal_static(__FUNCTION__);
    if (!isset($definitions)) {
      // Get definitions.
      $definitions = $this->moduleHandler->invokeAll('shunt_info');

      foreach ($definitions as $name => $description) {
        $shunt = new Shunt($name, $description);
        $definitions[$shunt->getName()] = $shunt->getDescription();
      }

      // Sort by machine name.
      ksort($definitions);
    }
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled($shunt) {
    // A non-existent shunt may be considered to be disabled.
    if (!$this->exists($shunt)) {
      return FALSE;
    }

    return $this->state->get("shunt.{$shunt}", FALSE);
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
  protected function setStatus($shunt, $status) {
    // Store arguments for t() reused below.
    $args = array('@name' => $shunt);

    // Make sure the shunt exists.
    if (!$this->exists($shunt)) {
      drupal_set_message($this->t('No such shunt "@name".', $args), 'error');
      return FALSE;
    }

    // Type cast the new value for strict comparison.
    $bool_status = (bool) $status;

    // Find out if the new status is actually different from the current one
    // and don't invoke hooks unless it is.
    $current_status = $this->isEnabled($shunt);
    if ($bool_status === $current_status) {
      return FALSE;
    }

    // Set the status.
    $this->state->set("shunt.{$shunt}", $bool_status);

    // Report success.
    $success_message['enabled'] = $this->t('Shunt "@name" has been enabled.', $args);
    $success_message['disabled'] = $this->t('Shunt "@name" has been disabled.', $args);
    drupal_set_message($success_message[$bool_status ? 'enabled' : 'disabled']);

    $change = $bool_status ? 'enabled' : 'disabled';
    $this->moduleHandler->invokeAll('shunt_post_change', array($shunt, $change));

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setStatusMultiple($statuses) {
    // Iterate over statuses.
    $changes = array();
    foreach ($statuses as $shunt => $status) {
      $bool_status = (bool) $status;
      $changed = $this->setStatus($shunt, (bool) $bool_status);
      if ($changed) {
        $changes[$shunt] = $bool_status ? 'enabled' : 'disabled';
      }
    }

    // Only invoke hooks if changes actually took place.
    if (!empty($changes)) {
      $this->moduleHandler->invokeAll('shunt_post_changeset', array($changes));
    }
  }

  /**
   * Translates a string to the current language or to a given language.
   *
   * See the t() documentation for details.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return $this->translationManager->translate($string, $args, $options);
  }
}

<?php

/**
 * @file
 * Contains \Drupal\shunt\ShuntManager.
 */

namespace Drupal\shunt;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Shunt plugin manager.
 */
class ShuntManager extends DefaultPluginManager {

  use StringTranslationTrait;

  /**
   * Constructs a ShuntManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache, LanguageManagerInterface $language_manager, StateInterface $state) {
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache, $language_manager, 'shunt_plugins', array(
      'shunt' => TRUE,
    ));
    $this->discovery = $this->getDiscovery();
    $this->state = $state;
    $this->alterInfo('shunts');
  }

  /**
   * Creates a YAML discovery for shunts.
   *
   * @return \Drupal\Component\Discovery\YamlDiscovery
   *   An YAML discovery instance.
   */
  protected function getDiscovery() {
    return new YamlDiscovery('shunts', $this->moduleHandler->getModuleDirectories());
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    ksort($definitions);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    static::validateDefinition($definition);
    $definition = static::sanitizeDefinition($definition);
  }

  /**
   * Validates a shunt definition.
   *
   * @param array $definition
   *   The plugin definition.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   In case of an invalid definition.
   */
  public static function validateDefinition($definition) {
    $plugin_id = $definition['id'];
    if (!static::isValidShuntName($plugin_id)) {
      throw new PluginException(sprintf('Invalid shunt name "%s".', $plugin_id));
    }
    if (!static::isValidShuntDescription($definition['description'])) {
      throw new PluginException(sprintf('Invalid description for shunt "%s".', $plugin_id));
    }
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
   *   TRUE if the given name is valid or FALSE if it is not.
   */
  public static function isValidShuntName($name) {
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
   * Determines whether a given shunt description is valid or not.
   *
   * Any string is a valid shunt description.
   *
   * @param string $description
   *   The description to test.
   *
   * @return bool
   *   TRUE if the given description is valid or FALSE if it is not.
   */
  public static function isValidShuntDescription($description) {
    return is_string($description);
  }

  /**
   * Sanitizes a shunt definition.
   *
   * @param array $definition
   *   The plugin definition.
   *
   * @return array
   *   The sanitized plugin definition.
   */
  public static function sanitizeDefinition($definition) {
    $definition['description'] = strip_tags($definition['description']);
    return $definition;
  }

  /**
   * Disables a given shunt.
   *
   * @param string $name
   *   The machine name of the shunt to disable.
   */
  public function disableShunt($name) {
    $this->disableShuntMultiple(array($name));
  }

  /**
   * Disables a given list of shunts.
   *
   * @param string $shunts
   *   An indexed array of the machine names of the shunts to disable.
   */
  public function disableShuntMultiple($shunts) {
    $statuses = array_fill_keys($shunts, FALSE);
    $this->setShuntStatusMultiple($statuses);
  }

  /**
   * Enables a given shunt.
   *
   * @param string $name
   *   The machine name of the shunt to enable.
   *
   * @see Drupal\shunt\ShuntHandler::disable()
   */
  public function enableShunt($name) {
    $this->enableShuntMultiple(array($name));
  }

  /**
   * Enables a given list of shunts.
   *
   * @param string $names
   *   An indexed array of the machine names of the shunts to enable.
   */
  public function enableShuntMultiple($names) {
    $statuses = array_fill_keys($names, TRUE);
    $this->setShuntStatusMultiple($statuses);
  }

  /**
   * Determines whether a given shunt exists.
   *
   * @param string $name
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt exists or FALSE if it doesn't.
   */
  public function shuntExists($name) {
    if (!static::isValidShuntName($name)) {
      return FALSE;
    }

    return (bool) $this->getDefinition($name);
  }

  /**
   * Determines whether a given shunt is enabled or not.
   *
   * @param string $name
   *   The machine name of the shunt.
   *
   * @return bool
   *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
   */
  public function shuntIsEnabled($name) {
    // A non-existent shunt may be considered to be disabled.
    if (!$this->shuntExists($name)) {
      return FALSE;
    }

    return $this->state->get("shunt.{$name}", FALSE);
  }

  /**
   * Sets a given set of shunt/status pairs.
   *
   * @param array $statuses
   *   An array of shunt/status pairs where each key is a shunt machine name and
   *   its corresponding value is the new status value: TRUE for enabled or
   *   FALSE for disabled.
   */
  public function setShuntStatusMultiple($statuses) {
    // Iterate over statuses.
    $changes = array();
    foreach ($statuses as $name => $status) {
      $bool_status = (bool) $status;
      $changed = $this->setShuntStatus($name, (bool) $bool_status);
      if ($changed) {
        $changes[$name] = $bool_status ? 'enabled' : 'disabled';
      }
    }

    // Only invoke hooks if changes actually took place.
    if (!empty($changes)) {
      $this->moduleHandler->invokeAll('shunt_post_changeset', array($changes));
    }
  }

  /**
   * Sets the status of a given shunt.
   *
   * This is an internal-only method. Consumer code should use enable() or
   * disable() instead.
   *
   * @param string $name
   *   The machine name of the shunt.
   * @param bool $status
   *   TRUE to enable or FALSE to disable.
   *
   * @return bool
   *   Returns TRUE if the status was changed or FALSE if not.
   */
  protected function setShuntStatus($name, $status) {
    // Store arguments for t() reused below.
    $args = array('@name' => $name);

    // Make sure the shunt exists.
    if (!$this->shuntExists($name)) {
      drupal_set_message($this->t('No such shunt "@name".', $args), 'error');
      return FALSE;
    }

    // Type cast the new value for strict comparison.
    $bool_status = (bool) $status;

    // Find out if the new status is actually different from the current one
    // and don't invoke hooks unless it is.
    $current_status = $this->shuntIsEnabled($name);
    if ($bool_status === $current_status) {
      return FALSE;
    }

    // Set the status.
    $this->state->set("shunt.{$name}", $bool_status);

    // Report success.
    $success_message['enabled'] = $this->t('Shunt "@name" has been enabled.', $args);
    $success_message['disabled'] = $this->t('Shunt "@name" has been disabled.', $args);
    drupal_set_message($success_message[$bool_status ? 'enabled' : 'disabled']);

    $change = $bool_status ? 'enabled' : 'disabled';
    $this->moduleHandler->invokeAll('shunt_post_change', array($name, $change));

    return TRUE;
  }

}

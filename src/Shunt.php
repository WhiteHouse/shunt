<?php

/**
 * @file
 * Contains \Drupal\shunt\Shunt.
 */

namespace Drupal\shunt;

use Drupal\Component\Utility\String;

/**
 * The Shunt class.
 */
class Shunt implements ShuntInterface {

  /**
   * @var string
   *   The shunt name.
   */
  protected $name;

  /**
   * @var string
   *   The shunt description.
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function __construct($name, $description) {
    $this->setName($name);
    $this->setDescription($description);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Sets the shunt name.
   *
   * @param string $name
   *   The shunt name.
   *
   * @throws \Drupal\shunt\ShuntException
   *   If an invalid shunt name is supplied.
   */
  protected function setName($name) {
    if (!static::isValidName($name)) {
      throw new ShuntException(sprintf('Invalid shunt name "%s"', $name));
    }
    $this->name = $name;
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
   * {@inheritdoc}
   */
  public function getDescription() {
    return String::checkPlain($this->description);
  }

  /**
   * Sets the shunt description.
   *
   * @param string $description
   *   The shunt description.
   *
   * @throws \Drupal\shunt\ShuntException
   *   If an invalid shunt description is supplied.
   */
  protected function setDescription($description) {
    if (!static::isValidDescription($description)) {
      throw new ShuntException(sprintf('Invalid description for shunt "%s"', $this->getName()));
    }
    $this->description = $description;
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
  public static function isValidDescription($description) {
    return is_string($description);
  }

}

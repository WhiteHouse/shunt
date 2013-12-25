<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\shunt\Shunt;

/**
 * Tests the Shunt class.
 */
class ShuntTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Shunt',
      'description' => 'Tests the Shunt module.',
      'group' => 'Shunt',
    );
  }

  /**
   * Tests shunt name validation.
   *
   * @see \Drupal\shunt\Shunt::isValidName()
   */
  public function testIsValidName() {
    // Valid names.
    $this->assertTrue(Shunt::isValidName('shunt'), 'Did not accept the default shunt name.');
    $this->assertTrue(Shunt::isValidName('_shunt'), 'Did not accept a shunt name with a leading underscore.');
    $this->assertTrue(Shunt::isValidName('shunt1'), 'Did not accept a shunt name with a digit.');
    $this->assertTrue(Shunt::isValidName('shünt'), 'Did not accept a shunt name with a valid Unicode character.');
    $this->assertTrue(Shunt::isValidName('_'), 'Did not accept a single underscore as a shunt name.');

    // Invalid names.
    $this->assertFalse(Shunt::isValidName('1shunt'), 'Did not reject a shunt name beginning with a digit.');
    $this->assertFalse(Shunt::isValidName('$hunt'), 'Did not reject a shunt name with an illegal character.');
    $this->assertFalse(Shunt::isValidName('all'), 'Did not reject the reserved shunt name "all".');
    $this->assertFalse(Shunt::isValidName(''), 'Did not reject a zero length shunt name.');
    $this->assertFalse(Shunt::isValidName(0), 'Did not reject a non-string shunt name.');
  }

}

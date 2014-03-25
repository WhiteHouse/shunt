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
   * @dataProvider providerIsValidName
   *
   * @param string $expected
   *   The expected output from the method.
   * @param string $name
   *   The name to provide to Shunt::isValidName().
   * @param string $message
   *   The message to provide as output for the test.
   *
   * @see \Drupal\shunt\Shunt::isValidName()
   */
  public function testIsValidName($expected, $name, $message) {
    $this->assertEquals($expected, Shunt::isValidName($name), $message);
  }

  /**
   * Data provider for testIsValidName().
   *
   * @see testIsValidName()
   */
  public function providerIsValidName() {
    // Valid names.
    $tests[] = array(TRUE, 'shunt', 'Did not accept the default shunt name.');
    $tests[] = array(TRUE, '_shunt', 'Did not accept a shunt name with a leading underscore.');
    $tests[] = array(TRUE, 'shunt1', 'Did not accept a shunt name with a digit.');
    $tests[] = array(TRUE, 'shünt', 'Did not accept a shunt name with a valid Unicode character.');
    $tests[] = array(TRUE, '_', 'Did not accept a single underscore as a shunt name.');

    // Invalid names.
    $tests[] = array(FALSE, '1shunt', 'Did not reject a shunt name beginning with a digit.');
    $tests[] = array(FALSE, '$hunt', 'Did not reject a shunt name with an illegal character.');
    $tests[] = array(FALSE, 'all', 'Did not reject the reserved shunt name "all".');
    $tests[] = array(FALSE, '', 'Did not reject a zero length shunt name.');
    $tests[] = array(FALSE, 0, 'Did not reject a non-string shunt name.');

    return $tests;
  }

}

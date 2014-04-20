<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntHandlerTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\Tests\UnitTestCase;
use Drupal\shunt\ShuntHandler;

/**
 * Tests the ShuntHandler class.
 */
class ShuntHandlerTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'ShuntHandler',
      'description' => 'Tests the ShuntHandler class.',
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
   *   The name to provide to ShuntHandler::isValidName().
   * @param string $message
   *   The message to provide as output for the test.
   *
   * @see \Drupal\shunt\ShuntHandler::isValidName()
   */
  public function testIsValidName($expected, $name, $message) {
    $this->assertEquals($expected, ShuntHandler::isValidName($name), $message);
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

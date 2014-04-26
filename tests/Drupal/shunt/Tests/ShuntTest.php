<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\shunt\Shunt;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Shunt class.
 *
 * @group Shunt
 *
 * @coversDefaultClass \Drupal\shunt\Shunt
 */
class ShuntTest extends UnitTestCase {

  /**
   * A valid shunt name.
   */
  const SHUNT_NAME_VALID = 'shunt';

  /**
   * An invalid shunt description.
   */
  const SHUNT_DESCRIPTION_INVALID = FALSE;

  /**
   * A valid shunt description.
   */
  const SHUNT_DESCRIPTION_VALID = 'Description';

  /**
   * An invalid shunt name.
   */
  const SHUNT_NAME_INVALID = 'all';

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Shunt',
      'description' => 'Tests the Shunt class.',
      'group' => 'Shunt',
    );
  }

  /**
   * Tests shunt description validation.
   *
   * @dataProvider providerShuntDescriptionValidation
   *
   * @param bool $expected
   *   The expected output from the method.
   * @param mixed $description
   *   The shunt description to test.
   * @param string $message
   *   The message to provide as output for the test.
   */
  public function testShuntDescriptionValidation($expected, $description, $message) {
    $this->assertEquals($expected, Shunt::isValidDescription($description), $message);
  }

  /**
   * Data provider for testShuntDescriptionValidation().
   *
   * @see testShuntDescriptionValidation()
   */
  public function providerShuntDescriptionValidation() {
    // Valid descriptions.
    $tests[] = array(TRUE, 'Description', 'Did not accept a valid shunt description.');

    // Invalid descriptions.
    $tests[] = array(FALSE, [], 'Did not reject an array shunt description.');

    return $tests;
  }

  /**
   * Tests shunt name validation.
   *
   * @dataProvider providerShuntNameValidation
   *
   * @param bool $expected
   *   The expected output from the method.
   * @param string $name
   *   The shunt name to test.
   * @param string $message
   *   The message to provide as output for the test.
   */
  public function testShuntNameValidation($expected, $name, $message) {
    $this->assertEquals($expected, Shunt::isValidName($name), $message);
  }

  /**
   * Data provider for testShuntNameValidation().
   *
   * @see testShuntNameValidation()
   */
  public function providerShuntNameValidation() {
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
    $tests[] = array(FALSE, [], 'Did not reject an array shunt name.');

    return $tests;
  }

  /**
   * Tests valid shunt construction.
   */
  public function testValidShuntConstruction() {
    $shunt = new Shunt($this::SHUNT_NAME_VALID, $this::SHUNT_DESCRIPTION_VALID);
    $this->assertEquals($this::SHUNT_NAME_VALID, $shunt->getName(), 'Failed to correctly set or get shunt name.');
    $this->assertEquals($this::SHUNT_DESCRIPTION_VALID, $shunt->getDescription(), 'Failed to correctly set or get shunt description.');
  }

  /**
   * Tests shunt construction with an invalid name.
   *
   * @expectedException \Drupal\shunt\ShuntException
   * @expectedExceptionMessage Invalid shunt name "all"
   */
  public function testShuntConstructionInvalidName() {
    new Shunt($this::SHUNT_NAME_INVALID, $this::SHUNT_DESCRIPTION_VALID);
  }

  /**
   * Tests shunt construction with an invalid description.
   *
   * @expectedException \Drupal\shunt\ShuntException
   * @expectedExceptionMessage Invalid description for shunt "shunt"
   */
  public function testShuntConstructionInvalidDescription() {
    new Shunt($this::SHUNT_NAME_VALID, $this::SHUNT_DESCRIPTION_INVALID);
  }

}

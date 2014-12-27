<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntManagerTest.
 */

namespace Drupal\Tests\shunt\Unit;

use Drupal\shunt\ShuntManager;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\shunt\ShuntManager
 * @group shunt
 */
class ShuntManagerTest extends UnitTestCase {

  /**
   * A valid shunt name.
   */
  const SHUNT_NAME_VALID = 'shunt';

  /**
   * A valid shunt description.
   */
  const SHUNT_DESCRIPTION_VALID = 'Description';

  /**
   * An invalid shunt description.
   */
  const SHUNT_DESCRIPTION_INVALID = FALSE;

  /**
   * An invalid shunt name.
   */
  const SHUNT_NAME_INVALID = FALSE;

  /**
   * Tests shunt description validation.
   *
   * @param bool $expected
   *   The expected output from the method.
   * @param mixed $description
   *   The shunt description to test.
   * @param string $message
   *   The message to provide as output for the test.
   *
   * @dataProvider providerShuntDescriptionValidation
   */
  public function testShuntDescriptionValidation($expected, $description, $message) {
    $this->assertEquals($expected, ShuntManager::isValidShuntDescription($description), $message);
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
   * @param bool $expected
   *   The expected output from the method.
   * @param string $name
   *   The shunt name to test.
   * @param string $message
   *   The message to provide as output for the test.
   *
   * @dataProvider providerShuntNameValidation
   */
  public function testShuntNameValidation($expected, $name, $message) {
    $this->assertEquals($expected, ShuntManager::isValidShuntName($name), $message);
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
   * Tests that a valid shunt definition is accepted.
   */
  public function testDefinitionValidation() {
    $definition = array(
      'id' => $this::SHUNT_NAME_VALID,
      'description' => $this::SHUNT_DESCRIPTION_VALID,
    );
    ShuntManager::validateDefinition($definition);
  }

  /**
   * Tests that an invalid shunt name is rejected.
   *
   * @expectedException \Drupal\Component\Plugin\Exception\PluginException
   * @expectedExceptionMessage Invalid shunt name ""
   */
  public function testRejectsInvalidName() {
    $definition = array(
      'id' => $this::SHUNT_NAME_INVALID,
      'description' => $this::SHUNT_DESCRIPTION_VALID,
    );
    ShuntManager::validateDefinition($definition);
  }

  /**
   * Tests that an invalid description is rejected.
   *
   * @expectedException \Drupal\Component\Plugin\Exception\PluginException
   * @expectedExceptionMessage Invalid description for shunt "shunt".
   */
  public function testInvalidDescription() {
    $definition = array(
      'id' => $this::SHUNT_NAME_VALID,
      'description' => $this::SHUNT_DESCRIPTION_INVALID,
    );
    ShuntManager::validateDefinition($definition);
  }

  /**
   * Tests shunt definition sanitization.
   *
   * @param array $expected
   *   The expected output from the method.
   * @param array $definition
   *   The shunt definition to test.
   * @param string $message
   *   The message to provide as output for the test.
   *
   * @dataProvider providerDefinitionSanitization
   */
  public function testDefinitionSanitization(array $expected, array $definition, $message) {
    $this->assertArrayEquals($expected, ShuntManager::sanitizeDefinition($definition), $message);
  }

  /**
   * Data provider for testShuntNameValidation().
   *
   * @see testDefinitionSanitization()
   */
  public function providerDefinitionSanitization() {
    $tag_free_description = array('description' => 'Now this is some HTML!');
    $html_in_description = array('description' => 'Now <em>this</em> is some <strong>HTML</strong>!<br />');

    $tests[] = array($tag_free_description, $html_in_description, 'Did not strip tags from description.');
    $tests[] = array($tag_free_description, $tag_free_description, 'Did not leave tag-free description intact.');

    return $tests;
  }

}

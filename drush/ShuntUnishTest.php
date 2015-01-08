<?php

/**
 * @file
 * Contains \Unish\ShuntUnishTest.
 */

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

  /**
   * Unish tests for the Shunt module.
   */
  class ShuntUnishTest extends CommandUnishTestCase {

    /**
     * The description of the "shunt" shunt.
     */
    const SHUNT_SHUNT_DESCRIPTION = 'Default shunt. No built-in behavior.';

    /**
     * The description of the "shuntexample" shunt.
     */
    const SHUNTEXAMPLE_SHUNT_DESCRIPTION = 'Display a fail whale at /shuntexample.';

    /**
     * An array of command options for use with CommandUnishTestCase::drush().
     *
     * @var array
     */
    protected $drushOptions = array('root' => '', 'uri' => '');

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      // Install a Drupal 8 sandbox using the testing profile.
      $sites = $this->setUpDrupal(1, TRUE, 8, 'testing');
      $this->drushOptions = array('root' => $this->webroot(), 'uri' => key($sites));

      // Symlink the Shunt module into the sandbox.
      $shunt_directory = dirname(__DIR__);
      symlink($shunt_directory, $this->webroot() . '/modules/shunt');

      // Enable the Shunt modules.
      $this->drush('pm-enable', array(
        'shunt',
        'shuntexample',
      ), $this->drushOptions + array('skip' => NULL, 'yes' => NULL));
    }

    /**
     * Returns the JSON representation of a given value, pretty printed.
     *
     * @param mixed $value
     *   The value to encode.
     * @return string
     *   Returns a JSON encoded string on success or FALSE on failure.
     */
    public static function jsonEncode($value) {
      return json_encode($value, JSON_PRETTY_PRINT);
    }

    /**
     * Tests the shunt-list command.
     */
    public function testShuntListCommand() {
      $this->drush('shunt-enable' , array('shunt'), $this->drushOptions + array('yes' => NULL));

      $shunt_list = array(
        'shunt' => array(
          'name' => 'shunt',
          'provider' => 'shunt',
          'description' => self::SHUNT_SHUNT_DESCRIPTION,
          'status' => 'Enabled',
        ),
        'shuntexample' => array(
          'name' => 'shuntexample',
          'provider' => 'shuntexample',
          'description' => self::SHUNTEXAMPLE_SHUNT_DESCRIPTION,
          'status' => 'Disabled',
        )
      );
      $output_unfiltered = static::jsonEncode($shunt_list);
      $output_enabled = static::jsonEncode(array('shunt' => $shunt_list['shunt']));
      $output_disabled = static::jsonEncode(array('shuntexample' => $shunt_list['shuntexample']));

      $options = $this->drushOptions + array('format' => 'json');

      // Test unfiltered output.
      $this->drush('shunt-list', array(), $options);
      $this->assertEquals($output_unfiltered, $this->getOutput());

      // Test "status" option.
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'));
      $this->assertEquals($output_enabled, $this->getOutput());

      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'));
      $this->assertEquals($output_disabled, $this->getOutput());

      $this->drush('shunt-list', array(), $options + array('status' => 'invalid'), NULL, NULL, self::EXIT_ERROR);
      $this->assertStringStartsWith('"invalid" is not a valid shunt status.', $this->getErrorOutput());

      $this->drush('shunt-enable' , array('shuntexample'), $this->drushOptions + array('yes' => NULL));
      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'));
      $this->assertEquals('', $this->getOutput());

      $this->drush('shunt-disable' , array(), $this->drushOptions + array('all' => NULL, 'yes' => NULL));
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'));
      $this->assertEquals('', $this->getOutput());
    }
  }

}

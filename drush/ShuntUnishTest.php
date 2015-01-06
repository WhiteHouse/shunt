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
     * Recursively converts an array to an object.
     *
     * @param array $array
     *   An arbitrary array.
     *
     * @return \stdClass
     *   Returns a class corresponding to the given array.
     */
    public static function arrayToObject(array $array) {
      $object = new \stdClass;
      foreach ($array as $key => $value) {
        if (strlen($key)) {
          if (is_array($value)) {
            $object->{$key} = static::arrayToObject($value);
          } else {
            $object->{$key} = $value;
          }
        }
      }
      return $object;
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
      $output_unfiltered = static::arrayToObject($shunt_list);
      $output_enabled = static::arrayToObject(array('shunt' => $shunt_list['shunt']));
      $output_disabled = static::arrayToObject(array('shuntexample' => $shunt_list['shuntexample']));

      $options = $this->drushOptions + array('format' => 'json');

      // Test unfiltered output.
      $this->drush('shunt-list', array(), $options);
      $this->assertEquals($output_unfiltered, $this->getOutputFromJSON());

      // Test "status" option.
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'));
      $this->assertEquals($output_enabled, $this->getOutputFromJSON());

      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'));
      $this->assertEquals($output_disabled, $this->getOutputFromJSON());

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

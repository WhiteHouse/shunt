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
     * Tests the shunt-enable command.
     */
    public function testShuntEnableCommand() {
      $this->drush('shunt-enable' , array(), $this->drushOptions);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $this->getErrorOutput());
      $this->assertFalse($this->shuntIsEnabled('shunt'), 'No shunts enabled without "shunts" argument.');

      $this->drush('shunt-enable' , array('invalid'), $this->drushOptions);
      $this->assertStringStartsWith('No such shunt "invalid".', $this->getErrorOutput(), 'Warned about invalid "shunts" argument.');

      $this->drush('shunt-enable' , array('shunt'), $this->drushOptions + array('no' => NULL));
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertFalse($this->shuntIsEnabled('shunt'), 'Shunt was not enabled with "no" option.');

      $this->drush('shunt-enable' , array('shunt'), $this->drushOptions + array('yes' => NULL));
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt'), 'Shunt was enabled with "yes" option.');

      $this->drush('shunt-enable' , array('shunt'), $this->drushOptions + array('no' => NULL));
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" is already enabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $error_output[1], 'Did not try to enable already enabled shunt.');

      $this->resetShunts();

      $this->drush('shunt-enable' , array('shunt', 'shuntexample'), $this->drushOptions + array('yes' => NULL));
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled multiple, explicitly named shunts.');

      $this->resetShunts();

      $this->drush('shunt-enable' , array(), $this->drushOptions + array('all' => NULL, 'yes' => NULL));
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt "shuntexample" has been enabled.', $error_output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled all shunts with "all" option.');

      $this->resetShunts();

      $this->drush('shunt-enable' , array('*'), $this->drushOptions + array('no' => NULL));
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded bare asterisk "shunts" argument.');
      $this->drush('shunt-enable' , array('shunt*'), $this->drushOptions + array('no' => NULL));
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and multiple matches.');
      $this->drush('shunt-enable' , array('shuntex*'), $this->drushOptions + array('no' => NULL));
      $this->assertStringStartsWith('The following shunts will be enabled: shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and single match.');
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

    /**
     * Resets all shunts to their default (disabled) state.
     */
    protected function resetShunts() {
      // @todo Figure out how to do this directly, without using Shunt Drush
      //   commands. See comment in ShuntUnishTest::shuntIsEnabled().
      $this->drush('shunt-disable' , array(), $this->drushOptions + array('all' => NULL, 'yes' => NULL));
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
    protected function shuntIsEnabled($name) {
      // @todo Figure out how to do this directly with ShuntManager to avoid
      //   using the Drush commands to test the Drush commands. At present,
      //   executing, for example,
      //   \Drupal::service('plugin.manager.shunt')->shuntIsEnabled($name)
      //   from this context results in a fatal error.
      $options = $this->drushOptions + array(
        'fields' => 'status',
        'format' => 'list',
      );
      $this->drush('shunt-info', array($name), $options);
      return $this->getOutput() === 'Enabled';
    }

  }

}

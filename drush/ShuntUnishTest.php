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
     * The sandbox site specification.
     *
     * @var string
     */
    protected $site = '';

    /**
     * All available shunt machine names.
     *
     * @var array
     */
    protected $allShunts = array('shunt', 'shuntexample');

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      // Install a Drupal 8 sandbox using the testing profile.
      $sites = $this->setUpDrupal(1, TRUE, 8, 'testing');
      $this->site = $this->webroot() . '#' . key($sites);

      // Symlink the Shunt module into the sandbox.
      $shunt_directory = dirname(__DIR__);
      symlink($shunt_directory, $this->webroot() . '/modules/shunt');

      // Enable the Shunt modules.
      $this->drush('pm-enable', $this->allShunts, array(
        'skip' => NULL,
        'yes' => NULL,
      ), $this->site);
    }

    /**
     * Returns the JSON representation of a given value, pretty printed.
     *
     * @param mixed $value
     *   The value to encode.
     *
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
      $this->drush('shunt-enable', array(), array(), $this->site);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $this->getErrorOutput());
      $this->assertFalse($this->shuntIsEnabled('shunt'), 'No shunts enabled without "shunts" argument.');

      $this->drush('shunt-enable', array('invalid'), array(), $this->site);
      $this->assertStringStartsWith('No such shunt "invalid".', $this->getErrorOutput(), 'Warned about invalid "shunts" argument.');

      $this->drush('shunt-enable', array('shunt'), array('no' => NULL), $this->site);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertFalse($this->shuntIsEnabled('shunt'), 'Shunt was not enabled with "no" option.');

      $this->drush('shunt-enable', array('shunt'), array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt'), 'Shunt was enabled with "yes" option.');

      $this->drush('shunt-enable', array('shunt'), array('no' => NULL), $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" is already enabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $error_output[1], 'Did not try to enable already enabled shunt.');

      $this->resetShunts();

      $this->drush('shunt-enable', $this->allShunts, array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled multiple, explicitly named shunts.');

      $this->resetShunts();

      $this->drush('shunt-enable', array(), array('all' => NULL, 'yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt "shuntexample" has been enabled.', $error_output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled all shunts with "all" option.');

      $this->resetShunts();

      $this->drush('shunt-enable', array('*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded bare asterisk "shunts" argument.');
      $this->drush('shunt-enable', array('shunt*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and multiple matches.');
      $this->drush('shunt-enable', array('shuntex*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-list command.
     */
    public function testShuntListCommand() {
      $this->enableShunts(array('shunt'));

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
        ),
      );
      $output_unfiltered = static::jsonEncode($shunt_list);
      $output_enabled = static::jsonEncode(array('shunt' => $shunt_list['shunt']));
      $output_disabled = static::jsonEncode(array('shuntexample' => $shunt_list['shuntexample']));

      $options = array('format' => 'json');

      // Test unfiltered output.
      $this->drush('shunt-list', array(), $options, $this->site);
      $this->assertEquals($output_unfiltered, $this->getOutput());

      // Test "status" option.
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'), $this->site);
      $this->assertEquals($output_enabled, $this->getOutput());

      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'), $this->site);
      $this->assertEquals($output_disabled, $this->getOutput());

      $this->drush('shunt-list', array(), $options + array('status' => 'invalid'), $this->site, NULL, self::EXIT_ERROR);
      $this->assertStringStartsWith('"invalid" is not a valid shunt status.', $this->getErrorOutput());

      $this->enableShunts(array('shuntexample'));
      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'), $this->site);
      $this->assertEquals('', $this->getOutput());

      $this->resetShunts();
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'), $this->site);
      $this->assertEquals('', $this->getOutput());
    }

    /**
     * Resets all shunts to their default (disabled) state.
     */
    protected function resetShunts() {
      $this->disableShunts($this->allShunts);
    }

    /**
     * Enables a given list of shunts.
     *
     * @param array $names
     *   An indexed array of shunt names.
     */
    protected function enableShunts(array $names) {
      $statuses = array_fill_keys($names, TRUE);
      $this->setShuntStatuses($statuses);
    }

    /**
     * Disables a given list of shunts.
     *
     * @param array $names
     *   An indexed array of shunt names.
     */
    protected function disableShunts(array $names) {
      $statuses = array_fill_keys($names, FALSE);
      $this->setShuntStatuses($statuses);
    }

    /**
     * Sets the status of a given list of shunts.
     *
     * @param array $statuses
     *   An associative array of shunt statuses where each key is a shunt
     *   machine name and its value is the status to set the shunt to.
     */
    protected function setShuntStatuses(array $statuses) {
      foreach ($statuses as $name => $status) {
        // Set state values directly to avoid using Shunt commands to test Shunt
        // commands.
        $this->drush('state-set', array("shunt.{$name}", $status ? 1 : 0), array(), $this->site);
      }
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
      // Access state values directly to avoid using Shunt commands to test
      // Shunt commands.
      $this->drush('state-get', array("shunt.{$name}"), array(), $this->site);
      return $this->getOutput() === 'true';
    }

  }

}

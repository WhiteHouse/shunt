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
     * Tests the shunt-disable command.
     */
    public function testShuntDisableCommand() {
      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', array(), array(), $this->site);
      $this->assertStringStartsWith('There were no shunts that could be disabled.', $this->getErrorOutput());
      $this->assertShuntIsEnabled('shunt', 'No shunts disabled without "shunts" argument.');

      $this->drush('shunt-disable', array('invalid'), array(), $this->site);
      $this->assertStringStartsWith('No such shunt "invalid".', $this->getErrorOutput(), 'Warned about invalid "shunts" argument.');

      $this->drush('shunt-disable', array('shunt'), array('no' => NULL), $this->site);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be disabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertShuntIsEnabled('shunt', 'Shunt was not disabled with "no" option.');

      $this->drush('shunt-disable', array('shunt'), array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('Shunt "shunt" has been disabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be disabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsDisabled('shunt', 'Shunt was disabled with "yes" option.');

      $this->drush('shunt-disable', array('shunt'), array('no' => NULL), $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" is already disabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be disabled.', $error_output[1], 'Did not try to enable already disabled shunt.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', $this->allShunts, array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shuntexample', $this->getOutput());
      $this->assertTrue(!$this->shuntIsEnabled('shunt') && !$this->shuntIsEnabled('shuntexample'), 'Disabled multiple, explicitly named shunts.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', array(), array('all' => NULL, 'yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shuntexample', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" has been disabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt "shuntexample" has been disabled.', $error_output[1]);
      $this->assertTrue(!$this->shuntIsEnabled('shunt') && !$this->shuntIsEnabled('shuntexample'), 'Disabled all shunts with "all" option.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', array('*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded bare asterisk "shunts" argument.');
      $this->drush('shunt-disable', array('shunt*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and multiple matches.');
      $this->drush('shunt-disable', array('shuntex*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-enable command.
     */
    public function testShuntEnableCommand() {
      $this->drush('shunt-enable', array(), array(), $this->site);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $this->getErrorOutput());
      $this->assertShuntIsDisabled('shunt', 'No shunts enabled without "shunts" argument.');

      $this->drush('shunt-enable', array('invalid'), array(), $this->site);
      $this->assertStringStartsWith('No such shunt "invalid".', $this->getErrorOutput(), 'Warned about invalid "shunts" argument.');

      $this->drush('shunt-enable', array('shunt'), array('no' => NULL), $this->site);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertShuntIsDisabled('shunt', 'Shunt was not enabled with "no" option.');

      $this->drush('shunt-enable', array('shunt'), array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsEnabled('shunt', 'Shunt was enabled with "yes" option.');

      $this->drush('shunt-enable', array('shunt'), array('no' => NULL), $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" is already enabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $error_output[1], 'Did not try to enable already enabled shunt.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', $this->allShunts, array('yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled multiple, explicitly named shunts.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', array(), array('all' => NULL, 'yes' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt "shunt" has been enabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt "shuntexample" has been enabled.', $error_output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shuntexample'), 'Enabled all shunts with "all" option.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', array('*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded bare asterisk "shunts" argument.');
      $this->drush('shunt-enable', array('shunt*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and multiple matches.');
      $this->drush('shunt-enable', array('shuntex*'), array('no' => NULL), $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shuntexample', $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-info command.
     */
    public function testShuntInfoCommand() {
      $this->enableShunts(array('shunt'));

      $options = array('format' => 'json');

      $this->drush('shunt-info', array(), $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned all info without "shunts" argument.');

      $this->drush('shunt-info', array('invalid'), array(), $this->site);
      $this->assertStringStartsWith('No such shunt "invalid".', $this->getErrorOutput(), 'Warned about invalid "shunts" argument.');

      $this->drush('shunt-info', array('shunt'), $options, $this->site);
      $this->assertEquals($this->shuntInfo('shunt'), $this->getOutput(), 'Returned info for explicitly named shunt.');

      $this->drush('shunt-info', $this->allShunts, $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned info for multiple, explicitly named shunts.');

      $this->drush('shunt-info', array('*'), $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Correctly expanded bare asterisk "shunts" argument.');

      $this->drush('shunt-info', array('shunt*'), $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and multiple matches.');

      $this->drush('shunt-info', array('shuntex*'), $options, $this->site);
      $this->assertEquals($this->shuntInfo('shuntexample'), $this->getOutput(), 'Correctly expanded "shunts" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-list command.
     */
    public function testShuntListCommand() {
      $this->enableShunts(array('shunt'));

      $options = array('format' => 'json');

      $this->drush('shunt-list', array(), $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned all info without "status" option.');

      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'), $this->site);
      $this->assertEquals($this->shuntInfo('shunt'), $this->getOutput(), 'Filtered info to enabled shunts.');

      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'), $this->site);
      $this->assertEquals($this->shuntInfo('shuntexample'), $this->getOutput(), 'Filtered info to disabled shunts.');

      $this->drush('shunt-list', array(), $options + array('status' => 'invalid'), $this->site, NULL, self::EXIT_ERROR);
      $this->assertStringStartsWith('"invalid" is not a valid shunt status.', $this->getErrorOutput(), 'Erred on invalid "status" option.');

      $this->enableShunts(array('shuntexample'));
      $this->drush('shunt-list', array(), $options + array('status' => 'disabled'), $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to enabled shunts without any available.');

      $this->disableShunts($this->allShunts);
      $this->drush('shunt-list', array(), $options + array('status' => 'enabled'), $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to disabled shunts without any available.');
    }

    /**
     * Asserts that a given shunt is enabled.
     *
     * @param string $name
     *   The machine name of the shunt.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsEnabled($name, $message = '') {
      $this->assertTrue($this->shuntIsEnabled($name), $message);
    }

    /**
     * Asserts that a given shunt is disabled.
     *
     * @param string $name
     *   The machine name of the shunt.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsDisabled($name, $message = '') {
      $this->assertFalse($this->shuntIsEnabled($name), $message);
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
      return (bool) $this->getOutput();
    }

    /**
     * Returns a given subset of available shunt info, JSON-encoded.
     *
     * @param string|null $name
     *   The machine name of a shunt to whose info to limit the return set.
     *
     * @return string
     *   A pretty-printed, JSON-encoded array of shunt info.
     *
     * @throws \InvalidArgumentException
     *   Throws an exception if an invalid shunt name is given.
     */
    protected function shuntInfo($name = NULL) {
      if (!is_null($name) && (!is_string($name) || !in_array($name, $this->allShunts))) {
        throw new \InvalidArgumentException(sprintf('Invalid shunt name.'));
      }

      $info = array(
        'shunt' => array(
          'name' => 'shunt',
          'provider' => 'shunt',
          'description' => 'Default shunt. No built-in behavior.',
          'status' => 'Enabled',
        ),
        'shuntexample' => array(
          'name' => 'shuntexample',
          'provider' => 'shuntexample',
          'description' => 'Display a fail whale at /shuntexample.',
          'status' => 'Disabled',
        ),
      );

      // If a shunt name is provided, return only its subset of info,
      // maintaining the same data structure depth.
      if ($name) {
        return json_encode(array($name => $info[$name]), JSON_PRETTY_PRINT);
      }
      // Otherwise return all info.
      else {
        return json_encode($info, JSON_PRETTY_PRINT);
      }
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
        $this->drush('state-set', array("shunt.{$name}", $status ? 'true' : 0), array(), $this->site);
      }
    }

  }

}

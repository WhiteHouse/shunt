<?php

/**
 * @file
 * Contains \Unish\ShuntUnishTest.
 */

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

  /**
   * Unish tests for the Shunt module.
   *
   * @todo Add tests for command argument autocompletion?
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
    protected $allShunts = ['shunt', 'shunt_example'];

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
      $this->drush('pm-enable', ['shunt', 'shuntexample'], [
        'skip' => NULL,
        'yes' => NULL,
      ], $this->site);
    }

    /**
     * Tests the shunt-disable command.
     */
    public function testShuntDisableCommand() {
      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', [], [], $this->site);
      $this->assertStringStartsWith('There were no shunts that could be disabled.', $this->getErrorOutput());
      $this->assertShuntIsEnabled('shunt', 'No shunts disabled without "ids" argument.');

      $this->drush('shunt-disable', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-disable', ['shunt'], ['no' => NULL], $this->site);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be disabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertShuntIsEnabled('shunt', 'Shunt was not disabled with "no" option.');

      $this->drush('shunt-disable', ['shunt'], ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('Shunt shunt has been disabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be disabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsDisabled('shunt', 'Shunt was disabled with "yes" option.');

      $this->drush('shunt-disable', ['shunt'], ['no' => NULL], $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt is already disabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be disabled.', $error_output[1], 'Did not try to enable already disabled shunt.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', $this->allShunts, ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shunt_example', $this->getOutput());
      $this->assertTrue(!$this->shuntIsEnabled('shunt') && !$this->shuntIsEnabled('shunt_example'), 'Disabled multiple, explicitly named shunts.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', [], ['all' => NULL, 'yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shunt_example', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt has been disabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt shunt_example has been disabled.', $error_output[1]);
      $this->assertTrue(!$this->shuntIsEnabled('shunt') && !$this->shuntIsEnabled('shunt_example'), 'Disabled all shunts with "all" option.');

      $this->enableShunts($this->allShunts);

      $this->drush('shunt-disable', ['*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shunt_example', $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');
      $this->drush('shunt-disable', ['shunt*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt, shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');
      $this->drush('shunt-disable', ['shunt_ex*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be disabled: shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-enable command.
     */
    public function testShuntEnableCommand() {
      $this->drush('shunt-enable', [], [], $this->site);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $this->getErrorOutput());
      $this->assertShuntIsDisabled('shunt', 'No shunts enabled without "ids" argument.');

      $this->drush('shunt-enable', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-enable', ['shunt'], ['no' => NULL], $this->site);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertStringStartsWith('Aborting.', $this->getErrorOutput());
      $this->assertShuntIsDisabled('shunt', 'Shunt was not enabled with "no" option.');

      $this->drush('shunt-enable', ['shunt'], ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('Shunt shunt has been enabled.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be enabled: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsEnabled('shunt', 'Shunt was enabled with "yes" option.');

      $this->drush('shunt-enable', ['shunt'], ['no' => NULL], $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt is already enabled.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be enabled.', $error_output[1], 'Did not try to enable already enabled shunt.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', $this->allShunts, ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shunt_example', $this->getOutput());
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shunt_example'), 'Enabled multiple, explicitly named shunts.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', [], ['all' => NULL, 'yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shunt_example', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt has been enabled.', $error_output[0]);
      $this->assertStringStartsWith('Shunt shunt_example has been enabled.', $error_output[1]);
      $this->assertTrue($this->shuntIsEnabled('shunt') && $this->shuntIsEnabled('shunt_example'), 'Enabled all shunts with "all" option.');

      $this->disableShunts($this->allShunts);

      $this->drush('shunt-enable', ['*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shunt_example', $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');
      $this->drush('shunt-enable', ['shunt*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt, shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');
      $this->drush('shunt-enable', ['shunt_ex*'], ['no' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be enabled: shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-info command.
     */
    public function testShuntInfoCommand() {
      $this->enableShunts(['shunt']);

      $options = ['format' => 'json'];

      $this->drush('shunt-info', [], $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned all info without "ids" argument.');

      $this->drush('shunt-info', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-info', ['shunt'], $options, $this->site);
      $this->assertEquals($this->shuntInfo('shunt'), $this->getOutput(), 'Returned info for explicitly named shunt.');

      $this->drush('shunt-info', $this->allShunts, $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned info for multiple, explicitly named shunts.');

      $this->drush('shunt-info', ['*'], $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');

      $this->drush('shunt-info', ['shunt*'], $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');

      $this->drush('shunt-info', ['shunt_ex*'], $options, $this->site);
      $this->assertEquals($this->shuntInfo('shunt_example'), $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-list command.
     */
    public function testShuntListCommand() {
      $this->enableShunts(['shunt']);

      $options = ['format' => 'json'];

      $this->drush('shunt-list', [], $options, $this->site);
      $this->assertEquals($this->shuntInfo(), $this->getOutput(), 'Returned all info without "status" option.');

      $this->drush('shunt-list', [], $options + ['status' => 'enabled'], $this->site);
      $this->assertEquals($this->shuntInfo('shunt'), $this->getOutput(), 'Filtered info to enabled shunts.');

      $this->drush('shunt-list', [], $options + ['status' => 'disabled'], $this->site);
      $this->assertEquals($this->shuntInfo('shunt_example'), $this->getOutput(), 'Filtered info to disabled shunts.');

      $this->drush('shunt-list', [], $options + ['status' => 'invalid'], $this->site, NULL, self::EXIT_ERROR);
      $this->assertStringStartsWith('invalid is not a valid shunt status.', $this->getErrorOutput(), 'Erred on invalid "status" option.');

      $this->enableShunts(['shunt_example']);
      $this->drush('shunt-list', [], $options + ['status' => 'disabled'], $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to enabled shunts without any available.');

      $this->disableShunts($this->allShunts);
      $this->drush('shunt-list', [], $options + ['status' => 'enabled'], $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to disabled shunts without any available.');
    }

    /**
     * Asserts that a given shunt is enabled.
     *
     * @param string $id
     *   A shunt ID.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsEnabled($id, $message = '') {
      $this->assertTrue($this->shuntIsEnabled($id), $message);
    }

    /**
     * Asserts that a given shunt is disabled.
     *
     * @param string $id
     *   A shunt ID.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsDisabled($id, $message = '') {
      $this->assertFalse($this->shuntIsEnabled($id), $message);
    }

    /**
     * Determines whether a given shunt is enabled or not.
     *
     * @param string $id
     *   The shunt ID.
     *
     * @return bool
     *   Returns TRUE if the shunt is enabled or FALSE if it is disabled.
     */
    protected function shuntIsEnabled($id) {
      // Access state values directly to avoid using Shunt commands to test
      // Shunt commands.
      $this->drush('state-get', ["shunt.{$id}"], [], $this->site);
      return (bool) $this->getOutput();
    }

    /**
     * Returns a given subset of available shunt info, JSON-encoded.
     *
     * @param string|null $id
     *   The ID of a shunt to whose info to limit the return set.
     *
     * @return string
     *   A pretty-printed, JSON-encoded array of shunt info.
     *
     * @throws \InvalidArgumentException
     *   Throws an exception if an invalid shunt ID is given.
     */
    protected function shuntInfo($id = NULL) {
      if (!is_null($id) && (!is_string($id) || !in_array($id, $this->allShunts))) {
        throw new \InvalidArgumentException(sprintf('Invalid shunt ID.'));
      }

      $info = [
        'shunt' => [
          'id' => 'shunt',
          'description' => 'Default shunt. No built-in behavior.',
          'status' => 'Enabled',
        ],
        'shunt_example' => [
          'id' => 'shunt_example',
          'description' => 'Display a fail whale at /shuntexample.',
          'status' => 'Disabled',
        ],
      ];

      // If a shunt ID is provided, return only its subset of info, maintaining
      // the same data structure depth.
      if ($id) {
        return json_encode([$id => $info[$id]], JSON_PRETTY_PRINT);
      }
      // Otherwise return all info.
      else {
        return json_encode($info, JSON_PRETTY_PRINT);
      }
    }

    /**
     * Enables a given list of shunts.
     *
     * @param array $ids
     *   An indexed array of shunt IDs.
     */
    protected function enableShunts(array $ids) {
      $statuses = array_fill_keys($ids, TRUE);
      $this->setShuntStatuses($statuses);
    }

    /**
     * Disables a given list of shunts.
     *
     * @param array $ids
     *   An indexed array of shunt IDs.
     */
    protected function disableShunts(array $ids) {
      $statuses = array_fill_keys($ids, FALSE);
      $this->setShuntStatuses($statuses);
    }

    /**
     * Sets the status of a given list of shunts.
     *
     * @param array $statuses
     *   An associative array of shunt statuses where each key is a shunt ID and
     *   its value is the status to set the shunt to.
     */
    protected function setShuntStatuses(array $statuses) {
      foreach ($statuses as $id => $status) {
        // Set state values directly to avoid using Shunt commands to test Shunt
        // commands.
        $this->drush('state-set', ["shunt.{$id}", $status ? 'true' : 0], [], $this->site);
      }
    }

  }

}

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
     * All available shunt IDs.
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
     * Tests the shunt-reset command.
     */
    public function testShuntResetCommand() {
      $this->tripShunts($this->allShunts);

      $this->drush('shunt-reset', [], [], $this->site);
      $this->assertStringStartsWith('There were no shunts that could be reset.', $this->getErrorOutput());
      $this->assertShuntIsTripped('shunt', 'No shunts reset without "ids" argument.');

      $this->drush('shunt-reset', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-reset', ['shunt'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be reset: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertShuntIsTripped('shunt', 'Shunt was not reset with "no" option.');

      $this->drush('shunt-reset', ['shunt'], ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('Shunt shunt has been reset.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be reset: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsNotTripped('shunt', 'Shunt was reset with "yes" option.');

      $this->drush('shunt-reset', ['shunt'], ['no' => NULL], $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt is not tripped.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be reset.', $error_output[1], 'Did not try to reset already shunt that was not tripped.');

      $this->tripShunts($this->allShunts);

      $this->drush('shunt-reset', $this->allShunts, ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be reset: shunt, shunt_example', $this->getOutput());
      $this->assertTrue(!$this->shuntIsTripped('shunt') && !$this->shuntIsTripped('shunt_example'), 'Reset multiple, explicitly named shunts.');

      $this->tripShunts($this->allShunts);

      $this->drush('shunt-reset', [], ['all' => NULL, 'yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be reset: shunt, shunt_example', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt has been reset.', $error_output[0]);
      $this->assertStringStartsWith('Shunt shunt_example has been reset.', $error_output[1]);
      $this->assertTrue(!$this->shuntIsTripped('shunt') && !$this->shuntIsTripped('shunt_example'), 'Reset all shunts with "all" option.');

      $this->tripShunts($this->allShunts);

      $this->drush('shunt-reset', ['*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be reset: shunt, shunt_example', $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');
      $this->drush('shunt-reset', ['shunt*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be reset: shunt, shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');
      $this->drush('shunt-reset', ['shunt_ex*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be reset: shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-trip command.
     */
    public function testShuntTripCommand() {
      $this->drush('shunt-trip', [], [], $this->site);
      $this->assertStringStartsWith('There were no shunts that could be tripped.', $this->getErrorOutput());
      $this->assertShuntIsNotTripped('shunt', 'No shunts tripped without "ids" argument.');

      $this->drush('shunt-trip', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-trip', ['shunt'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be tripped: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): n', $output[1]);
      $this->assertShuntIsNotTripped('shunt', 'Shunt was not tripped with "no" option.');

      $this->drush('shunt-trip', ['shunt'], ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('Shunt shunt has been tripped.', $this->getErrorOutput());
      $output = $this->getOutputAsList();
      $this->assertEquals('The following shunts will be tripped: shunt', $output[0]);
      $this->assertEquals('Do you want to continue? (y/n): y', $output[1]);
      $this->assertShuntIsTripped('shunt', 'Shunt was tripped with "yes" option.');

      $this->drush('shunt-trip', ['shunt'], ['no' => NULL], $this->site);
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt is already tripped.', $error_output[0]);
      $this->assertStringStartsWith('There were no shunts that could be tripped.', $error_output[1], 'Did not try to trip already tripped shunt.');

      $this->resetShunts($this->allShunts);

      $this->drush('shunt-trip', $this->allShunts, ['yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be tripped: shunt, shunt_example', $this->getOutput());
      $this->assertTrue($this->shuntIsTripped('shunt') && $this->shuntIsTripped('shunt_example'), 'Tripped multiple, explicitly named shunts.');

      $this->resetShunts($this->allShunts);

      $this->drush('shunt-trip', [], ['all' => NULL, 'yes' => NULL], $this->site);
      $this->assertStringStartsWith('The following shunts will be tripped: shunt, shunt_example', $this->getOutput());
      $error_output = $this->getErrorOutputAsList();
      $this->assertStringStartsWith('Shunt shunt has been tripped.', $error_output[0]);
      $this->assertStringStartsWith('Shunt shunt_example has been tripped.', $error_output[1]);
      $this->assertTrue($this->shuntIsTripped('shunt') && $this->shuntIsTripped('shunt_example'), 'Tripped all shunts with "all" option.');

      $this->resetShunts($this->allShunts);

      $this->drush('shunt-trip', ['*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be tripped: shunt, shunt_example', $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');
      $this->drush('shunt-trip', ['shunt*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be tripped: shunt, shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');
      $this->drush('shunt-trip', ['shunt_ex*'], ['no' => NULL], $this->site, NULL, self::UNISH_EXITCODE_USER_ABORT);
      $this->assertStringStartsWith('The following shunts will be tripped: shunt_example', $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-info command.
     */
    public function testShuntInfoCommand() {
      $this->tripShunts(['shunt']);

      $options = ['format' => 'json'];

      $this->drush('shunt-info', [], $options, $this->site);
      $this->assertEquals($this->infoOutput(), $this->getOutput(), 'Returned all info without "ids" argument.');

      $this->drush('shunt-info', ['invalid'], [], $this->site);
      $this->assertStringStartsWith('No such shunt invalid.', $this->getErrorOutput(), 'Warned about invalid "ids" argument.');

      $this->drush('shunt-info', ['shunt'], $options, $this->site);
      $this->assertEquals($this->infoOutput('shunt'), $this->getOutput(), 'Returned info for explicitly named shunt.');

      $this->drush('shunt-info', $this->allShunts, $options, $this->site);
      $this->assertEquals($this->infoOutput(), $this->getOutput(), 'Returned info for multiple, explicitly named shunts.');

      $this->drush('shunt-info', ['*'], $options, $this->site);
      $this->assertEquals($this->infoOutput(), $this->getOutput(), 'Correctly expanded bare asterisk "ids" argument.');

      $this->drush('shunt-info', ['shunt*'], $options, $this->site);
      $this->assertEquals($this->infoOutput(), $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and multiple matches.');

      $this->drush('shunt-info', ['shunt_ex*'], $options, $this->site);
      $this->assertEquals($this->infoOutput('shunt_example'), $this->getOutput(), 'Correctly expanded "ids" argument with trailing slash and single match.');
    }

    /**
     * Tests the shunt-list command.
     */
    public function testShuntListCommand() {
      $this->tripShunts(['shunt']);

      $options = ['format' => 'json'];

      $this->drush('shunt-list', [], $options, $this->site);
      $this->assertEquals($this->listOutput(), $this->getOutput(), 'Returned all info without "status" option.');

      $this->drush('shunt-list', [], $options + ['status' => 'tripped'], $this->site);
      $this->assertEquals($this->listOutput('shunt'), $this->getOutput(), 'Filtered info to tripped shunts.');

      $this->drush('shunt-list', [], $options + ['status' => 'not tripped'], $this->site);
      $this->assertEquals($this->listOutput('shunt_example'), $this->getOutput(), 'Filtered info to shunts that are not tripped.');

      $this->drush('shunt-list', [], $options + ['status' => 'tripped,not tripped'], $this->site);
      $this->assertEquals($this->listOutput(), $this->getOutput(), 'Returned all info with both "status" option values given.');

      $this->drush('shunt-list', [], $options + ['status' => 'invalid'], $this->site, NULL, self::EXIT_ERROR);
      $this->assertStringStartsWith('invalid is not a valid shunt status.', $this->getErrorOutput(), 'Erred on invalid "status" option.');

      $this->tripShunts(['shunt_example']);
      $this->drush('shunt-list', [], $options + ['status' => 'not tripped'], $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to shunts that are not tripped without any available.');

      $this->resetShunts($this->allShunts);
      $this->drush('shunt-list', [], $options + ['status' => 'tripped'], $this->site);
      $this->assertEquals('', $this->getOutput(), 'Returned empty when filtered to tripped shunts without any available.');
    }

    /**
     * Asserts that a given shunt is tripped.
     *
     * @param string $id
     *   A shunt ID.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsTripped($id, $message = '') {
      $this->assertTrue($this->shuntIsTripped($id), $message);
    }

    /**
     * Asserts that a given shunt is not tripped.
     *
     * @param string $id
     *   A shunt ID.
     * @param string $message
     *   The assertion message.
     */
    public function assertShuntIsNotTripped($id, $message = '') {
      $this->assertFalse($this->shuntIsTripped($id), $message);
    }

    /**
     * Determines whether a given shunt is tripped or not.
     *
     * @param string $id
     *   The shunt ID.
     *
     * @return bool
     *   Returns TRUE if the shunt is tripped or FALSE if not.
     */
    protected function shuntIsTripped($id) {
      // Access state values directly to avoid using Shunt commands to test
      // Shunt commands.
      $this->drush('state-get', ["shunt.{$id}"], [], $this->site);
      return (bool) $this->getOutput();
    }

    /**
     * Returns the expected output of the shunt-info command, JSON-encoded.
     *
     * @param string|null $id
     *   The ID of a shunt to whose info to limit the return set.
     *
     * @return string
     *   A pretty-printed, JSON-encoded array of expected command output.
     */
    protected function infoOutput($id = NULL) {
      $info = [
        'shunt' => [
          'id' => 'shunt',
          'label' => 'Shunt',
          'description' => 'Default shunt. No built-in behavior.',
          'status' => 'Tripped',
        ],
        'shunt_example' => [
          'id' => 'shunt_example',
          'label' => 'Shunt example',
          'description' => 'Display a fail whale at /shuntexample.',
          'status' => 'Not tripped',
        ],
      ];

      return $this->filterAndEncode($info, $id);
    }

    /**
     * Returns the expected output of the shunt-list command, JSON-encoded.
     *
     * @param string|null $id
     *   The ID of a shunt to whose info to limit the return set.
     *
     * @return string
     *   A pretty-printed, JSON-encoded array of expected command output.
     */
    protected function listOutput($id = NULL) {
      $info = [
        'shunt' => [
          'name' => 'Shunt (shunt)',
          'description' => 'Default shunt. No built-in behavior.',
          'status' => 'Tripped',
        ],
        'shunt_example' => [
          'name' => 'Shunt example (shunt_example)',
          'description' => 'Display a fail whale at /shuntexample.',
          'status' => 'Not tripped',
        ],
      ];

      return $this->filterAndEncode($info, $id);
    }

    /**
     * Optionally filters an array of data and JSON encodes it.
     *
     * @param array $data
     *   An associative array of data.
     * @param string|null $key
     *   The key of an element to limit the return set to.
     *
     * @return string
     *   A pretty-printed, JSON-encoded array of shunt info.
     */
    protected function filterAndEncode(array $data, $key) {
      // If a shunt ID is provided, return only its subset of data, maintaining
      // the same data structure depth.
      if ($key) {
        return json_encode([$key => $data[$key]], JSON_PRETTY_PRINT);
      }
      // Otherwise return all data.
      else {
        return json_encode($data, JSON_PRETTY_PRINT);
      }
    }

    /**
     * Trips a given list of shunts.
     *
     * @param array $ids
     *   An indexed array of shunt IDs.
     */
    protected function tripShunts(array $ids) {
      $statuses = array_fill_keys($ids, TRUE);
      $this->setShuntStatuses($statuses);
    }

    /**
     * Resets a given list of shunts.
     *
     * @param array $ids
     *   An indexed array of shunt IDs.
     */
    protected function resetShunts(array $ids) {
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

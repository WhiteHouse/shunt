<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntUiTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests the Shunt web UI.
 *
 * @group shunt
 */
class ShuntUiTest extends WebTestBase {

  const CONFIG_FORM_PATH = 'admin/config/development/shunts';

  /**
   * {@inheritdoc}
   */
  public static $modules = array('shunt', 'shuntexample');

  /**
   * The shunt manager.
   *
   * @var \Drupal\shunt\ShuntManager
   */
  protected $shuntManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->shuntManager = \Drupal::service('plugin.manager.shunt');

    $privileged_user = $this->drupalCreateUser(array('administer shunts'));
    $this->drupalLogin($privileged_user);
  }

  /**
   * Asserts that a shunt has a given status.
   *
   * @param string $name
   *   The shunt name.
   * @param bool $status
   *   The shunt status--TRUE for enabled or FALSE for disabled.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use format_string() to embed variables in the message text, not
   *   t(). If left blank, a default message will be displayed.
   */
  protected function assertShuntStatus($name, $status, $message = '') {
    $actual = $this->shuntManager->shuntIsEnabled($name);
    $expected = (bool) $status;
    $this->assertIdentical($actual, $expected, $message);
  }

  /**
   * Tests config form access.
   */
  public function testConfigFormAccess() {
    $this->drupalGet($this::CONFIG_FORM_PATH);
    $this->assertResponse(200, 'Granted access to config form to privileged user.');

    $this->drupalLogout();
    $this->drupalGet($this::CONFIG_FORM_PATH);
    $this->assertResponse(403, 'Denied access to config form non-privileged user.');
  }

  /**
   * Tests making shunt status changes through the config form.
   */
  public function testConfigFormStatusChanges() {
    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was disabled by default.');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was disabled by default.');

    $edit_enable_shunt['shunts[shunt]'] = 'shunt';
    $edit_enable_shunt['shunts[shuntexample]'] = FALSE;
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_enable_shunt, t('Save configuration'));
    $this->assertShuntStatus('shunt', TRUE, 'Shunt "shunt" was enabled.');
    $this->assertText(t('Shunt "shunt" has been enabled.'), 'Displayed message for enabled shunt');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was unchanged.');
    $this->assertNoText(t('Shunt "shuntexample" has been enabled.'), 'Did not display message for unchanged shunt');

    $edit_enable_shuntexample['shunts[shunt]'] = FALSE;
    $edit_enable_shuntexample['shunts[shuntexample]'] = 'shuntexample';
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_enable_shuntexample, t('Save configuration'));
    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was disabled.');
    $this->assertText(t('Shunt "shunt" has been disabled.'), 'Displayed message for disabled shunt');
    $this->assertShuntStatus('shuntexample', TRUE, 'Shunt "shuntexample" was enabled.');
    $this->assertText(t('Shunt "shuntexample" has been enabled.'), 'Displayed message for enabled shunt');

    $edit_disable_both['shunts[shunt]'] = FALSE;
    $edit_disable_both['shunts[shuntexample]'] = FALSE;
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_disable_both, t('Save configuration'));
    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was unchanged.');
    $this->assertNoText(t('Shunt "shunt" has been disabled.'), 'Did not display message for unchanged shunt');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was disabled.');
    $this->assertText(t('Shunt "shuntexample" has been disabled.'), 'Displayed message for disabled shunt');
  }

}

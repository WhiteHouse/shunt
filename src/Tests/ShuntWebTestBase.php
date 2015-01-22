<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntWebTestBase.
 */

namespace Drupal\shunt\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides common functionality for shunt web tests.
 */
abstract class ShuntWebTestBase extends WebTestBase {

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

}

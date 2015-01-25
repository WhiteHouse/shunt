<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntWebTestBase.
 */

namespace Drupal\shunt\Tests;

use Drupal\shunt\Entity\Shunt;
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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $privileged_user = $this->drupalCreateUser(array('administer shunts'));
    $this->drupalLogin($privileged_user);
  }

  /**
   * Asserts that a shunt has a given status.
   *
   * @param string $id
   *   The shunt ID.
   * @param bool $status
   *   The shunt status--TRUE for enabled or FALSE for disabled.
   * @param string $message
   *   (optional) A message to display with the assertion. Do not translate
   *   messages: use format_string() to embed variables in the message text, not
   *   t(). If left blank, a default message will be displayed.
   */
  protected function assertShuntStatus($id, $status, $message = '') {
    $actual = Shunt::load($id)->isShuntEnabled();
    $expected = (bool) $status;
    $this->assertIdentical($actual, $expected, $message);
  }

}

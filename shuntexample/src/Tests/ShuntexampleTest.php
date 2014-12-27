<?php

/**
 * @file
 * Contains \Drupal\shuntexample\Tests\ShuntexampleTest.
 */

namespace Drupal\shuntexample\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests Shunt Example module.
 */
class ShuntexampleTest extends WebTestBase {

  const PAGE_PATH = 'shuntexample';

  const SHUNT_NAME = 'shuntexample';

  /**
   * {@inheritdoc}
   */
  public static $modules = array('shunt', 'shuntexample');

  /**
   * A user object with permission to administer shunts.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $privilegedUser;

  /**
   * The shunt manager.
   *
   * @var \Drupal\shunt\ShuntManager
   */
  protected $shuntManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'Shunt Example',
      'description' => 'Tests the Shunt Example module.',
      'group' => 'Shunt',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->shuntManager = \Drupal::service('plugin.manager.shunt');

    user_role_change_permissions(DRUPAL_ANONYMOUS_RID, array('access content' => TRUE));
  }

  /**
   * Tests the Shunt Example page.
   */
  public function testShuntExamplePage() {
    $this->drupalGet($this::PAGE_PATH);
    $this->assertTitle('Hello world! | Drupal', 'Displayed default page title.');
    $this->assertText('Enable the "shuntexample" shunt to make this page fail gracefully.', 'Displayed default page content.');

    $this->shuntManager->enableShunt($this::SHUNT_NAME);
    $this->drupalGet($this::PAGE_PATH);
    $this->assertTitle('Fail whale! | Drupal', 'Changed page title based on shunt status.');
    $this->assertText('Disable the "shuntexample" shunt to restore this page.', 'Changed page content based on shunt status.');
  }

}

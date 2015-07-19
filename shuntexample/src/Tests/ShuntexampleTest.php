<?php

/**
 * @file
 * Contains \Drupal\shuntexample\Tests\ShuntexampleTest.
 */

namespace Drupal\shuntexample\Tests;

use Drupal\shunt\Entity\Shunt;
use Drupal\simpletest\WebTestBase;

/**
 * Tests Shunt Example module.
 *
 * @group shuntexample
 */
class ShuntexampleTest extends WebTestBase {

  /**
   * The Shunt Example page path.
   */
  const PAGE_PATH = 'shuntexample';

  /**
   * The Shunt Example shunt ID.
   */
  const SHUNT_ID = 'shunt_example';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['shunt', 'shuntexample'];

  /**
   * A user object with permission to administer shunts.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $privilegedUser;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => 'Shunt Example',
      'description' => 'Tests the Shunt Example module.',
      'group' => 'Shunt',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    user_role_change_permissions(DRUPAL_ANONYMOUS_RID, ['access content' => TRUE]);
  }

  /**
   * Tests the Shunt Example page.
   */
  public function testShuntExamplePage() {
    $this->drupalGet($this::PAGE_PATH);
    $this->assertTitle('Hello world! | Drupal', 'Displayed default page title.');
    $this->assertText('Trip the "shunt_example" shunt to make this page fail gracefully.', 'Displayed default page content.');

    Shunt::load($this::SHUNT_ID)->trip();
    // The following tests are finicky without a cache clear. They succeed via
    // core/scripts/run-tests.sh but fail via the web UI.
    drupal_flush_all_caches();
    $this->drupalGet($this::PAGE_PATH);
    $this->assertTitle('Fail whale! | Drupal', 'Changed page title based on shunt status.');
    $this->assertText('Reset the "shunt_example" shunt to restore this page.', 'Changed page content based on shunt status.');
  }

}

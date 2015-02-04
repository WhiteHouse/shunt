<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntDeleteFormTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\shunt\Entity\Shunt;

/**
 * Tests the shunt entity.
 *
 * @group shunt
 */
class ShuntTest extends ShuntWebTestBase {

  /**
   * Tests the form.
   */
  public function testForm() {
    $state = \Drupal::state();
    /** @var \Drupal\shunt\Entity\Shunt $shunt */
    $shunt = Shunt::load('shunt');

    $shunt->trip();

    $shunt->delete();
    $this->assertNull($state->get('shunt.shunt'), "Shunt's state was deleted with it.");
  }

}

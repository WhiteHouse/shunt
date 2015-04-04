<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntDeleteFormTest.
 */

namespace Drupal\shunt\Tests;

/**
 * Tests the delete shunt form.
 *
 * @group shunt
 */
class ShuntDeleteFormTest extends ShuntWebTestBase {

  /**
   * Tests the form.
   */
  public function testForm() {
    $base_path = self::CONFIG_PAGE_PATH;

    $this->drupalGet("{$base_path}/invalid/delete");
    $this->assertResponse(404, 'Erred when given invalid shunt ID.');

    $this->drupalGet("{$base_path}/shunt_example/delete");
    $this->assertResponse(403, 'Erred when given a protected shunt ID.');

    $this->drupalGet("{$base_path}/shunt/delete");
    $this->assertResponse(200, 'Granted access to delete form to privileged user.');
    $this->assertTitle('Are you sure you want to delete the shunt shunt? | Drupal');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Delete', 'Correctly labeled submit button "Delete".');
    $delete_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}",
      ':text' => 'Cancel',
    ]);
    $this->assertTrue($delete_link, 'Displayed "Cancel" link.');

    $this->drupalLogout();
    $this->drupalGet("{$base_path}/shunt/edit");
    $this->assertResponse(403, 'Denied access to edit form to non-privileged user.');
  }

}

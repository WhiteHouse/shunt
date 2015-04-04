<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntFormTest.
 */

namespace Drupal\shunt\Tests;

/**
 * Tests the shunt form.
 *
 * @group shunt
 */
class ShuntFormTest extends ShuntWebTestBase {

  /**
   * Tests the form.
   */
  public function testForm() {
    $base_path = self::CONFIG_PAGE_PATH;

    $this->drupalGet("{$base_path}/invalid/edit");
    $this->assertResponse(404, 'Erred when given invalid shunt ID.');

    $this->drupalGet("{$base_path}/shunt/edit");
    $this->assertResponse(200, 'Granted access to edit form to privileged user.');
    $this->assertTitle('Edit shunt shunt | Drupal');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Save', 'Correctly labeled submit button "Save".');
    $delete_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}/shunt/delete",
      ':text' => 'Delete',
    ]);
    $this->assertTrue($delete_link, 'Displayed "Delete" link for unprotected shunt.');

    $this->drupalGet("{$base_path}/shunt_example/edit");
    $delete_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}/shunt_example/delete",
      ':text' => 'Delete',
    ]);
    $this->assertTrue(empty($delete_link), 'Did not display "Delete" link for protected shunt.');

    $this->drupalLogout();
    $this->drupalGet("{$base_path}/shunt/edit");
    $this->assertResponse(403, 'Denied access to edit form to non-privileged user.');
  }

}

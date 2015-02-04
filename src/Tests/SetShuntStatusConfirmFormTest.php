<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\SetShuntStatusConfirmFormTest.
 */

namespace Drupal\shunt\Tests;

/**
 * Tests the set shunt status confirm form.
 *
 * @group shunt
 */
class SetShuntStatusConfirmFormTest extends ShuntWebTestBase {

  /**
   * Tests the form.
   */
  public function testForm() {
    $base_path = self::CONFIG_PAGE_PATH;

    $this->assertShuntStatus('shunt', FALSE, 'Shunt not tripped by default.');

    $this->drupalGet("{$base_path}/invalid/trip");
    $this->assertResponse(404, 'Erred when given invalid shunt ID.');

    $this->drupalGet("{$base_path}/shunt/invalid");
    $this->assertResponse(404, 'Erred when given invalid action.');

    $this->drupalGet("{$base_path}/shunt/trip", [
      'query' => ['destination' => "{$base_path}/shunt/trip"],
    ]);
    $this->assertResponse(200, 'Granted access to trip form to privileged user.');
    $this->assertTitle('Are you sure you want to trip the shunt shunt? | Drupal');
    $this->assertText('Default shunt. No built-in behavior.', 'Displayed shunt description as confirmation text.');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Trip', 'Correctly labeled submit button "Trip".');
    $cancel_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}/shunt/trip",
      ':text' => 'Cancel',
    ]);
    $this->assertTrue($cancel_link, 'Displayed "Cancel" link pointing to given destination argument.');

    $this->drupalPostForm(NULL, [], t('Trip'));
    $this->assertUrl("{$base_path}/shunt/trip", [], 'Redirected to given destination.');
    $this->assertText('Shunt shunt has been tripped.', 'Displayed the "tripped" notice.');
    $cancel_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}",
      ':text' => 'Cancel',
    ]);
    $this->assertTrue($cancel_link, 'Displayed "Cancel" link pointing to config form.');
    $this->assertShuntStatus('shunt', TRUE, 'Tripped shunt.');

    $this->drupalPostForm(NULL, [], t('Trip'));
    $this->assertUrl($base_path, [], 'Redirected to config form.');
    $this->assertText('Shunt shunt is already tripped.', 'Displayed "already tripped" warning.');
    $this->assertShuntStatus('shunt', TRUE, 'Did not change shunt status.');

    $this->drupalGet("{$base_path}/shunt/reset", [
      'query' => ['destination' => "{$base_path}/shunt/reset"],
    ]);
    $this->assertResponse(200, 'Granted access to reset form to privileged user.');
    $this->assertTitle('Are you sure you want to reset the shunt shunt? | Drupal');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Reset', 'Correctly labeled submit button "Reset".');

    $this->drupalPostForm(NULL, [], t('Reset'));
    $this->assertUrl("{$base_path}/shunt/reset", [], 'Redirected to given destination.');
    $this->assertText('Shunt shunt has been reset.', 'Displayed the "reset" notice.');
    $this->assertShuntStatus('shunt', FALSE, 'Reset shunt.');

    $this->drupalPostForm(NULL, [], t('Reset'));
    $this->assertText('Shunt shunt is not tripped.', 'Displayed "not tripped" warning.');
    $this->assertUrl($base_path, [], 'Redirected to config form.');
    $this->assertShuntStatus('shunt', FALSE, 'Did not change shunt status.');

    $this->drupalLogout();
    $this->drupalGet("{$base_path}/shunt/trip");
    $this->assertResponse(403, 'Denied access to trip form to non-privileged user.');
    $this->drupalGet("{$base_path}/shunt/reset");
    $this->assertResponse(403, 'Denied access to reset form to non-privileged user.');
  }

}

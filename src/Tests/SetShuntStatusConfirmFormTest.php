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

    $this->assertShuntStatus('shunt', FALSE, 'Shunt disabled by default.');

    $this->drupalGet("{$base_path}/invalid/enable");
    $this->assertResponse(404, 'Erred when given invalid shunt ID.');

    $this->drupalGet("{$base_path}/shunt/invalid");
    $this->assertResponse(404, 'Erred when given invalid action.');

    $this->drupalGet("{$base_path}/shunt/enable", [
      'query' => ['destination' => "{$base_path}/shunt/enable"],
    ]);
    $this->assertResponse(200, 'Granted access to enable form to privileged user.');
    $this->assertTitle('Are you sure you want to enable the shunt shunt? | Drupal');
    $this->assertText('Default shunt. No built-in behavior.', 'Displayed shunt description as confirmation text.');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Enable', 'Correctly labeled submit button "Enable".');
    $cancel_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => '/admin/config/development/shunts/shunt/enable',
      ':text' => 'Cancel',
    ]);
    $this->assertTrue($cancel_link, 'Displayed "Cancel" link pointing to given destination argument.');

    $this->drupalPostForm(NULL, [], t('Enable'));
    $this->assertUrl("{$base_path}/shunt/enable", [], 'Redirected to given destination.');
    $this->assertText('Shunt shunt has been enabled.', 'Displayed the "enabled" notice.');
    $cancel_link = $this->xpath('//div[@id=:id]/a[@href=:href][text()=:text]', [
      ':id' => 'edit-actions',
      ':href' => "/{$base_path}",
      ':text' => 'Cancel',
    ]);
    $this->assertTrue($cancel_link, 'Displayed "Cancel" link pointing to config form.');
    $this->assertShuntStatus('shunt', TRUE, 'Enabled shunt.');

    $this->drupalPostForm(NULL, [], t('Enable'));
    $this->assertUrl($base_path, [], 'Redirected to config form.');
    $this->assertText('Shunt shunt is already enabled.', 'Displayed "already enabled" warning.');
    $this->assertShuntStatus('shunt', TRUE, 'Did not change shunt status.');

    $this->drupalGet("{$base_path}/shunt/disable", [
      'query' => ['destination' => "{$base_path}/shunt/disable"],
    ]);
    $this->assertResponse(200, 'Granted access to disable form to privileged user.');
    $this->assertTitle('Are you sure you want to disable the shunt shunt? | Drupal');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Disable', 'Correctly labeled submit button "Disable".');

    $this->drupalPostForm(NULL, [], t('Disable'));
    $this->assertUrl("{$base_path}/shunt/disable", [], 'Redirected to given destination.');
    $this->assertText('Shunt shunt has been disabled.', 'Displayed the "disabled" notice.');
    $this->assertShuntStatus('shunt', FALSE, 'Disabled shunt.');

    $this->drupalPostForm(NULL, [], t('Disable'));
    $this->assertText('Shunt shunt is already disabled.', 'Displayed "already disabled" warning.');
    $this->assertUrl($base_path, [], 'Redirected to config form.');
    $this->assertShuntStatus('shunt', FALSE, 'Did not change shunt status.');

    $this->drupalLogout();
    $this->drupalGet("{$base_path}/shunt/enable");
    $this->assertResponse(403, 'Denied access to enable form to non-privileged user.');
    $this->drupalGet("{$base_path}/shunt/disable");
    $this->assertResponse(403, 'Denied access to disable form to non-privileged user.');
  }

}

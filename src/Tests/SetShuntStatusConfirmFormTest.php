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
    $config_form_path = 'admin/config/development/shunts';

    $this->assertShuntStatus('shunt', FALSE, 'Shunt disabled by default.');

    $this->drupalGet("{$config_form_path}/invalid/enable");
    $this->assertResponse(404, 'Erred when given invalid shunt name.');

    $this->drupalGet("{$config_form_path}/shunt/invalid");
    $this->assertResponse(404, 'Erred when given invalid action.');

    $this->drupalGet("{$config_form_path}/shunt/enable", array(
      'query' => array('destination' => "{$config_form_path}/shunt/enable"),
    ));
    $this->assertResponse(200, 'Granted access to enable form to privileged user.');
    $this->assertTitle('Are you sure you want to enable the "shunt" shunt? | Drupal');
    $this->assertText('Default shunt. No built-in behavior.', 'Displayed shunt description as confirmation text.');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Enable', 'Correctly labeled submit button "Enable".');
    // @todo Tighten up this test as it will currently find any link on the
    //   page with the correct HREF rather than the "Cancel" link specifically.
    $this->assertLinkByHref("{$config_form_path}/shunt/enable", 0, 'Displayed "Cancel" link pointing to given destination argument.');

    $this->drupalPostForm(NULL, array(), t('Enable'));
    $this->assertUrl("{$config_form_path}/shunt/enable", array(), 'Redirected to given destination.');
    $this->assertText('Shunt "shunt" has been enabled.', 'Displayed the "enabled" notice.');
    // @todo Tighten up this test as it will currently find any link on the
    //   page with the correct HREF rather than the "Cancel" link specifically.
    $this->assertLinkByHref($config_form_path, 0, 'Displayed "Cancel" link pointing to config form.');
    $this->assertShuntStatus('shunt', TRUE, 'Enabled shunt.');

    $this->drupalPostForm(NULL, array(), t('Enable'));
    $this->assertUrl($config_form_path, array(), 'Redirected to config form.');
    $this->assertText('Shunt "shunt" is already enabled.', 'Displayed "already enabled" warning.');
    $this->assertShuntStatus('shunt', TRUE, 'Did not change shunt status.');

    $this->drupalGet("{$config_form_path}/shunt/disable", array(
      'query' => array('destination' => "{$config_form_path}/shunt/disable"),
    ));
    $this->assertResponse(200, 'Granted access to disable form to privileged user.');
    $this->assertTitle('Are you sure you want to disable the "shunt" shunt? | Drupal');
    $this->assertFieldByXPath('//input[@id="edit-submit"]//@value', 'Disable', 'Correctly labeled submit button "Disable".');

    $this->drupalPostForm(NULL, array(), t('Disable'));
    $this->assertUrl("{$config_form_path}/shunt/disable", array(), 'Redirected to given destination.');
    $this->assertText('Shunt "shunt" has been disabled.', 'Displayed the "disabled" notice.');
    $this->assertShuntStatus('shunt', FALSE, 'Disabled shunt.');

    $this->drupalPostForm(NULL, array(), t('Disable'));
    $this->assertText('Shunt "shunt" is already disabled.', 'Displayed "already disabled" warning.');
    $this->assertUrl($config_form_path, array(), 'Redirected to config form.');
    $this->assertShuntStatus('shunt', FALSE, 'Did not change shunt status.');

    $this->drupalLogout();
    $this->drupalGet("{$config_form_path}/shunt/enable");
    $this->assertResponse(403, 'Denied access to enable form to non-privileged user.');
    $this->drupalGet("{$config_form_path}/shunt/disable");
    $this->assertResponse(403, 'Denied access to disable form to non-privileged user.');
  }

}

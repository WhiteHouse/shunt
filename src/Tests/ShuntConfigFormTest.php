<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntConfigFormTest.
 */

namespace Drupal\shunt\Tests;

/**
 * Tests the Shunt config form.
 *
 * @group shunt
 */
class ShuntConfigFormTest extends ShuntWebTestBase {

  const CONFIG_FORM_PATH = 'admin/config/development/shunts';

  /**
   * Tests the config form.
   */
  public function testConfigFormStatusChanges() {
    $this->drupalGet($this::CONFIG_FORM_PATH);
    $this->assertResponse(200, 'Granted access to config form to privileged user.');

    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was disabled by default.');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was disabled by default.');

    $edit_enable_shunt['shunts[shunt]'] = 'shunt';
    $edit_enable_shunt['shunts[shuntexample]'] = FALSE;
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_enable_shunt, t('Save configuration'));
    $this->assertShuntStatus('shunt', TRUE, 'Shunt "shunt" was enabled.');
    $this->assertText(t('Shunt "shunt" has been enabled.'), 'Displayed message for enabled shunt');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was unchanged.');
    $this->assertNoText(t('Shunt "shuntexample" has been enabled.'), 'Did not display message for unchanged shunt');

    $edit_enable_shuntexample['shunts[shunt]'] = FALSE;
    $edit_enable_shuntexample['shunts[shuntexample]'] = 'shuntexample';
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_enable_shuntexample, t('Save configuration'));
    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was disabled.');
    $this->assertText(t('Shunt "shunt" has been disabled.'), 'Displayed message for disabled shunt');
    $this->assertShuntStatus('shuntexample', TRUE, 'Shunt "shuntexample" was enabled.');
    $this->assertText(t('Shunt "shuntexample" has been enabled.'), 'Displayed message for enabled shunt');

    $edit_disable_both['shunts[shunt]'] = FALSE;
    $edit_disable_both['shunts[shuntexample]'] = FALSE;
    $this->drupalPostForm($this::CONFIG_FORM_PATH, $edit_disable_both, t('Save configuration'));
    $this->assertShuntStatus('shunt', FALSE, 'Shunt "shunt" was unchanged.');
    $this->assertNoText(t('Shunt "shunt" has been disabled.'), 'Did not display message for unchanged shunt');
    $this->assertShuntStatus('shuntexample', FALSE, 'Shunt "shuntexample" was disabled.');
    $this->assertText(t('Shunt "shuntexample" has been disabled.'), 'Displayed message for disabled shunt');

    $this->drupalLogout();
    $this->drupalGet($this::CONFIG_FORM_PATH);
    $this->assertResponse(403, 'Denied access to config form to non-privileged user.');
  }

}

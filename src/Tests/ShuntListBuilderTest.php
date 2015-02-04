<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntListBuilderTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\shunt\Entity\Shunt;

/**
 * Tests the shunt list.
 *
 * @group shunt
 */
class ShuntListBuilderTest extends ShuntWebTestBase {

  /**
   * Tests the page.
   */
  public function testPage() {
    Shunt::load('shunt')->trip();

    $this->drupalGet(self::CONFIG_PAGE_PATH);
    $this->assertResponse(200, 'Granted access to the config page to privileged user.');
    $this->assertTitle('Shunts | Drupal');

    $help_text = $this->xpath('//div[@id=:id]/p', [':id' => 'block-help']);
    $this->assertTrue($help_text, 'Displayed help text.');

    $add_link = $this->xpath('//nav[@class=:class]/li[a[@href=:href] and a=:text]', [
      ':class' => 'action-links',
      ':href' => '/' . self::CONFIG_PAGE_PATH . '/add',
      ':text' => 'Add shunt',
    ]);
    $this->assertTrue($add_link, 'Displayed "Add shunt" link.');

    $thead = $this->xpath('//table/thead/tr[th[1]=:1 and th[2]=:2 and th[3]=:3 and th[4]=:4]', [
      ':1' => 'Shunt',
      ':2' => 'Description',
      ':3' => 'Status',
      ':4' => 'Operations',
    ]);
    $this->assertTrue($thead, 'Displayed expected columns');

    $shunt_details = $this->xpath('//table/tbody/tr[1][td[1]=:label and td[2]=:description]', [
      ':label' => 'Shunt',
      ':description' => 'Default shunt. No built-in behavior.',
    ]);
    $this->assertTrue($shunt_details, 'Correctly displayed shunt details.');

    $shunt_statuses = $this->xpath('//table/tbody[tr[1]/td[3]=:tripped and tr[2]/td[3]=:not]', [
      ':tripped' => 'Tripped',
      ':not' => 'Not tripped',
    ]);
    $this->assertTrue($shunt_statuses, 'Displayed correct shunt statuses.');

    $reset_dropdown_button = $this->xpath('//table/tbody/tr[1]/td[last()]/div/div/ul[@class=:class]/li[a[@href=:href] and a=:text]', [
      ':class' => 'dropbutton',
      ':href' => '/' . self::CONFIG_PAGE_PATH . '/shunt/reset',
      ':text' => 'Reset',
    ]);
    $this->assertTrue($reset_dropdown_button, 'Displayed "Reset" dropdown button on tripped shunt.');

    $trip_dropdown_button = $this->xpath('//table/tbody/tr[2]/td[last()]/div/div/ul[@class=:class]/li[a[@href=:href] and a=:text]', [
      ':class' => 'dropbutton',
      ':href' => '/' . self::CONFIG_PAGE_PATH . '/shunt_example/trip',
      ':text' => 'Trip',
    ]);
    $this->assertTrue($trip_dropdown_button, 'Displayed "Trip" dropdown button on not tripped shunt.');

    $edit_dropdown_button = $this->xpath('//table/tbody/tr[1]/td[last()]/div/div/ul[@class=:class]/li[a[@href=:href] and a=:text]', [
      ':class' => 'dropbutton',
      ':href' => '/' . self::CONFIG_PAGE_PATH . '/shunt/edit',
      ':text' => 'Edit',
    ]);
    $this->assertTrue($edit_dropdown_button, 'Displayed "Edit" dropdown button.');

    $delete_dropdown_button = $this->xpath('//table/tbody/tr[1]/td[last()]/div/div/ul[@class=:class]/li[a[@href=:href] and a=:text]', [
      ':class' => 'dropbutton',
      ':href' => '/' . self::CONFIG_PAGE_PATH . '/shunt/delete',
      ':text' => 'Delete',
    ]);
    $this->assertTrue($delete_dropdown_button, 'Displayed "Delete" dropdown button.');

    $this->drupalLogout();
    $this->drupalGet(self::CONFIG_PAGE_PATH);
    $this->assertResponse(403, 'Denied access to the config page to non-privileged user.');
  }

}

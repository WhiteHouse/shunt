<?php

/**
 * @file
 * Contains \Drupal\shunt\Tests\ShuntHandlerTest.
 */

namespace Drupal\shunt\Tests;

use Drupal\shunt\ShuntHandler;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the ShuntHandler class.
 *
 * @group Shunt
 *
 * @coversDefaultClass \Drupal\shunt\Tests\ShuntHandler
 */
class ShuntHandlerTest extends UnitTestCase {

  /**
   * The module handler mock.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The state mock.
   *
   * @var \Drupal\Core\State\StateInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $state;

  /**
   * The translation manager mock.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'ShuntHandler',
      'description' => 'Tests the ShuntHandler class.',
      'group' => 'Shunt',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->moduleHandler = $this->getMock('Drupal\Core\Extension\ModuleHandlerInterface');
    $this->state = $this->getMock('Drupal\Core\State\StateInterface');
    $this->translationManager = $this->getMock('Drupal\Core\StringTranslation\TranslationInterface');
  }

  /**
   * Tests that the ShuntHandler class can be instantiated.
   */
  public function testCreate() {
    new ShuntHandler($this->moduleHandler, $this->state, $this->translationManager);
  }

}

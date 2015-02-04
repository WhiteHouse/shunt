<?php

/**
 * @file
 * Contains \Drupal\shunt\Controller\ShuntexampleController.
 */

namespace Drupal\shuntexample\Controller;

use Drupal\shunt\Entity\Shunt;

/**
 * Controller class for the Shunt Example module.
 */
class ShuntexampleController {

  /**
   * Route content callback.
   *
   * @return array
   *   A theme array.
   *
   * @see shuntexample-hello.html.twig
   * @see shuntexample-fail.html.twig
   */
  public function helloContent() {
    // Fail cheap if the "shunt_example" shunt is tripped.
    if ($this->isShuntTripped()) {
      return ['#theme' => 'shuntexample_fail'];
    }

    // Expensive processing can be done down here.
    return ['#theme' => 'shuntexample_hello'];
  }

  /**
   * Route title callback.
   *
   * @return string
   *   A title string.
   */
  public function helloTitle() {
    return ($this->isShuntTripped()) ? t('Fail whale!') : t('Hello world!');
  }

  /**
   * Determines whether the shuntexample shunt is tripped.
   *
   * @return bool
   *   TRUE if the the shunt is tripped or FALSE if not.
   */
  protected function isShuntTripped() {
    // If your module doesn't declare a dependency on shunt in its .info.yml
    // file, it needs to make sure the module is enabled before trying to load
    // a shunt.
    if (!\Drupal::moduleHandler()->moduleExists('shunt')) {
      return FALSE;
    }

    return Shunt::load('shunt_example')->isTripped();
  }

}

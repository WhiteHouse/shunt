<?php

/**
 * @file
 * Contains \Drupal\shunt\Controller\ShuntexampleController.
 */

namespace Drupal\shuntexample\Controller;

/**
 * Controller class for the Shunt Example module.
 */
class ShuntexampleController {

  /**
   * Route callable method.
   *
   * @return array
   *   A theme array.
   *
   * @see shuntexample-hello.html.twig
   * @see shuntexample-fail.html.twig
   */
  public function hello() {
    // Fail cheap if the "shuntexample" shunt is enabled.
    if (\Drupal::moduleHandler()->moduleExists('shunt') && shunt_is_enabled('shuntexample')) {
      return array('#theme' => 'shuntexample_fail');
    }

    // Expensive processing can be done down here.
    return array('#theme' => 'shuntexample_hello');
  }
}

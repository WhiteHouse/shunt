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
   * Route content callback.
   *
   * @return array
   *   A theme array.
   *
   * @see shuntexample-hello.html.twig
   * @see shuntexample-fail.html.twig
   */
  public function helloContent() {
    // Fail cheap if the "shuntexample" shunt is enabled.
    if ($this->isShuntTripped()) {
      return array('#theme' => 'shuntexample_fail');
    }

    // Expensive processing can be done down here.
    return array('#theme' => 'shuntexample_hello');
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
    return \Drupal::moduleHandler()->moduleExists('shunt') && \Drupal::service('plugin.manager.shunt')->shuntIsEnabled('shuntexample');
  }

}

<?php

/**
 * @file
 * Contains \Drupal\shunt\Access\ShuntAccessCheck.
 */

namespace Drupal\shunt\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks access for shunt routes.
 */
class ShuntAccessCheck implements AccessCheckInterface {

  /**
   * Declares whether the access check applies to a specific route or not.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to consider attaching to.
   *
   * @return array
   *   An array of route requirement keys this access checker applies to.
   */
  public function applies(Route $route) {
    return array_key_exists('_shunt_access_check', $route->getRequirements());
  }

  /**
   * Checks access.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(Request $request) {
    $route = $request->get('_route');
    switch ($route) {
      case 'entity.shunt.delete_form':
        /** @var \Drupal\shunt\Entity\Shunt $shunt */
        $shunt = $request->get('shunt');
        if ($shunt->isProtected()) {
          return AccessResult::forbidden();
        }
        return AccessResult::allowed();

      default:
        return AccessResult::forbidden();
    }
  }

}

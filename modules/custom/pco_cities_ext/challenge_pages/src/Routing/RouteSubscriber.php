<?php
/**
 * @file
 * Contains \Drupal\ua_sc_module\Routing\SearchAlterRouteSubscriber.
 */

namespace Drupal\challenge_page_module\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('challenge')) {
      $route->setDefaults(array(
        '_controller' => 'Drupal\challenge_page_module\Controller\ChallengePageModuleController::challenge_page',
      ));
    }
  }

}


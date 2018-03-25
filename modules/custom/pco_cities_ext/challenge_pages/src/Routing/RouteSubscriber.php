<?php

namespace Drupal\challenge_pages\Routing;

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
      $route->setDefaults([
        '_controller' => 'Drupal\challenge_pages\Controller\ChallengePageController::challengePage',
      ]);
    }
  }

}

<?php

namespace Drupal\pco_cities\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * PCO CITIES mode subscriber for controller requests.
 */
class PCOCITIES4032404Subscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function on403(GetResponseForExceptionEvent $event) {
    if ($event->getException() instanceof AccessDeniedHttpException) {
      $event->setException(new NotFoundHttpException());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::EXCEPTION][] = ['on403'];
    return $events;
  }

}

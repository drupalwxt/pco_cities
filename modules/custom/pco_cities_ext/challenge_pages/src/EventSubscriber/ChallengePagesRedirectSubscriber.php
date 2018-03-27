<?php

namespace Drupal\challenge_pages\EventSubscriber;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ChallengePagesRedirectSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(LanguageManagerInterface $languageManager) {

    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    return([
      KernelEvents::REQUEST => [
        ['redirectMyContentTypeNode'],
      ],
    ]);
  }

  /**
   * Redirect requests for challenge go to custom module controller route.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function redirectMyContentTypeNode(GetResponseEvent $event) {
    $request = $event->getRequest();
    $language = $this->languageManager->getCurrentLanguage()->getId();

    if ($request->attributes->get('_route') !== 'entity.node.canonical') {
      return;
    }

    if ($request->attributes->get('node')->getType() !== 'challenge') {
      return;
    }

    // $redirect_url = Url::fromUri('entity:node/123');.
    $node = $request->attributes->get('node');
    if ($language == 'fr') {
      $redirect_url = '/fr/defis/' . $node->getTranslation('fr')->get('field_friendly_url')->getValue()[0]['value'];
    }
    else {
      $redirect_url = '/en/challenges/' . $node->get('field_friendly_url')->getValue()[0]['value'];
    }

    $response = new RedirectResponse($redirect_url, 301);
    $event->setResponse($response);
  }

}

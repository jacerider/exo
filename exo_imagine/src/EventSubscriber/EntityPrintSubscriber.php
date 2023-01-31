<?php

namespace Drupal\exo_imagine\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\entity_print\Event\PreSendPrintEvent;
use Drupal\entity_print\Event\PrintEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The PostRenderSubscriber class.
 */
class EntityPrintSubscriber implements EventSubscriberInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * PostRenderSubscriber constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(RequestStack $request_stack) {
    $this->requestStack = $request_stack;
  }

  /**
   * Before print.
   */
  public function preSend(PreSendPrintEvent $event) {
    $this->requestStack->getCurrentRequest()->headers->set('X-Drupal-Entity-Print', 1);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [PrintEvents::PRE_SEND => 'preSend'];
  }

}

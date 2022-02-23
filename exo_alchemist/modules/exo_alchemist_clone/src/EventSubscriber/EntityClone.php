<?php

namespace Drupal\exo_alchemist_clone\EventSubscriber;

use Drupal\entity_clone\Event\EntityCloneEvent;
use Drupal\entity_clone\Event\EntityCloneEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscribers for Entity Clone.
 */
class EntityClone implements EventSubscriberInterface {

  /**
   * An example event subscriber.
   *
   * Dispatched before an entity is cloned and saved.
   *
   * @see EntityCloneEvents::PRE_CLONE
   */
  public function entityPreClone(EntityCloneEvent $event): void {
    $new = $event->getClonedEntity();
    $new->exoAlchemistClone = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[EntityCloneEvents::PRE_CLONE][] = ['entityPreClone'];
    return $events;
  }

}

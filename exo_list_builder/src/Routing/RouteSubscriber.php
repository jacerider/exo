<?php

namespace Drupal\exo_list_builder\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -200];
    return $events;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = \Drupal::entityTypeManager()->getStorage('exo_entity_list')->loadMultiple();
    foreach ($exo_entity_lists as $exo_entity_list) {
      if (!$exo_entity_list->isPublished()) {
        continue;
      }
      $exo_entity_list->getHandler()->alterRoutes($collection);
    }
  }

}

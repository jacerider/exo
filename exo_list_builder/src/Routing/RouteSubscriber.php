<?php

namespace Drupal\exo_list_builder\Routing;

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
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = \Drupal::entityTypeManager()->getStorage('exo_entity_list')->loadMultiple();

    foreach ($exo_entity_lists as $exo_entity_list) {
      if ($exo_entity_list->isOverride()) {
        if ($exo_entity_list->getTargetEntityTypeId() === 'taxonomy_term') {
          continue;
        }
        $route = $collection->get('entity.' . $exo_entity_list->getTargetEntityTypeId() . '.collection');
        if ($route) {
          if ($url = $exo_entity_list->getUrl()) {
            $route->setPath($url);
          }
          $defaults = $route->getDefaults();
          $defaults['_controller'] = '\Drupal\exo_list_builder\Controller\ExoListController::listing';
          $defaults['_title_callback'] = '\Drupal\exo_list_builder\Controller\ExoListController::listingTitle';
          $defaults['exo_entity_list'] = $exo_entity_list->id();
          unset($defaults['_entity_list']);
          $route->setDefaults($defaults);
        }
      }
    }
  }

}

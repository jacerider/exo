<?php

namespace Drupal\exo_imagine\Routing;

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
    if ($route = $collection->get('image.style_public')) {
      $route->setDefault('_controller', 'Drupal\exo_imagine\Controller\ImageStyleDownloadController::deliver');
    }
  }

}

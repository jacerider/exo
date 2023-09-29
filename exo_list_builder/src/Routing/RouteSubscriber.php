<?php

namespace Drupal\exo_list_builder\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\exo_list_builder\EntityListInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

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
      if ($exo_entity_list->isOverride()) {
        if ($exo_entity_list->getTargetEntityTypeId() === 'taxonomy_term') {
          $route = $collection->get('entity.taxonomy_vocabulary.edit_form');
          if ($route) {
            // Set up a redirect route that can be used as a menu task.
            foreach ($exo_entity_list->getTargetBundleIds() as $bundle) {
              $edit_redirect_route = new Route('/admin/structure/taxonomy/manage/{taxonomy_vocabulary}/update', [
                '_controller' => '\Drupal\exo_list_builder\Controller\ExoListController::vocabularyEditRedirect',
              ], $route->getRequirements(), $route->getOptions());
              $collection->add('exo_list_builder.' . $exo_entity_list->id() . '.' . $bundle . '.taxonomy_vocabulary.update_form', $edit_redirect_route);
            }
          }
          continue;
        }
        if ($exo_entity_list->getTargetEntityTypeId() === 'node') {
          $route = $collection->get('system.admin_content');
          if ($route) {
            $this->overrideRoute($exo_entity_list, $route);
          }
          continue;
        }
        $route = $collection->get('entity.' . $exo_entity_list->getTargetEntityTypeId() . '.collection');
        if ($exo_entity_list->getTargetEntityTypeId() === 'media') {
          $route->setRequirement('_permission', 'access media overview');
        }
        if ($route) {
          $this->overrideRoute($exo_entity_list, $route);
        }
      }
    }
  }

  /**
   * Override a route.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_entity_list
   *   The entity list.
   * @param \Symfony\Component\Routing\Route $route
   *   The route.
   */
  protected function overrideRoute(EntityListInterface $exo_entity_list, Route $route) {
    if ($url = $exo_entity_list->getUrl()) {
      $route->setPath($url);
    }
    $defaults = $route->getDefaults();
    $defaults['_controller'] = '\Drupal\exo_list_builder\Controller\ExoListController::listing';
    $defaults['_title_callback'] = '\Drupal\exo_list_builder\Controller\ExoListController::listingTitle';
    $defaults['exo_entity_list'] = $exo_entity_list->id();
    unset($defaults['_entity_list']);
    unset($defaults['title']);
    $route->setDefaults($defaults);
    $options = $route->getOptions();
    $options['parameters']['exo_entity_list']['type'] = 'entity:exo_entity_list';
    $route->setOptions($options);
  }

}

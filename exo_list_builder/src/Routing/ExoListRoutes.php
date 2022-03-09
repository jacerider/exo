<?php

namespace Drupal\exo_list_builder\Routing;

use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes.
 */
class ExoListRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];

    $entity_type_manager = \Drupal::entityTypeManager();
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = $entity_type_manager->getStorage('exo_entity_list')->loadMultiple();

    foreach ($exo_entity_lists as $exo_entity_list) {
      $override = $exo_entity_list->isOverride();
      $defaults = [
        '_controller' => '\Drupal\exo_list_builder\Controller\ExoListController::listing',
        '_title_callback' => '\Drupal\exo_list_builder\Controller\ExoListController::listingTitle',
        'exo_entity_list' => $exo_entity_list->id(),
      ];
      $requirements = [
        '_entity_access'  => 'exo_entity_list.view',
      ];
      if ($override && $exo_entity_list->getTargetEntityTypeId() === 'taxonomy_term') {
        // Special condition allowing for override of taxonomy management page.
        foreach ($exo_entity_list->getTargetBundleIds() as $bundle) {
          $routes['exo_list_builder.' . $exo_entity_list->id() . '.taxonomy_vocabulary.overview_form'] = new Route('/admin/structure/taxonomy/manage/' . $bundle . '/overview', $defaults, $requirements);
        }
        $override = FALSE;
      }
      if (!$override && ($url = $exo_entity_list->getUrl())) {
        $routes['exo_list_builder.' . $exo_entity_list->id()] = new Route($url, $defaults, $requirements);
      }
    }

    foreach ($entity_type_manager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($exo_list_builder = $entity_type->get('exo_list_builder')) {
        foreach ($exo_list_builder as $link_template => $data) {
          if (!is_array($data)) {
            continue;
          }
          $data += [
            'id' => '',
            'title_callback' => '\Drupal\exo_list_builder\Controller\ExoListController::listingTitle',
            'controller' => '\Drupal\exo_list_builder\Controller\ExoListController::listing',
          ];
          $exo_entity_list_id = $data['id'];
          if ($entity_type->hasLinkTemplate($link_template) && isset($exo_entity_lists[$exo_entity_list_id])) {
            $exo_entity_list = $exo_entity_lists[$exo_entity_list_id];
            $route = new Route($entity_type->getLinkTemplate('archive-collection'));
            $route
              ->addDefaults([
                '_controller' => $data['controller'],
                '_title_callback' => $data['title_callback'],
                'exo_entity_list' => $exo_entity_list->id(),
              ])
              ->setRequirement('_entity_access', 'exo_entity_list.view');
            $routes["entity.{$entity_type_id}.archive_collection"] = $route;
          }
        }
      }
    }

    return $routes;
  }

}

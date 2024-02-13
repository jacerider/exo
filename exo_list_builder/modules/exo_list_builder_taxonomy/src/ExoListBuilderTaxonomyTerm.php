<?php

namespace Drupal\exo_list_builder_taxonomy;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Url;
use Drupal\exo_list_builder\ExoListBuilderContent;
use Drupal\taxonomy\VocabularyInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides a list builder for content entities.
 */
class ExoListBuilderTaxonomyTerm extends ExoListBuilderContent {

  /**
   * The vocabulary.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected ?VocabularyInterface $vocabulary;

  /**
   * {@inheritdoc}
   */
  public function toUrl(array $options = []) {
    $entity_list = $this->getEntityList();
    if ($entity_list->isOverride()) {
      foreach ($entity_list->getTargetBundleIds() as $bundle) {
        $route_name = 'exo_list_builder.taxonomy_vocabulary.' . $bundle . '.overview_form';
        return Url::fromRoute($route_name, [], $options);
      }
    }
    return parent::toUrl($options);
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteName() {
    $entity_list = $this->getEntityList();
    $override = $entity_list->isOverride();
    if ($override) {
      return 'exo_list_builder.' . $entity_list->id() . '.taxonomy_vocabulary.overview_form';
    }
    return parent::getRouteName();
  }

  /**
   * {@inheritdoc}
   */
  public function allowOverride() {
    $entity_list = $this->getEntityList();
    $bundles = $entity_list->getTargetBundleIds();
    if (count($bundles) === 1) {
      return TRUE;
    }
    return parent::allowOverride();
  }

  /**
   * Add the sort query.
   *
   * This only impacts non-table lists.
   */
  protected function addQuerySort(QueryInterface $query, $context = 'default') {
    parent::addQuerySort($query, $context);
    $query->sort('name');
  }

  /**
   * {@inheritDoc}
   */
  public function routes(array $current_routes) {
    $routes = [];
    $entity_list = $this->entityList;
    if ($entity_list->isOverride()) {
      $bundle_info = \Drupal::service('entity_type.bundle.info')->getAllBundleInfo();
      // Special condition allowing for override of taxonomy management page.
      foreach ($entity_list->getTargetBundleIds() as $bundle) {
        $generic = empty($entity_list->getTargetBundleIncludeIds());
        if ($generic && isset($current_routes['exo_list_builder.taxonomy_vocabulary.' . $bundle . '.overview_form'])) {
          continue;
        }
        $defaults = [
          '_controller' => '\Drupal\exo_list_builder_taxonomy\Controller\ExoListTaxonomyController::listing',
          '_title_callback' => '\Drupal\exo_list_builder_taxonomy\Controller\ExoListTaxonomyController::listingTitle',
          'exo_entity_list' => $entity_list->id(),
          'taxonomy_vocabulary' => $bundle,
        ];
        $list_url = $entity_list->getUrl();
        $url = $list_url && !$generic ? $list_url : '/admin/structure/taxonomy/manage/' . $bundle . '/overview';
        if (!$generic) {
          $defaults['_title_callback'] = '\Drupal\exo_list_builder\Controller\ExoListController::listingTitle';
        }
        // Overview.
        $routes['exo_list_builder.taxonomy_vocabulary.' . $bundle . '.overview_form'] = new Route($url, $defaults, [
          '_entity_access'  => 'exo_entity_list.view',
        ]);
        // Add.
        $routes['exo_list_builder.taxonomy_vocabulary.' . $bundle . '.add_form'] = new Route($url . '/add', [
          '_controller' => '\Drupal\taxonomy\Controller\TaxonomyController::addForm',
          '_title' => 'Add ' . $bundle_info['taxonomy_term'][$bundle]['label'] . ' Term',
          'taxonomy_vocabulary' => $bundle,
        ], [
          '_entity_create_access' => 'taxonomy_term:' . $bundle,
        ]);
      }
    }
    else {
      $routes = parent::routes($routes);
    }
    return $routes;
  }

  /**
   * {@inheritDoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $entity_list = $this->entityList;
    if ($entity_list->isOverride()) {
      $route = $collection->get('entity.taxonomy_vocabulary.edit_form');
      if ($route) {
        // Set up a redirect route that can be used as a menu task.
        foreach ($entity_list->getTargetBundleIds() as $bundle) {
          $edit_redirect_route = new Route('/admin/structure/taxonomy/manage/{taxonomy_vocabulary}/update', [
            '_controller' => '\Drupal\exo_list_builder_taxonomy\Controller\ExoListTaxonomyController::vocabularyEditRedirect',
          ], $route->getRequirements(), $route->getOptions());
          $collection->add('exo_list_builder.taxonomy_vocabulary.' . $bundle . '.update_form', $edit_redirect_route);
        }
      }
    }
  }

  /**
   * Get fields accounting for shown/hidden.
   *
   * @return array
   *   The fields.
   */
  protected function getShownFields() {
    $fields = parent::getShownFields();

    if ($vocabulary = $this->getVocabulary()) {
      // Integrate with taxonomy controls.
      $controls = $vocabulary->getThirdPartySettings('exo_list_builder');
      if (isset($controls['order']) && empty($controls['order'])) {
        unset($fields['weight']);
      }
    }

    return $fields;
  }

  /**
   * Get vocabulary.
   *
   * @return \Drupal\taxonomy\VocabularyInterface
   *   The vocabulary.
   */
  protected function getVocabulary() {
    if (!isset($this->vocabulary)) {
      $this->vocabulary = NULL;
      if ($vid = \Drupal::routeMatch()->getParameter('taxonomy_vocabulary')) {
        $this->vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load($vid);
      }
    }
    return $this->vocabulary;
  }

}

<?php

namespace Drupal\exo_list_builder_taxonomy\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local actions.
 */
class ExoListBuilderTaxonomyLocalActions extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * Route provider.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a FieldUiLocalAction object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider to load routes by name.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(RouteProviderInterface $route_provider, EntityTypeManagerInterface $entity_type_manager) {
    $this->routeProvider = $route_provider;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $this->derivatives = [];
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = $this->entityTypeManager->getStorage('exo_entity_list')->loadMultiple();
    $bundle_info = \Drupal::service('entity_type.bundle.info')->getAllBundleInfo();

    foreach ($exo_entity_lists as $exo_entity_list) {
      if (!$exo_entity_list->isPublished()) {
        continue;
      }
      $override = $exo_entity_list->isOverride();
      if ($override && $exo_entity_list->getTargetEntityTypeId() === 'taxonomy_term') {
        foreach ($exo_entity_list->getTargetBundleIds() as $bundle) {
          $this->derivatives['exo_list_builder.taxonomy_vocabulary.' . $bundle . '.add_form'] = [
            'route_name' => 'exo_list_builder.taxonomy_vocabulary.' . $bundle . '.add_form',
            'title' => 'Add ' . $bundle_info['taxonomy_term'][$bundle]['label'] . ' Term',
            'appears_on' => ['exo_list_builder.taxonomy_vocabulary.' . $bundle . '.overview_form'],
          ] + $base_plugin_definition;
        }
      }
    }

    return $this->derivatives;
  }

}

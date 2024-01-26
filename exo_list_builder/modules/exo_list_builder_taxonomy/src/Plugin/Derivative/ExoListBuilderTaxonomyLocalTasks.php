<?php

namespace Drupal\exo_list_builder_taxonomy\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines dynamic local tasks.
 */
class ExoListBuilderTaxonomyLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Creates a DynamicLocalTasks object.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(TranslationInterface $string_translation, EntityTypeManagerInterface $entity_type_manager) {
    $this->stringTranslation = $string_translation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var \Drupal\exo_list_builder\EntityListInterface[] $exo_entity_lists */
    $exo_entity_lists = $this->entityTypeManager->getStorage('exo_entity_list')->loadMultiple();

    foreach ($exo_entity_lists as $exo_entity_list) {
      if (!$exo_entity_list->isPublished()) {
        continue;
      }
      if ($exo_entity_list->isOverride() && $exo_entity_list->getTargetEntityTypeId() === 'taxonomy_term') {
        foreach ($exo_entity_list->getTargetBundleIds() as $bundle) {
          $route_name = 'exo_list_builder.taxonomy_vocabulary.' . $bundle . '.overview_form';
          $this->derivatives[$route_name] = [
            'route_name' => $route_name,
            'title' => 'List',
            'base_route' => $route_name,
            'weight' => -10,
          ] + $base_plugin_definition;
          // The update redirect route. This is needed due to how the taxonomy
          // module generates is menu tasks.
          $redirect_route_name = 'exo_list_builder.taxonomy_vocabulary.' . $bundle . '.update_form';
          $this->derivatives[$redirect_route_name] = [
            'route_name' => $redirect_route_name,
            'title' => 'Edit',
            'base_route' => $route_name,
          ] + $base_plugin_definition;
        }
      }
    }
    return $this->derivatives;
  }

}

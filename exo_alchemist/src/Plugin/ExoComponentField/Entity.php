<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_alchemist\Command\ExoComponentCommand;

/**
 * A generic entity adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "entity",
 *   label = @Translation("Entity"),
 *   context_definitions = {
 *     "entity" = @ContextDefinition("entity", label = @Translation("Entity"))
 *   }
 * )
 */
class Entity extends EntityReferenceBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'render' => $this->t('The rendered entity.'),
    ] + parent::propertyInfo();
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    $value = parent::viewValue($item, $delta, $contexts);
    $entity = $this->getReferencedEntity($item, $contexts);
    if ($entity) {
      $value['render'] = [
        '#markup' => $this->entityTypeManager->getViewBuilder($this->getEntityType())->view($entity, $this->getViewMode()),
      ];
    }
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public static function buildCommand(ExoComponentCommand $command, array &$data) {
    $entity_types = \Drupal::entityTypeManager()->getDefinitions();
    ksort($entity_types);
    $data['entity_type'] = $command->getIo()->choice(
        t('Entity Type'),
        array_keys($entity_types)
    );

    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($data['entity_type']);
    $data['bundles'] = $command->getIo()->choiceNoList(
        t('Bundles'),
        array_keys($bundles),
        NULL,
        TRUE
    );
  }

}

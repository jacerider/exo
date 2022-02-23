<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * A 'node' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "node",
 *   label = @Translation("Node"),
 *   context_definitions = {
 *     "entity" = @ContextDefinition("entity", label = @Translation("Entity"))
 *   }
 * )
 */
class Node extends EntityReference implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  protected $entityType = 'node';

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

}

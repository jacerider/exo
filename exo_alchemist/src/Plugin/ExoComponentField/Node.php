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

}

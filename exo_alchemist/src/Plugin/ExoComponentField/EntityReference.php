<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * A generic 'entity' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "entity_reference",
 *   label = @Translation("Entity Reference"),
 * )
 */
class EntityReference extends EntityReferenceBase implements ContainerFactoryPluginInterface {

}

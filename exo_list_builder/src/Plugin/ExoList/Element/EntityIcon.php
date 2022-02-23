<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "entity_icon",
 *   label = @Translation("Render"),
 *   description = @Translation("Render the entity icon."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "_icon",
 *   },
 *   exclusive = FALSE,
 * )
 */
class EntityIcon extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    if ($icon = $this->getIcon($entity)) {
      return $icon->setIconOnly();
    }
    return NULL;
  }

}

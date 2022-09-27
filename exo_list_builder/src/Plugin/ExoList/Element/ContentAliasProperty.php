<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "content_alias_property",
 *   label = @Translation("Content Alias Property"),
 *   description = @Translation("Render a property of a content entity."),
 *   weight = 0,
 *   field_type = {
 *     "content_alias_property",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = TRUE,
 * )
 */
class ContentAliasProperty extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return isset($field['alias_property']) ? $field_item->get($field['alias_property'])->getValue() : NULL;
  }

}

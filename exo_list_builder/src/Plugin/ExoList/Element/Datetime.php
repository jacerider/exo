<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "datetime",
 *   label = @Translation("Formatted Date"),
 *   description = @Translation("Render the datetime as formatted date."),
 *   weight = 0,
 *   field_type = {
 *     "datetime",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Datetime extends Timestamp {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return $field_item->date ? $this->formatTimestamp($field_item->date->getTimestamp(), $field_item->date->getTimezone()->getName()) : '';
  }

}

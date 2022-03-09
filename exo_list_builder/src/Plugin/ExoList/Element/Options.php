<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "options",
 *   label = @Translation("Label"),
 *   description = @Translation("Render options as option label."),
 *   weight = 0,
 *   field_type = {
 *     "list_float",
 *     "list_integer",
 *     "list_string",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class Options extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $provider = $field_item->getFieldDefinition()->getFieldStorageDefinition()->getOptionsProvider('value', $entity);
    // Flatten the possible options, to support opt groups.
    $options = OptGroup::flattenOptions($provider->getPossibleOptions());
    return $options[$field_item->value];
  }

}

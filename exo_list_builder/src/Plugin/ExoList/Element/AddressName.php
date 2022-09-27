<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "address_name",
 *   label = @Translation("Address: Name"),
 *   description = @Translation("Render the first/middle/last name of address."),
 *   weight = 0,
 *   field_type = {
 *     "address__name",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = TRUE,
 * )
 */
class AddressName extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $value = $field_item->getValue();
    return implode(' ', array_filter([
      $value['given_name'],
      $value['additional_name'],
      $value['family_name'],
    ]));
  }

}

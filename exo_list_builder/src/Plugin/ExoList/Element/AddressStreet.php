<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "address_street",
 *   label = @Translation("Address: Street"),
 *   description = @Translation("Render the address line 1 and line 2."),
 *   weight = 0,
 *   field_type = {
 *     "address__street",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = TRUE,
 * )
 */
class AddressStreet extends ExoListElementContentBase {

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $value = $field_item->getValue();
    return implode(', ', array_filter([
      $value['address_line1'],
      $value['address_line2'],
    ]));
  }

}

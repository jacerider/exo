<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "address_street",
 *   label = @Translation("Address: Street"),
 *   description = @Translation("Filter by address address line 1 and line 2."),
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
class AddressStreet extends ExoListFilterMatchBase {

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $group = $query->orConditionGroup();
    $this->queryAlterByField($field['field_name'] . '.address_line1', $group, $value, $entity_list, $field);
    $this->queryAlterByField($field['field_name'] . '.address_line2', $group, $value, $entity_list, $field);
    $query->condition($group);
  }

}

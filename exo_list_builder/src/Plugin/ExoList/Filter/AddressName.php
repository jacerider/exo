<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "address_name",
 *   label = @Translation("Address: Name"),
 *   description = @Translation("Filter by address full name."),
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
class AddressName extends ExoListFilterMatchBase {

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $group = $query->orConditionGroup();
    $this->queryAlterByField($field['field_name'] . '.given_name', $group, $value, $entity_list, $field);
    $this->queryAlterByField($field['field_name'] . '.additional_name', $group, $value, $entity_list, $field);
    $this->queryAlterByField($field['field_name'] . '.family_name', $group, $value, $entity_list, $field);
    $query->condition($group);
  }

}

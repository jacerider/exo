<?php

namespace Drupal\exo_list_builder;

/**
 * Defines an interface for classes to expose filter values and alter the query.
 */
interface ExoListComputedFilterInterface {

  /**
   * Get the available field values.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param string $field_id
   *   The field id.
   * @param string $property
   *   The property id.
   * @param string $condition
   *   The input condition.
   *
   * @return string[]
   *   The field values.
   */
  public static function getExoListAvailableFieldValues(EntityListInterface $entity_list, $field_id, $property, $condition);

  /**
   * Alter the exo list query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface $query
   *   The query.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return string[]
   *   The field values.
   */
  public static function alterExoListQuery($query, $value, EntityListInterface $entity_list, array $field);

}

<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Form\FormInterface;

/**
 * Defines an interface to build entity listings.
 */
interface ExoListBuilderInterface extends EntityListBuilderInterface, FormInterface {

  /**
   * Get entity list.
   *
   * @return \Drupal\exo_list_builder\EntityListInterface
   *   The entity list.
   */
  public function getEntityList();

  /**
   * Get entity list.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   *
   * @return $this
   */
  public function setEntityList(EntityListInterface $entity_list);

  /**
   * Load available entity fields.
   *
   * @return array
   *   The entity fields.
   */
  public function loadFields();

  /**
   * Add a condition to the query.
   *
   * @param string|\Drupal\Core\Entity\Query\ConditionInterface $field
   *   Name of the field being queried or an instance of ConditionInterface.
   * @param string|int|bool|array|null $value
   *   (optional) The value for $field.
   * @param string|null $operator
   *   (optional) The comparison operator. Possible values:
   *   - '=', '<>', '>', '>=', '<', '<=', 'STARTS_WITH', 'CONTAINS',
   *     'ENDS_WITH': These operators expect $value to be a literal of the
   *     same type as the column.
   *   - 'IN', 'NOT IN': These operators expect $value to be an array of
   *     literals of the same type as the column.
   *   - 'IS NULL', 'IS NOT NULL': These operators ignore $value, for that
   *     reason it is recommended to use a $value of NULL for clarity.
   *   - 'BETWEEN', 'NOT BETWEEN': These operators expect $value to be an array
   *     of two literals of the same type as the column.
   *   If NULL, defaults to the '=' operator.
   * @param string|null $langcode
   *   (optional) The language code allows filtering results by specific
   *   language.
   *
   * @return $this
   */
  public function addQueryCondition($field, $value = NULL, $operator = NULL, $langcode = NULL);

}
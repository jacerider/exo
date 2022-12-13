<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;

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
   * Get a query option.
   *
   * @return mixed
   *   The query options.
   */
  public function getOption($key, $default_value = NULL);

  /**
   * Set a query option.
   *
   * @return $this
   */
  public function setOption($key, $value);

  /**
   * Get limit.
   *
   * @return int
   *   The limit.
   */
  public function getLimit();

  /**
   * Set limit. This will override configuration.
   *
   * @param int $limit
   *   The limit.
   *
   * @return $this
   */
  public function setLimit($limit);

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

  /**
   * Build filter form fields.
   */
  public function buildFormFilterFields(array $filters, FormStateInterface $form_state);

  /**
   * Get the query.
   *
   * @param string $context
   *   Can be used to alter the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query.
   */
  public function getQuery($context = 'default');

  /**
   * Get the cached total.
   *
   * If not cached, will load the total.
   *
   * @return int
   *   The total results.
   */
  public function getTotal();

  /**
   * Get the raw total.
   *
   * The raw total will rebuild the query and return the total.
   *
   * @param bool $ignoreFilters
   *   Ignore filters.
   *
   * @return int
   *   The total results.
   */
  public function getRawTotal($ignoreFilters = FALSE);

  /**
   * Get actions.
   *
   * @return \Drupal\exo_list_builder\Plugin\ExoListActionInterface[]
   *   An array of action instances.
   */
  public function getActions();

  /**
   * Get filters.
   *
   * @return array
   *   The fields.
   */
  public function getFilters();

  /**
   * Get the current value of a filter.
   *
   * @param string $field_id
   *   The field id.
   *
   * @return mixed
   *   The value of the filter.
   */
  public function getFilterValue($field_id);

  /**
   * Get weight field.
   *
   * @return array
   *   The weight field.
   */
  public function getWeightField();

  /**
   * Check if the entity list is filtered.
   *
   * @param bool $include_defaults
   *   Whether to count default values as filtered.
   *
   * @return bool
   *   Returns TRUE if filtered.
   */
  public function isFiltered($include_defaults = FALSE);

  /**
   * Check if the entity list has been modified by the user.
   *
   * This can happen any time the list is submitted.
   *
   * @return bool
   *   Returns TRUE if modified.
   */
  public function isModified();

  /**
   * Get queue.
   *
   * @return \Drupal\Core\Queue\QueueInterface
   *   The queue.
   */
  public function getQueue($action_id);

  /**
   * Get cache contexts.
   *
   * @return array
   *   The cache contexts.
   */
  public function getCacheContexts();

  /**
   * Get cache tags.
   *
   * @return array
   *   The cache tags.
   */
  public function getCacheTags();

}

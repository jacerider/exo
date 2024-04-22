<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\Routing\RouteCollection;

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
   * Gets the URL object for the list.
   *
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\Exception\UndefinedLinkTemplateException
   */
  public function toUrl(array $options = []);

  /**
   * Returns the route name.
   *
   * @return string
   *   The route name.
   */
  public function getRouteName();

  /**
   * Check if entity list support overriding the list builder.
   *
   * @return bool
   *   Return TRUE if the entity list support overriding the list builder.
   */
  public function allowOverride();

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
   * Get options url.
   *
   * @param array $exclude_options
   *   An array of query options to exclude.
   * @param array $exclude_filters
   *   An array of query filters to exclude.
   * @param array $query
   *   Additional query parameters.
   *
   * @return \Drupal\Core\Url
   *   The url.
   */
  public function getOptionsUrl(array $exclude_options = [], array $exclude_filters = [], array $query = []);

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
   * Get field entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The field entity.
   */
  public function getFieldEntity(EntityInterface $entity, array $field);

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
   * Get query conditions.
   */
  public function getQueryConditions();

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
   * @param string $context
   *   Can be used to alter the query.
   *
   * @return int
   *   The total results.
   */
  public function getRawTotal($ignoreFilters = FALSE, $context = 'all');

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
   * Add routes.
   *
   * @param \Symfony\Component\Routing\Route[] $current_routes
   *   The current routes.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of new routes.
   */
  public function routes(array $current_routes);

  /**
   * Alter routes.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The route collection.
   */
  public function alterRoutes(RouteCollection $collection);

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

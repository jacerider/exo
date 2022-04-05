<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining eXo Entity List entities.
 */
interface EntityListInterface extends ConfigEntityInterface {

  /**
   * Return a filtered url.
   *
   * @param array $filters
   *   The filters.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   */
  public function toFilteredUrl(array $filters = [], array $options = []);

  /**
   * Encode options.
   *
   * @parar array $options
   *   The options.
   *
   * @return string
   *   The encoded options.
   */
  public function optionsEncode(array $options);

  /**
   * Decode options.
   *
   * @parar string $options
   *   The options.
   *
   * @return array
   *   The decoded options.
   */
  public function optionsDecode($options);

  /**
   * Get the target entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getTargetEntityTypeId();

  /**
   * Get the target entity type.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   The target entity type definition.
   */
  public function getTargetEntityType();

  /**
   * Get the target bundle ids.
   *
   * @return array
   *   The target bundle ids.
   */
  public function getTargetBundleIds();

  /**
   * Get the target bundle ids to include.
   *
   * @return array
   *   The target bundle ids to include.
   */
  public function getTargetBundleIncludeIds();

  /**
   * Get the target bundle ids to exclude.
   *
   * @return array
   *   The target bundle ids to exclude.
   */
  public function getTargetBundleExcludeIds();

  /**
   * Check if we are using all bundles.
   *
   * @return bool
   *   All bundles if TRUE.
   */
  public function isAllBundles();

  /**
   * Check if entity list support overriding the list builder.
   *
   * @return bool
   *   Return TRUE if the entity list support overriding the list builder.
   */
  public function allowOverride();

  /**
   * Check if the entity should be used as the list builder for the target type.
   *
   * @return bool
   *   Returns TRUE if the entity should be used as the list builder.
   */
  public function isOverride();

  /**
   * Return the entity list format.
   *
   * @return string
   *   The entity list format.
   */
  public function getFormat();

  /**
   * Return the entity list url.
   *
   * @return string
   *   The entity list url.
   */
  public function getUrl();

  /**
   * Returns the route name.
   *
   * @return string
   *   The route name.
   */
  public function getRouteName();

  /**
   * Get items per page.
   *
   * @return int
   *   The number of items per page.
   */
  public function getLimit();

  /**
   * Get the limit options.
   *
   * @return array
   *   The limit options as [int => int].
   */
  public function getLimitOptions();

  /**
   * Returns operations status.
   *
   * @return bool
   *   Returns TRUE if operations should be shown.
   */
  public function showOperations();

  /**
   * Get enabled action definitions.
   *
   * @return array
   *   The action ddefinitions.
   */
  public function getActions();

  /**
   * Get available action definitions.
   *
   * @return array
   *   The action ddefinitions.
   */
  public function getAvailableActions();

  /**
   * Get the sort default field.
   *
   * @return string
   *   The sort default field id.
   */
  public function getSort();

  /**
   * Get enabled field definitions.
   *
   * @return array
   *   The field definitions.
   */
  public function getFields();

  /**
   * Set enabled field definitions.
   *
   * @param array $fields
   *   The field definitions.
   *
   * @return $this
   */
  public function setFields(array $fields);

  /**
   * Get available field definitions.
   *
   * @return array
   *   The field definitions.
   */
  public function getAvailableFields();

  /**
   * Check if field is enabled.
   *
   * @return bool
   *   TRUE if the field is enabled.
   */
  public function hasField($field_name);

  /**
   * Get field definition.
   *
   * @return array
   *   The field definition.
   */
  public function getField($field_name);

  /**
   * Get field value.
   *
   * @param string $field_name
   *   The field name.
   * @param string $key
   *   The value key.
   *
   * @return mixed
   *   The field value.
   */
  public function getFieldValue($field_name, $key);

  /**
   * Gets the weight of this list.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

  /**
   * Sets the weight of this list.
   *
   * @param int $weight
   *   The weight of the variant.
   *
   * @return $this
   */
  public function setWeight($weight);

  /**
   * Get the exo_list_buidler handler class.
   *
   * @return string
   *   The class.
   */
  public function getHandlerClass();

  /**
   * Get the exo_list_builder entity handler.
   *
   * @return \Drupal\exo_list_builder\ExoListBuilderInterface
   *   The entity handler.
   */
  public function getHandler();

  /**
   * Gets the settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings();

  /**
   * Gets data from this settings object.
   *
   * @param string $key
   *   A string that maps to a key within the configuration data.
   *   If no key is specified, then the entire data array is returned.
   * @param mixed $default
   *   The default value to return if the key is not found.
   *
   * @return mixed
   *   The data that was requested.
   */
  public function getSetting($key = '', $default = NULL);

}

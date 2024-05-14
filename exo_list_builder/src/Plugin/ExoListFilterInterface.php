<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list filters.
 */
interface ExoListFilterInterface extends PluginInspectionInterface, ConfigurableInterface {

  /**
   * The default settings.
   */
  const DEFAULTS = [
    'position' => 'header',
    'label' => '',
    'expose' => TRUE,
    'default' => [
      'status' => FALSE,
      'lock' => FALSE,
      'value' => NULL,
    ],
    'expose_block' => FALSE,
    'multiple' => FALSE,
    'multiple_join' => 'or',
    'allow_zero' => FALSE,
    'remember' => FALSE,
  ];

  /**
   * Gets default value for this plugin.
   *
   * @return array
   *   An associative array with the default value.
   */
  public function defaultValue();

  /**
   * Build the configuration form.
   *
   * @param array $form
   *   The configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field);

  /**
   * Validates a configuration form for this plugin.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state);

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field);

  /**
   * Alter build form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return array
   *   The form structure.
   */
  public function buildFormAfter(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field);

  /**
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   */
  public function validateForm(array &$form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field);

  /**
   * Check if the filter value is considered empty.
   *
   * @param mixed $raw_value
   *   The filter value.
   *
   * @return bool
   *   Returns TRUE if value is considered empty.
   */
  public function isEmpty($raw_value);

  /**
   * Check if filter supports multiple.
   *
   * @return bool
   *   Returns TRUE if filter supports multiple.
   */
  public function supportsMultiple();

  /**
   * Check if field allows multiple.
   *
   * @param array $field
   *   The field definition.
   *
   * @return bool
   *   Returns TRUE if filter allows multiple.
   */
  public function allowsMultiple(array $field);

  /**
   * Get the join type.
   *
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   Etiher 'or' or 'and'.
   */
  public function getMultipleJoin(array $field);

  /**
   * Convert value to query.
   *
   * @param array $raw_value
   *   The filter raw value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return string|array
   *   The url value.
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field);

  /**
   * Convert value to preview.
   *
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   The preview string.
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field);

  /**
   * Get default filter value.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   The default value.
   */
  public function getDefaultValue(EntityListInterface $entity_list, array $field);

  /**
   * Helper function to get filtered options.
   *
   * Only applies to filters that implement ExoListFieldValuesElementInterface
   * and ExoListFieldValuesInterface.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   * @param string $input
   *   The input.
   *
   * @return array
   *   The filtered options.
   */
  public function getFilteredValueOptions(EntityListInterface $entity_list, array $field, $input = NULL);

  /**
   * Should query be altered.
   *
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return bool
   *   Returns TRUE if query should be altered.
   */
  public function allowQueryAlter(&$value, EntityListInterface $entity_list, array $field);

  /**
   * Get the field name used in the query.
   *
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   The field name.
   */
  public function getQueryFieldName(array $field);

  /**
   * Alter the entity query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Entity\Query\ConditionInterface $query
   *   The query.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field);

  /**
   * Alter the raw query.
   *
   * @param \Drupal\Core\Database\Query\SelectInterface $query
   *   The query.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   */
  public function queryRawAlter(SelectInterface $query, $value, EntityListInterface $entity_list, array $field);

  /**
   * Get result total for a given field value.
   *
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return int
   *   The total.
   */
  public function getOptionTotal($value, EntityListInterface $entity_list, array $field);

  /**
   * Get the overview value.
   *
   * If a value is provided, the value will be shown in the filter overview.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   The overview value.
   */
  public function getOverviewValue(EntityListInterface $entity_list, array $field);

  /**
   * Whether this theme negotiator should be used to set the theme.
   *
   * @param array $field
   *   The field definition.
   *
   * @return bool
   *   TRUE if this filter should be allowed.
   */
  public function applies(array $field);

}

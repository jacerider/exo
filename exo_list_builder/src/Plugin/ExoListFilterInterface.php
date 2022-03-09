<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list filters.
 */
interface ExoListFilterInterface extends PluginInspectionInterface, ConfigurableInterface {

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
   * Form validation handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function validateForm(array &$form, FormStateInterface $form_state);

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
   * @return array
   *   The form structure.
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
   * Alter the query.
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

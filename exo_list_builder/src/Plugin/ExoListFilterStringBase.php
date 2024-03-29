<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListFilterStringBase extends ExoListFilterMatchBase {

  /**
   * Returns the options for the match operator.
   *
   * @return array
   *   List of options.
   */
  protected function getMatchOperatorOptions() {
    return [
      '=' => t('Equals'),
      'STARTS_WITH' => t('Starts with'),
      'CONTAINS' => t('Contains'),
      'CONTAINS_ANY' => t('Contains any'),
      'ENDS_WITH' => t('Ends with'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'additional_fields' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();

    $options = [];
    foreach ($entity_list->getAvailableFields() as $field) {
      if (in_array($field['type'], ['string', 'text_long'])) {
        $options[$field['field_name']] = $field['label'];
      }
    }
    if ($options) {
      $form['additional_fields'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Also search in'),
        '#options' => $options,
        '#default_value' => $configuration['additional_fields'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    $form_state->setValue('additional_fields', array_filter($form_state->getValue('additional_fields') ?: []));
  }

  /**
   * Alter string fields.
   *
   * @param string $field_id
   *   The field id.
   * @param \Drupal\Core\Entity\Query\QueryInterface|\Drupal\Core\Condition\ConditionInterface $query
   *   The query.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   */
  protected function queryAlterByStringField($field_id, $query, $value, EntityListInterface $entity_list, array $field) {
    $configuration = $this->getConfiguration();

    if (!empty($configuration['additional_fields'])) {
      $subquery = $query->orConditionGroup();
      $this->queryAlterByField($field_id, $subquery, $value, $entity_list, $field);
      foreach ($configuration['additional_fields'] as $additional_property) {
        $this->queryAlterByField($additional_property, $subquery, $value, $entity_list, $field);
      }
      $this->queryAlterByField($field_id, $subquery, $value, $entity_list, $field);
      $query->condition($subquery);
      return;
    }
    if ($field_id) {
      $this->queryAlterByField($field_id, $query, $value, $entity_list, $field);
    }
  }

}

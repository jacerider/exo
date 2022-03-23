<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListFilterMatchBase extends ExoListFilterBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'match_operator' => '=',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValue() {
    return '';
  }

  /**
   * Returns the options for the match operator.
   *
   * @return array
   *   List of options.
   */
  protected function getMatchOperatorOptions() {
    return [
      '=' => t('Equals'),
      '>' => t('Greater than'),
      '<' => t('Less than'),
      '>=' => t('Greater than or equal'),
      '<=' => t('Less than or equal'),
      '<>' => t('Does not equal'),
      'IN' => t('IN'),
      'NOT IN' => t('NOT IN'),
      'STARTS_WITH' => t('Starts with'),
      'CONTAINS' => t('Contains'),
      'ENDS_WITH' => t('Ends with'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $form['match_operator'] = [
      '#type' => 'select',
      '#title' => $this->t('Match operator'),
      '#options' => $this->getMatchOperatorOptions(),
      '#default_value' => $configuration['match_operator'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $form['q'] = [
      '#type' => 'textfield',
      '#title' => $field['display_label'],
      '#default_value' => $value,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    return $raw_value['q'];
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return empty($raw_value['q']);
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['field_name'], $query, $value, $entity_list, $field);
  }

  /**
   * Alter the query by field_id and match operator.
   *
   * @param string $field_id
   *   The field id.
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query.
   * @param mixed $value
   *   The filter value.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   */
  protected function queryAlterByField($field_id, QueryInterface $query, $value, EntityListInterface $entity_list, array $field) {
    $match_operator = $this->getConfiguration()['match_operator'];
    $query->condition($field_id, $value, $match_operator);
  }

}

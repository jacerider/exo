<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListContentTrait;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "content_property",
 *   label = @Translation("Property"),
 *   description = @Translation("Filter by entity property."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class ContentProperty extends ExoListFilterMatchBase implements ExoListFieldValuesInterface {
  use ExoListContentTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'property' => [],
      'autocomplete' => FALSE,
      'select' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $properties = $this->getPropertyOptions($field['definition']);
    if (empty($configuration['property'])) {
      $configuration['property'] = key($properties);
    }
    $form['property'] = [
      '#type' => 'radios',
      '#title' => $this->t('Property'),
      '#options' => $properties,
      '#default_value' => $configuration['property'],
      '#required' => TRUE,
    ];
    if (count($properties) > 5) {
      $form['property']['#type'] = 'select';
    }

    $form['autocomplete'] = [
      '#type' => 'checkbox',
      '#id' => $form['#id'] . '-autocomplete',
      '#title' => $this->t('As Autocomplete'),
      '#default_value' => $configuration['autocomplete'],
      '#states' => [
        'disabled' => [
          ':input[id="' . $form['#id'] . '-dropdown' . '"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['select'] = [
      '#type' => 'checkbox',
      '#id' => $form['#id'] . '-dropdown',
      '#title' => $this->t('As Select Dropdown'),
      '#default_value' => $configuration['select'],
      '#states' => [
        'disabled' => [
          ':input[id="' . $form['#id'] . '-autocomplete' . '"]' => ['checked' => TRUE],
        ],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);

    $configuration = $this->getConfiguration();
    if (!empty($configuration['select'])) {
      $options = $this->getValueOptions($entity_list, $field);
      $form['q']['#type'] = 'select';
      $form['q']['#options'] = ['' => $this->t('- Select -')] + array_combine($options, $options);
    }
    elseif (!empty($configuration['autocomplete'])) {
      $form['q'] += [
        '#autocomplete_route_name' => 'exo_list_builder.autocomplete',
        '#autocomplete_route_parameters' => [
          'exo_entity_list' => $entity_list->id(),
          'field_name' => $field['id'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['id'] . '.' . $this->getConfiguration()['property'], $query, $value, $entity_list, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $configuration = $this->getConfiguration();
    return $this->getAvailableFieldValues($entity_list, $field['id'], $configuration['property'], $input);
  }

}

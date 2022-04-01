<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a config entity field.
 *
 * @ExoListFilter(
 *   id = "config_property",
 *   label = @Translation("Property"),
 *   description = @Translation("Filter by entity property."),
 *   weight = 0,
 *   field_type = {
 *     "config",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class ConfigProperty extends ExoListFilterMatchBase implements ExoListFieldValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
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
      $form['q']['#type'] = 'select';
      $form['q']['#options'] = ['' => $this->t('- Select -')] + $this->getValueOptions($entity_list, $field);
    }
    elseif (!empty($configuration['autocomplete'])) {
      $form['q'] += [
        '#autocomplete_route_name' => 'exo_list_builder.autocomplete',
        '#autocomplete_route_parameters' => [
          'exo_entity_list' => $entity_list->id(),
          'field_id' => $field['id'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['field_name'], $query, $value, $entity_list, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface[] $entities */
    $entities = \Drupal::entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->loadMultiple();
    $options = [];
    foreach ($entities as $entity) {
      $value = $entity->get('target_entity_type');
      if (is_string($value)) {
        $options[$value] = $value;
      }
      if (is_array($value)) {
        foreach ($value as $val) {
          if (is_string($val)) {
            $options[$val] = $val;
          }
        }
      }
    }
    return array_combine($options, $options);
  }

}

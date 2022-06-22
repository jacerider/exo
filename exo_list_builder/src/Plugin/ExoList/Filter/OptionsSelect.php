<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "options_select",
 *   label = @Translation("Select"),
 *   description = @Translation("Filter options with a select element."),
 *   weight = 0,
 *   field_type = {
 *     "list_float",
 *     "list_integer",
 *     "list_string",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class OptionsSelect extends ExoListFilterBase implements ExoListFieldValuesInterface {

  /**
   * {@inheritdoc}
   */
  protected $supportsMultiple = TRUE;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'include' => [],
      'exclude' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $options = $this->getValueOptions($entity_list, $field);
    $form['include'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Include'),
      '#default_value' => $configuration['include'],
      '#options' => $options,
    ];
    $form['exclude'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Exclude'),
      '#default_value' => $configuration['exclude'],
      '#options' => $options,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
    parent::validateConfigurationForm($form, $form_state);
    $form_state->setValue('include', array_filter($form_state->getValue('include', [])));
    $form_state->setValue('exclude', array_filter($form_state->getValue('exclude', [])));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $configuration = $this->getConfiguration();
    $options = $this->getValueOptions($entity_list, $field);
    if (!empty(array_filter($configuration['include']))) {
      $options = array_intersect_key($options, array_flip($configuration['include']));
    }
    if (!empty(array_filter($configuration['exclude']))) {
      $options = array_diff_key($options, array_flip($configuration['exclude']));
    }

    $form['q'] = [
      '#type' => 'select',
      '#title' => $field['display_label'],
      '#multiple' => $this->allowsMultiple($field),
      '#options' => $options,
      '#empty_option' => $this->t('- All -'),
      '#empty_value' => NULL,
      '#default_value' => $value,
    ];
    $form['#access'] = !empty($options) && count($options) > 1;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $entities = [];
    $bundle_key = $entity_list->getTargetEntityType()->getKey('bundle');
    if ($bundle_key) {
      foreach ($entity_list->getTargetBundleIds() as $bundle_id) {
        $entities[] = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->create([
          'type' => $bundle_id,
        ]);
      }
    }
    else {
      $entities[] = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->create();
    }
    $options = [];
    foreach ($entities as $entity) {
      $provider = $field['definition']->getFieldStorageDefinition()->getOptionsProvider('value', $entity);
      $options += OptGroup::flattenOptions($provider->getPossibleOptions());
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty($raw_value) {
    return $this->checkEmpty($raw_value['q']);
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
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $query->condition($field['field_name'], $value, '=');
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    $options = $this->getValueOptions($entity_list, $field);
    return $options[$value];
  }

}

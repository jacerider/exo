<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterInterface;
use Drupal\exo_list_builder\Plugin\ExoListWidgetBase;
use Drupal\exo_list_builder\Plugin\ExoListWidgetValuesInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListWidget(
 *   id = "select",
 *   label = @Translation("Dropdown"),
 *   description = @Translation("Select widget."),
 * )
 */
class Select extends ExoListWidgetBase implements ExoListWidgetValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'empty_option' => NULL,
      'empty_value' => NULL,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $filter, $field);
    $configuration = $this->getConfiguration();
    $form['empty_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option'),
      '#default_value' => $configuration['empty_option'],
    ];
    $form['empty_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty value'),
      '#default_value' => $configuration['empty_value'],
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $configuration = $this->getConfiguration();
    $options = $filter->getFilteredValueOptions($entity_list, $field);
    $element['#type'] = 'select';
    $element['#exo_configuration'] = $configuration;
    if (!empty($configuration['empty_option']) && !empty($element['empty_value'])) {
      $element['#empty_option'] = $configuration['empty_option'];
      $element['#empty_value'] = $configuration['empty_value'];
    }
    elseif (!empty($configuration['empty_value'])) {
      $options = [$configuration['empty_value'] => $this->t('- All -')] + $options;
    }
    elseif (!empty($configuration['empty_option'])) {
      $options = ['' => $configuration['empty_option']] + $options;
    }
    else {
      $options = ['' => $this->t('- All -')] + $options;
    }
    $element['#options'] = $options;
    $element['#multiple'] = $filter->allowsMultiple($field);
    $element['#access'] = count($options) > 1;
    $element['#element_validate'] = [[get_class($this), 'validateElement']];
  }

  /**
   * Element validate callback.
   */
  public static function validateElement($element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if ($value && isset($element['#exo_configuration']['empty_value']) && $element['#exo_configuration']['empty_value'] === $value) {
      $form_state->setValue($element['#parents'], NULL);
    }
  }

}

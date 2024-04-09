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
 *   id = "checkboxes",
 *   label = @Translation("Check boxes/radio buttons"),
 *   description = @Translation("Check box/radio widget."),
 * )
 */
class Options extends ExoListWidgetBase implements ExoListWidgetValuesInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'limit' => 50,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $filter, $field);
    $configuration = $this->getConfiguration();
    $form['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum Results'),
      '#default_value' => $configuration['limit'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $options = $filter->getFilteredValueOptions($entity_list, $field);
    $element['#type'] = 'radios';
    $element['#title'] = $filter->getConfiguration()['label'];
    $element['#options'] = $options;
    if ($filter->allowsMultiple($field)) {
      $element['#type'] = 'checkboxes';
      if (empty($element['#default_value'])) {
        $element['#default_value'] = [];
      }
      elseif (!is_array($element['#default_value'])) {
        $element['#default_value'] = [$element['#default_value']];
      }
    }
    $element['#element_validate'] = [[get_class($this), 'validateElement']];
  }

  /**
   * Element validate callback.
   */
  public static function validateElement($element, FormStateInterface $form_state) {
    $value = $form_state->getValue($element['#parents']);
    if (is_array($value)) {
      $value = array_filter($value);
    }
    $form_state->setValue($element['#parents'], $value);
  }

}

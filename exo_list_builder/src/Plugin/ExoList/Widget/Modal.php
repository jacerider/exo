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
 *   id = "modal",
 *   label = @Translation("Modal"),
 *   description = @Translation("Modal widget."),
 *   provider = "exo_modal",
 * )
 */
class Modal extends ExoListWidgetBase implements ExoListWidgetValuesInterface {

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $options = $filter->getFilteredValueOptions($entity_list, $field);
    $multiple = $filter->allowsMultiple($field);

    $element['#type'] = 'exo_modal';
    $element['#use_close'] = FALSE;
    $element['#modal_settings'] = [
      'exo_preset' => 'aside_right',
    ];

    $element['value'] = [
      '#type' => $multiple ? 'checkboxes' : 'radios',
      '#options' => $options,
      '#default_value' => $element['#default_value'] ?: ($multiple ? [] : ''),
    ];

    $element['actions'] = [
      '#type' => 'actions',
    ];

    $element['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('See Results'),
      '#button_type' => 'primary',
    ];

    $element['actions']['reset'] = [
      '#type' => 'link',
      '#title' => $this->t('Clear'),
      '#url' => $entity_list->getHandler()->getOptionsUrl([], [$field['id']]),
    ];
    $element['#element_validate'] = [[get_class($this), 'validateElement']];
  }

  /**
   * Element validate callback.
   */
  public static function validateElement($element, FormStateInterface $form_state) {
    $multiple = $element['value']['#type'] === 'checkboxes';
    $value = $form_state->getValue($element['#parents']);
    $value = $value['value'];
    if ($multiple) {
      $value = array_filter($value);
    }
    $form_state->setValue($element['#parents'], $value);
  }

}

<?php

namespace Drupal\exo_filter\Plugin\ExoFilter\sort;

use Drupal\Core\Form\FormStateInterface;

/**
 * Default widget implementation.
 *
 * @ExoSort(
 *   id = "default",
 *   label = @Translation("Default"),
 * )
 */
class DefaultWidget extends SortWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function exposedFormAlter(array &$form, FormStateInterface $form_state) {
    parent::exposedFormAlter($form, $form_state);

    foreach ($this->sortElements as $element) {
      if (!empty($form[$element])) {
        $form[$element]['#type'] = 'select';
      }
    }
  }

}

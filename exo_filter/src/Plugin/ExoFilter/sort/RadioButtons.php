<?php

namespace Drupal\exo_filter\Plugin\ExoFilter\sort;

use Drupal\Core\Form\FormStateInterface;

/**
 * Radio Buttons sort widget implementation.
 *
 * @ExoSort(
 *   id = "buttons",
 *   label = @Translation("Radio Buttons"),
 * )
 */
class RadioButtons extends SortWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function exposedFormAlter(array &$form, FormStateInterface $form_state) {
    parent::exposedFormAlter($form, $form_state);

    foreach ($this->sortElements as $element) {
      if (!empty($form[$element])) {
        $form[$element]['#theme'] = 'bef_radios';
        $form[$element]['#type'] = 'radios';
      }
    }
  }

}

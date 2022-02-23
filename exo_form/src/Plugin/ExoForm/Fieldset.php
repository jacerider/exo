<?php

namespace Drupal\exo_form\Plugin\ExoForm;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "fieldset",
 *   label = @Translation("Fieldset"),
 *   element_types = {
 *     "fieldset",
 *   }
 * )
 */
class Fieldset extends Wrapper {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $this->enableWrapper();
    $element = parent::preRender($element);
    if (isset($element['#process'])) {
      if (!in_array('exo_form_element_process', $element['#process'])) {
        array_unshift($element['#process'], 'exo_form_element_process');
      }
    }
    return $element;
  }

}

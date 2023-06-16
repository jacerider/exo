<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "checkbox_tree",
 *   label = @Translation("Checkbox Tree"),
 *   element_types = {
 *     "checkbox_tree",
 *   }
 * )
 */
class CheckboxTree extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $this->disableExoOnElement($element);
    return $element;
  }

}

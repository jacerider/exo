<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "select_or_other_buttons",
 *   label = @Translation("Select or Other Buttons"),
 *   element_types = {
 *     "select_or_other_buttons",
 *   }
 * )
 */
class SelectOrOtherButtons extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    $element['#description_display'] = 'before';
  }

}

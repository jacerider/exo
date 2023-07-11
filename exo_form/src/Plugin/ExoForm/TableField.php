<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "tablefield",
 *   label = @Translation("TableField"),
 *   element_types = {
 *     "tablefield",
 *   }
 * )
 */
class TableField extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    foreach (Element::children($element['tablefield']['table']) as $i) {
      foreach (Element::children($element['tablefield']['table'][$i]) as $ii) {
        $element['tablefield']['table'][$i][$ii]['#exo_form_default'] = TRUE;
      }
    }
    return $element;
  }

}

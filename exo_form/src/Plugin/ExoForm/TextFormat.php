<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "text_format",
 *   label = @Translation("Text Format"),
 *   element_types = {
 *     "text_format",
 *   }
 * )
 */
class TextFormat extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['format']['#attributes']['class'][] = 'exo-filter-wrapper';
    $element['format']['#attributes']['style'] = 'display: none;';
    $element['format']['guidelines']['#access'] = FALSE;
    $element['format']['help']['#access'] = FALSE;
    if (!empty($element['#description'])) {
      $element['#description'] = [
        '#markup' => '<div class="description">' . $element['#description'] . '</div>',
      ];
    }
    return $element;
  }

}

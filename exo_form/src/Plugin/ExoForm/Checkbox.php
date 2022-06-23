<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "checkbox",
 *   label = @Translation("Checkbox"),
 *   element_types = {
 *     "checkbox",
 *   }
 * )
 */
class Checkbox extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#wrapper_attributes']['class'][] = 'exo-form-checkbox';
    $element['#wrapper_attributes']['class'][] = 'exo-form-checkbox-js';
    $element['#attached']['library'][] = 'exo_form/checkbox';
    // Blazy and modules like to add crap. Let's remove crap.
    if (isset($element['#field_suffix']) && $element['#field_suffix'] == '&nbsp;') {
      $element['#field_suffix'] = '';
    }
    if (isset($element['#title_display']) && $element['#title_display'] == 'invisible') {
      $element['#title_display'] = 'after';
      $element['#title'] = $this->t('<span class="visually-hidden">@title</span>', ['@title' => $element['#title']]);
    }
    if (!empty($element['#title'])) {
      if ($element['#title'] instanceof MarkupInterface) {
        $element['#title'] = Markup::create('<div class="exo-ripple"></div>' . $element['#title']);
      }
      else {
        $element['#title'] = '<div class="exo-ripple"></div>' . $element['#title'];
      }
    }
    else {
      $element['#title'] = '<div class="exo-ripple"></div>';
    }
    return $element;
  }

}

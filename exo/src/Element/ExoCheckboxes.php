<?php

namespace Drupal\exo\Element;

use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;

/**
 * Provides a form element for a set of checkboxs that can be render arrays.
 *
 * {@inheritdoc}
 *
 * @FormElement("exo_checkboxes")
 */
class ExoCheckboxes extends Checkboxes {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + [
      '#exo_style' => NULL,
    ];
  }

  /**
   * Expands a checkboxes element into individual checkbox elements.
   */
  public static function processCheckboxes(&$element, FormStateInterface $form_state, &$complete_form) {
    $renderer = \Drupal::service('renderer');
    if (count($element['#options']) > 0) {
      foreach ($element['#options'] as $key => &$choice) {
        if (is_array($choice)) {
          $choice = $renderer->render($choice);
        }
        else {
          $choice = Markup::create($choice);
        }
      }
    }
    $element['#theme_wrappers'][] = 'container';
    $element['#attributes']['class'][] = 'exo-element-options';
    $element['#exo_style'] = $element['#exo_style'] ?? 'stacked';
    if ($element['#exo_style'] != 'custom') {
      $element['#attributes']['class'][] = 'exo-' . $element['#exo_style'];
    }
    $element['#attached']['library'][] = 'exo/element.options';
    return parent::processCheckboxes($element, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   *
   * If exo_clean is passed in this field will not be wrapped in a fieldset.
   */
  public static function preRenderCompositeFormElement($element) {
    if (empty($element['#exo_clean'])) {
      $element = parent::preRenderCompositeFormElement($element);
    }
    return $element;
  }

}

<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Markup;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "select",
 *   label = @Translation("Select"),
 *   element_types = {
 *     "select",
 *     "webform_entity_select",
 *   }
 * )
 */
class Select extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $floatSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    // Ignore webform fields where select2 is enabled.
    if (!empty($element['#select2'])) {
      return $element;
    }
    $element = parent::preRender($element);
    $label = $element['#placeholder'] ?? $element['#title'] ?? NULL;
    $element['#wrapper_attributes']['class'][] = 'exo-form-select';
    $element['#wrapper_attributes']['class'][] = 'exo-form-select-js';
    $element['#attached']['library'][] = 'exo_form/select';
    if (isset($element['#multiple']) && $element['#multiple']) {
      $element['#attached']['library'][] = 'exo_form/checkbox';
    }

    // Place textfield to avoid javascript jumping.
    $element['#children_prefix']['wrapper']['#markup'] = '<div class="exo-form-select-wrapper exo-form-input exo-form-input-js form-item">';

    $element['#children_suffix']['carot']['#markup'] = Markup::create('<span class="exo-form-select-caret" role="presentation" aria-hidden="true">&#9660;</span>');
    $element['#children_suffix']['hidden']['#markup'] = Markup::create('<button type="button" class="exo-form-select-hidden" aria-haspopup="listbox" aria-label="Toggle Options">Toggle Options</button>');
    $element['#children_suffix']['text']['#markup'] = Markup::create('<input class="exo-form-input-item exo-form-input-item-js exo-form-select-trigger" readonly tabindex="-1" data-exo-auto-submit-exclude' . ($label ? ' aria-label="' . $label . '"' : '') . '></input>');
    $element['#children_suffix']['wrapper']['#markup'] = '</div>';

    if (!empty($element['#options_enabled'])) {
      $element['#attributes']['data-options-enabled'] = json_encode($element['#options_enabled']);
    }
    if (!empty($element['#options_disabled'])) {
      $element['#attributes']['data-options-disabled'] = json_encode($element['#options_disabled']);
    }
    if (!empty($element['#options_disabled_label'])) {
      $element['#attributes']['data-options-disabled-label'] = $element['#options_disabled_label'];
    }
    return $element;
  }

  /**
   * Check if element should be processed.
   *
   * @return bool
   *   Return TRUE if element should be processed.
   */
  public function applies($element) {
    // Support SHS module.
    if (!empty($element['#shs'])) {
      return FALSE;
    }
    return parent::applies($element);
  }

}

<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "text_or_entity_autocomplete",
 *   label = @Translation("Text or Entity Autocomplete"),
 *   element_types = {
 *     "text_or_entity_autocomplete",
 *   }
 * )
 */
class TextOrEntityAutocomplete extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $floatSupported = TRUE;

  /**
   * Wrapping attributes are sent to template via this attribute name.
   *
   * @var string
   */
  protected $featureAttributeKey = 'exo_form_inner_attributes';

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#exo_form_attributes']['class'][] = 'form-item';
    $element['#exo_form_attributes']['class'][] = 'exo-form-input';
    $element['#exo_form_attributes']['class'][] = 'exo-form-input-js';
    $element['#attributes']['class'][] = 'exo-form-input-item';
    $element['#attributes']['class'][] = 'exo-form-input-item-js';
    $element['#attached']['library'][] = 'exo_form/input';
    $element['#suffix'] = '<div class="exo-form-input-line"></div>';
    return $element;
  }

}

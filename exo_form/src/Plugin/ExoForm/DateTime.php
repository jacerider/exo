<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "datetime",
 *   label = @Translation("Date"),
 *   element_types = {
 *     "datetime",
 *     "exo_datetime",
 *     "exo_datetime_month_year",
 *   }
 * )
 */
class DateTime extends ExoFormBase {

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
  public function process(&$element) {
    parent::process($element);
    foreach (Element::children($element) as $key) {
      $child_element = &$element[$key];
      if (isset($child_element['#type']) && $child_element['#type'] == 'date') {
        $child_element['#datetime_child'] = TRUE;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element = parent::preRender($element);
    $element['#wrapper_attributes']['class'][] = 'exo-form-element';
    $element['#wrapper_attributes']['class'][] = 'exo-form-datetime';
    $element['#attributes']['class'][] = 'exo-form-inline';
    $element['#attributes']['class'][] = 'exo-form-input';
    $element['#theme_wrappers'] = ['form_element'];
    $element['date']['#attributes']['placeholder'] = t('Date');
    $element['time']['#attributes']['placeholder'] = t('Time');
    if ($this->getSetting('style') === 'float_inside'&& !empty($this->floatSupported)) {
      $element['#' . $this->featureAttributeKey]['class'][] = 'exo-form-input';
      $element['#' . $this->featureAttributeKey]['class'][] = 'force-active';
      $element['date']['#exo_form_element_attributes']['class'][] = 'exo-form-element-float-inside';
      $element['date']['#exo_form_element_attributes']['class'][] = 'has-label';
      $element['date']['#exo_form_element_attributes']['class'][] = 'value';
    }
    return $element;
  }

}

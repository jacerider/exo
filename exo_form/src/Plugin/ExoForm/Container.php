<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\exo_form\Plugin\ExoFormBase;
use Drupal\Core\Render\Element;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "container",
 *   label = @Translation("Container"),
 *   element_types = {
 *     "container",
 *   }
 * )
 */
class Container extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->disableWrapper();
  }

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    $element['#exo_form_is_container'] = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    // If we have no visible children, we hide the container.
    $empty = !Element::getVisibleChildren($element);
    if ($empty && isset($element['widget']['#type'])) {
      return [];
    }
    if ($empty && empty($element['#id']) && empty($element['#attributes']['id'])) {
      return [];
    }
    $element['#exo_form_element_attributes']['class'][] = 'exo-form-element';
    $element['#exo_form_element_attributes']['class'][] = 'exo-form-element-js';
    $element['#exo_form_attributes']['class'][] = 'exo-form-container';
    $element['#exo_form_attributes']['class'][] = 'exo-form-container-js';
    // When a nested element is grouped, we flag this container for hiding.
    if (!empty($element['widget'][0]['#group'])) {
      $element['#exo_form_element_attributes']['class'][] = 'exo-form-container-hide';
    }
    if (!$this->hasVisibleChildren($element) && empty($element['#group'])) {
      $element['#exo_form_attributes']['class'][] = 'exo-form-container-hide';
    }

    // IEF module support.
    if (isset($element['ief_add_save']) || isset($element['ief_edit_save']) || isset($element['ief_reference_save'])) {
      $this->enableWrapper();
      $element['#attributes']['class'][] = 'exo-form-inline';
      $element['#attributes']['class'][] = 'exo-form-inline-compact';
    }

    // Special handling for commerce_entity_select type fields.
    if (isset($element['widget']['target_id']['#type']) && $element['widget']['target_id']['#type'] == 'commerce_entity_select') {
      if (isset($element['widget']['target_id']['value']['#type']) && $element['widget']['target_id']['value']['#type'] == 'hidden') {
        $element['#exo_form_attributes']['class'][] = 'exo-form-container-hide';
      }
    }

    return parent::preRender($element);
  }

  /**
   * {@inheritdoc}
   */
  protected function hasVisibleChildren($element) {
    $visible = FALSE;
    if (isset($element['#type']) && !in_array($element['#type'], ['value', 'container'])) {
      $visible = TRUE;
    }
    foreach (Element::children($element) as $key) {
      $child_element = $element[$key];
      if ($this->hasVisibleChildren($child_element)) {
        $visible = TRUE;
      }
    }
    return $visible;
  }

}

<?php

namespace Drupal\exo_form\Plugin\ExoForm;

use Drupal\Core\Render\Element;
use Drupal\exo_form\Plugin\ExoFormBase;

/**
 * Provides a plugin for element type(s).
 *
 * @ExoForm(
 *   id = "managed_file",
 *   label = @Translation("Managed File"),
 *   element_types = {
 *     "managed_file",
 *     "exo_config_image",
 *   }
 * )
 */
class ManagedFile extends ExoFormBase {

  /**
   * {@inheritdoc}
   */
  protected $intersectSupported = TRUE;

  /**
   * {@inheritdoc}
   */
  public function process(&$element) {
    parent::process($element);
    foreach ($element['#pre_render'] as $key => $callback) {
      if (is_array($callback) && $callback[0] === 'Drupal\claro\ClaroPreRender') {
        unset($element['#pre_render'][$key]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preRender($element) {
    $element['upload']['#is_wrapped'] = TRUE;
    $element['#exo_form_attributes']['class'][] = 'exo-form-managed-file';
    $element['#exo_form_attributes']['class'][] = 'exo-form-managed-file-js';
    if ($element['#type'] == 'managed_file') {
      foreach (Element::children($element) as $key) {
        if (substr($key, 0, 5) === 'file_') {
          $element[$key]['filename']['#attributes']['class'][] = 'exo-form-file-input';
        }
      }
    }
    return parent::preRender($element);
  }

}

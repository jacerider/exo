<?php

namespace Drupal\exo_filter\Plugin\ExoFilter\filter;

use Drupal\exo_filter\Plugin\ExoFilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'links' formatter.
 *
 * @ExoFilter(
 *   id = "links",
 *   label = @Translation("Links"),
 *   field_types = {
 *     "list_field",
 *     "taxonomy_index_tid",
 *     "search_api_term",
 *     "search_api_options",
 *     "exo_filter_entity_reference",
 *   }
 * )
 */
class Links extends ExoFilterBase {

  /**
   * {@inheritdoc}
   */
  public function exposedElementAlter(&$element, FormStateInterface $element_state, $context) {
    $element['#type'] = 'exo_checkboxes';
    $element['#exo_style'] = 'custom';
    if (empty($element['#multiple'])) {
      $element['#type'] = 'exo_radios';
    }
  }

}

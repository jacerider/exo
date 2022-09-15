<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterInterface;
use Drupal\exo_list_builder\Plugin\ExoListWidgetBase;
use Drupal\exo_list_builder\Plugin\ExoListWidgetValuesInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListWidget(
 *   id = "autocomplete",
 *   label = @Translation("Autocomplete"),
 *   description = @Translation("Autocomplete widget."),
 * )
 */
class Autocomplete extends ExoListWidgetBase implements ExoListWidgetValuesInterface {

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    if (!$entity_list->isNew()) {
      $element['#multiple'] = $filter->allowsMultiple($field);
      $element += [
        '#autocomplete_route_name' => 'exo_list_builder.autocomplete',
        '#autocomplete_route_parameters' => [
          'exo_entity_list' => $entity_list->id(),
          'field_id' => $field['id'],
        ],
        '#element_validate' => [
          [$this, 'validateElementAutocomplete'],
        ],
      ];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function validateElementAutocomplete($element, FormStateInterface $form_state) {
    if (!empty($element['#multiple'])) {
      $value = $form_state->getValue($element['#parents']);
      $value = array_map('trim', explode(',', $value));
      $form_state->setValue($element['#parents'], $value);
    }
  }

}

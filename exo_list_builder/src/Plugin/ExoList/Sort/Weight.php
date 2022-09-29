<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Sort;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListSortBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListSort(
 *   id = "weight",
 *   label = @Translation("Weight"),
 *   description = @Translation("Use weight sort order."),
 *   weight = -100,
 *   entity_type = {},
 *   bundle = {},
 * )
 */
class Weight extends ExoListSortBase {

  /**
   * {@inheritdoc}
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL) {
    $fields = $entity_list->getAvailableFields();
    if (isset($fields['weight'])) {
      $query->sort('weight', $direction);
    }
    elseif (isset($fields['field_weight'])) {
      $query->sort('field_weight.value', $direction);
    }
    if ($title_key = $entity_list->getTargetEntityType()->getKey('label')) {
      $query->sort($title_key, $direction);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityListInterface $exo_list) {
    $fields = $exo_list->getAvailableFields();
    if (isset($fields['weight']) || isset($field['field_weight'])) {
      return TRUE;
    }
    return FALSE;
  }

}

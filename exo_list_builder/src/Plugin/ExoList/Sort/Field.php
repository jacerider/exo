<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Sort;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListSortBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListSort(
 *   id = "field",
 *   label = @Translation("Field"),
 *   description = @Translation("Sort by enabled list field."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 *   no_ui = true,
 * )
 */
class Field extends ExoListSortBase {

  /**
   * {@inheritdoc}
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL, $field_name = NULL) {
    if (!empty($field_name)) {
      $field = $entity_list->getField($field_name);
      if (empty($field['sort_field'])) {
        // Field is not valid. Run away!
        return;
      }
      if (empty($direction)) {
        $direction = $field['view']['sort'];
      }
      $query->sort($field['sort_field'], $direction);
    }
  }

}

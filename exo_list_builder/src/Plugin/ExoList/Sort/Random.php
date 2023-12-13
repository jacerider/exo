<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Sort;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListSortBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListSort(
 *   id = "random",
 *   label = @Translation("Random"),
 *   description = @Translation("Use random sort order."),
 *   entity_type = {},
 *   bundle = {},
 * )
 */
class Random extends ExoListSortBase {

  /**
   * {@inheritdoc}
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL) {
    $query->addTag('entity_list_sort_by_random');
  }

}

<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Sort;

use Drupal\exo_list_builder\Plugin\ExoListSortBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListSort(
 *   id = "default",
 *   label = @Translation("Default"),
 *   description = @Translation("Use default sort order."),
 *   weight = -100,
 *   entity_type = {},
 *   bundle = {},
 * )
 */
class None extends ExoListSortBase {

}

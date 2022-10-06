<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Widget;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterInterface;
use Drupal\exo_list_builder\Plugin\ExoListWidgetBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListWidget(
 *   id = "number",
 *   label = @Translation("Number"),
 *   description = @Translation("Number widget."),
 * )
 */
class Number extends ExoListWidgetBase {

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $element['#type'] = 'number';
  }

}

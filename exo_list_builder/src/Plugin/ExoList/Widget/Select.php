<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Widget;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterInterface;
use Drupal\exo_list_builder\Plugin\ExoListWidgetBase;
use Drupal\exo_list_builder\Plugin\ExoListWidgetValuesInterface;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListWidget(
 *   id = "select",
 *   label = @Translation("Dropdown"),
 *   description = @Translation("Select widget."),
 * )
 */
class Select extends ExoListWidgetBase implements ExoListWidgetValuesInterface {

  /**
   * {@inheritDoc}
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field) {
    $options = $filter->getFilteredValueOptions($entity_list, $field);
    $element['#type'] = 'select';
    $element['#options'] = ['' => $this->t('- All -')] + $options;
    $element['#multiple'] = $filter->allowsMultiple($field);
    $element['#access'] = count($options) > 1;
  }

}

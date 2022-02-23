<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list actions.
 */
interface ExoListFieldValuesInterface {

  /**
   * Get the available field values.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   * @param string $input
   *   The input.
   *
   * @return string[]
   *   The field values.
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL);

}

<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Defines an interface for exo list actions.
 */
interface ExoListFieldPropertyInterface {

  /**
   * Get the property options to export.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   An array of property.
   */
  public function getPropertyOptions(FieldDefinitionInterface $field_definition);

}

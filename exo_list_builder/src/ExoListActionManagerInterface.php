<?php

namespace Drupal\exo_list_builder;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Define exo list action manager interface.
 */
interface ExoListActionManagerInterface extends PluginManagerInterface {

  /**
   * Get definition options.
   *
   * @return array
   *   An array of definition options.
   */
  public function getOptions();

  /**
   * Get definition options for a given field type.
   *
   * @param string $entity_type
   *   The entity type id.
   * @param string $bundle
   *   The bundle id.
   *
   * @return array
   *   An array of definition options.
   */
  public function getFieldOptions($entity_type = NULL, $bundle = NULL);

}

<?php

namespace Drupal\exo_list_builder;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines an interface for exo_list_field managers.
 */
interface ExoListFieldManagerInterface extends PluginManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function getFields($entity_type = NULL, $bundle = NULL);

}

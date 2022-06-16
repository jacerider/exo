<?php

namespace Drupal\exo_list_builder;

use Drupal\Component\Plugin\Discovery\CachedDiscoveryInterface;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;

/**
 * Define exo list sort manager interface.
 */
interface ExoListSortManagerInterface extends PluginManagerInterface, CachedDiscoveryInterface, CacheableDependencyInterface {

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

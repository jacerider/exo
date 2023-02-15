<?php

namespace Drupal\exo;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Defines the interface for eXo theme provider plugin managers.
 */
interface ExoThemeProviderPluginManagerInterface extends PluginManagerInterface {

  /**
   * Get all definitions.
   *
   * @return array
   *   An array of all definitions.
   */
  public function getAllDefinitions();

}

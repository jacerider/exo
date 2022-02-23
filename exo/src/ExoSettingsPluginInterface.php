<?php

namespace Drupal\exo;

/**
 * Defines the interface for eXo settings.
 */
interface ExoSettingsPluginInterface {

  /**
   * Return the plugin manager.
   *
   * @return \Drupal\Component\Plugin\PluginManagerInterface
   *   The plugin manager.
   */
  public function getPluginManager();

  /**
   * Create a local instance of settings for a plugin.
   *
   * @param string $plugin_id
   *   The plugin id.
   * @param array $settings
   *   An array of local settings.
   *
   * @return \Drupal\exo\ExoSettingsInstanceInterface
   *   An instance of the local settings.
   */
  public function createPluginInstance($plugin_id, array $settings = []);

  /**
   * Create a local instance of settings with plugin selection.
   *
   * @param array $settings
   *   An array of local settings.
   * @param string $id
   *   An optional instance id. Useful when showing multiple instances of the
   *   same type on the same page.
   *
   * @return \Drupal\exo\ExoSettingsInstanceInterface
   *   An instance of the local settings.
   */
  public function createPluginSelectInstance(array $settings = [], $id = NULL);

}

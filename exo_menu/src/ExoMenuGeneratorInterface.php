<?php

namespace Drupal\exo_menu;

/**
 * Defines an interface for generating eXo menus.
 */
interface ExoMenuGeneratorInterface {

  /**
   * Generate an eXo menu.
   *
   * @param string $id
   *   A unique id.
   * @param string $style
   *   The menu style.
   * @param string[] $menu_ids
   *   The menu ids.
   * @param array $settings
   *   An array of settings.
   *
   * @return \Drupal\exo_menu\ExoMenuInterface
   *   An eXo menu.
   */
  public function generate($id, $style = 'tree', array $menu_ids = [], array $settings = []);

}

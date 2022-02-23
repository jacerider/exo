<?php

namespace Drupal\exo_menu_component\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Menu Component type entities.
 */
interface MenuComponentTypeInterface extends ConfigEntityInterface {

  /**
   * Gets the target menu.
   *
   * @return array
   *   The menus targeted by the entity.
   */
  public function getTargetMenu();

  /**
   * Sets the target menus.
   *
   * @param array $target_menu
   *   The target menus.
   *
   * @return \Drupal\simple_megamenu\Entity\SimpleMegaMenuTypeInterface
   *   The SimpleMegaMenuType.
   */
  public function setTargetMenu(array $target_menu);

}

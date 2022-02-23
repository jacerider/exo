<?php

namespace Drupal\exo_menu_component;

use Drupal\exo_menu_component\Entity\MenuComponentInterface;
use Drupal\exo_menu_component\Entity\MenuComponentTypeInterface;

/**
 * Interface MenuComponentManagerInterface.
 *
 * @package Drupal\exo_menu_component
 */
interface MenuComponentManagerInterface {

  /**
   * Gets the menus targeted by a specific Simple mega menu type.
   *
   * @param \Drupal\exo_menu_component\Entity\MenuComponentTypeInterface $entity
   *   The Simple mega menu type entity.
   *
   * @return array
   *   The menus targeted by the config entity.
   */
  public function getTargetMenus(MenuComponentTypeInterface $entity);

  /**
   * Get MenuComponentType entities which target a menu.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return array
   *   An array keyed by the MenuComponentType id and with the label as value.
   *   Otherwise, an empty array.
   */
  public function getMegaMenuTypeWhichTargetMenu($menu_name);

  /**
   * Is the menu is referenced by a MenuComponentType entity.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return bool
   *   TRUE, if the menu is targeted by a MenuComponentType entity.
   *   Otherwise, FALSE.
   */
  public function menuIsTargetedByMegaMenuType($menu_name);

  /**
   * Get a MenuComponentType entity.
   *
   * @param string $id
   *   The MenuComponentType id.
   *
   * @return \Drupal\exo_menu_component\Entity\MenuComponentTypeInterface
   *   The MenuComponentTypeInterface entity.
   */
  public function getMenuComponentType($id);

  /**
   * Get a MenuComponent entity.
   *
   * @param string $id
   *   The MenuComponent id.
   *
   * @return \Drupal\exo_menu_component\Entity\MenuComponentInterface
   *   The MenuComponentInterface entity.
   */
  public function getMenuComponent($id);

  /**
   * View a MenuComponent entity.
   *
   * @param string $id
   *   The MenuComponent id.
   * @param string $view_mode
   *   The view mode.
   *
   * @return mixed
   *   A render array for the component.
   */
  public function viewMenuComponent($id, $view_mode = 'default');

  /**
   * Create a menu component entity.
   *
   * @param string $type
   *   The menu component type id.
   *
   * @return \Drupal\exo_menu_component\Entity\MenuComponentInterface
   *   The MenuComponentInterface entity.
   */
  public function createMenuComponent($type);

  /**
   * Get form object for menu component entity.
   *
   * @param \Drupal\exo_menu_component\Entity\MenuComponentInterface $menu_component
   *   The menu component.
   *
   * @return \Drupal\Core\Entity\EntityFormInterface
   *   The entity form interface.
   */
  public function getFormObject(MenuComponentInterface $menu_component);

}

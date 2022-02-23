<?php

namespace Drupal\exo_menu_component\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Menu Component type entity.
 *
 * @ConfigEntityType(
 *   id = "exo_menu_component_type",
 *   label = @Translation("Menu Component Type"),
 *   label_collection = @Translation("Menu Component Types"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\exo_menu_component\MenuComponentTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\exo_menu_component\Form\MenuComponentTypeForm",
 *       "edit" = "Drupal\exo_menu_component\Form\MenuComponentTypeForm",
 *       "delete" = "Drupal\exo_menu_component\Form\MenuComponentTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\exo_menu_component\MenuComponentTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "exo_menu_component_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "exo_menu_component",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "targetMenu",
 *   },
 *   links = {
 *     "add-form" = "/admin/config/exo/menu-component-type/add",
 *     "edit-form" = "/admin/config/exo/menu-component-type/{exo_menu_component_type}/edit",
 *     "delete-form" = "/admin/config/exo/menu-component-type/{exo_menu_component_type}/delete",
 *     "collection" = "/admin/config/exo/menu-component-type"
 *   }
 * )
 */
class MenuComponentType extends ConfigEntityBundleBase implements MenuComponentTypeInterface {

  /**
   * The Menu Component type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Menu Component type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The target menus this mega menu type is used for.
   *
   * @var array
   */
  protected $targetMenu = [];

  /**
   * {@inheritdoc}
   */
  public function getTargetMenu() {
    return $this->targetMenu;
  }

  /**
   * {@inheritdoc}
   */
  public function setTargetMenu($target_menu) {
    $this->targetMenu = $target_menu;
    return $this;
  }

}

<?php

namespace Drupal\exo_menu_component\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Menu Components.
 *
 * @ingroup exo_menu_component
 */
interface MenuComponentInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Menu Component creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Menu Component.
   */
  public function getCreatedTime();

  /**
   * Sets the Menu Component creation timestamp.
   *
   * @param int $timestamp
   *   The Menu Component creation timestamp.
   *
   * @return \Drupal\exo_menu_component\Entity\MenuComponentInterface
   *   The called Menu Component entity.
   */
  public function setCreatedTime($timestamp);

}

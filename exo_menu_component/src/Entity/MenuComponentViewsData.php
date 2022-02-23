<?php

namespace Drupal\exo_menu_component\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Menu Components.
 */
class MenuComponentViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.
    return $data;
  }

}

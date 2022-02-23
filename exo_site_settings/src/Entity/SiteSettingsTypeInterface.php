<?php

namespace Drupal\exo_site_settings\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining config page type entities.
 */
interface SiteSettingsTypeInterface extends ConfigEntityInterface {

  /**
   * Check if settings type is aggregate.
   *
   * @return bool
   *   TRUE if aggregate.
   */
  public function isAggregate();

}

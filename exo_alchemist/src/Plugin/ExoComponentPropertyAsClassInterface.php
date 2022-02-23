<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Defines an interface for Component that expose themselves as classes.
 */
interface ExoComponentPropertyAsClassInterface extends ExoComponentPropertyOptionsInterface, ExoComponentPropertyInterface {

  /**
   * Set the class prefix.
   *
   * @param string $prefix
   *   The prefix.
   *
   * @return $this
   */
  public function setPrefix($prefix);

}

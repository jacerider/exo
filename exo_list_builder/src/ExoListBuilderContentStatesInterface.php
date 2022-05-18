<?php

namespace Drupal\exo_list_builder;

/**
 * Defines an interface to build entity listings.
 */
interface ExoListBuilderContentStatesInterface extends ExoListBuilderInterface {

  /**
   * Get default state label.
   *
   * @return string
   *   The default state label.
   */
  public function getDefaultStateLabel();

  /**
   * Get default state icon.
   *
   * @return string
   *   The default state icon.
   */
  public function getDefaultStateIcon();

  /**
   * Get state definitions.
   *
   * @return array
   *   An array of states.
   */
  public function getStates();

  /**
   * Get the current state definition.
   *
   * @return array
   *   The state definition.
   */
  public function getState();

}

<?php

namespace Drupal\exo_alchemist\Plugin;

/**
 * Defines an interface for Component Property plugins.
 */
interface ExoComponentPropertyOptionsInterface {

  /**
   * Get an array of all options.
   *
   * Key should be class key and value should be class label.
   *
   * @return array
   *   An array of options.
   */
  public function getOptions();

  /**
   * Get an array of filtered options.
   *
   * @return array
   *   An array of options.
   */
  public function getFilteredOptions();

  /**
   * Get an array of options that are formatted for final use.
   *
   * @return array
   *   An array of options.
   */
  public function getFormattedOptions();

}

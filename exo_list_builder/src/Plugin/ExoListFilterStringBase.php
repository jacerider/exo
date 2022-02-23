<?php

namespace Drupal\exo_list_builder\Plugin;

/**
 * Base class for eXo list filters.
 */
abstract class ExoListFilterStringBase extends ExoListFilterMatchBase {

  /**
   * Returns the options for the match operator.
   *
   * @return array
   *   List of options.
   */
  protected function getMatchOperatorOptions() {
    return [
      '=' => t('Equals'),
      'STARTS_WITH' => t('Starts with'),
      'CONTAINS' => t('Contains'),
      'ENDS_WITH' => t('Ends with'),
    ];
  }

}

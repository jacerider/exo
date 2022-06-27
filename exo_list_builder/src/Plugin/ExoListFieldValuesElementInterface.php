<?php

namespace Drupal\exo_list_builder\Plugin;

/**
 * Defines an interface for exo list actions.
 */
interface ExoListFieldValuesElementInterface {

  /**
   * Get the parents of the form element to convert to a select or autocomplete.
   *
   * @return array
   *   The parents of the form element.
   */
  public function getValuesParents();

}

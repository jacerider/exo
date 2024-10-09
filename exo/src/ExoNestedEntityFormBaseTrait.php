<?php

namespace Drupal\exo;

/**
 * Provides a helper to for nesting entity forms.
 *
 * @internal
 */
trait ExoNestedEntityFormBaseTrait {

  /**
   * The inner form key.
   * 
   * Used by ExoNestedEntityFormTrait. Should be applied to base form.
   * 
   * @param string
   *   The inner form key.
   */
  public $innerFormKey = '';

  /**
   * The inner form parents.
   * 
   * Used by ExoNestedEntityFormTrait. Should be applied to base form.
   * 
   * @param array
   *   The inner form parents.
   */
  public $innerFormParents = [];

}

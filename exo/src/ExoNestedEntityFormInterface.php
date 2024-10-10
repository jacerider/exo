<?php

namespace Drupal\exo;

/**
 * Defines an object which is used to store instance settings.
 */
interface ExoNestedEntityFormInterface {

  /**
   * Gets the inner form key.
   * 
   * @param string $key
   * 
   * @return string $key
   *  Inner form key.
   */
  public function getInnerFormKey($key);

  /**
   * Gets the inner form parents.
   * 
   * @param string $key
   * 
   * @return array $key
   *  Inner form parents key.
   */
  public function getInnerFormParents($key);

  /**
   * Sets the inner form key.
   * 
   * @param string $key
   */
  public function setInnerFormKey($key);

  /**
   * Sets the inner form parents.
   * 
   * @param string $key
   * @param array $parents
   */
  public function setInnerFormParents($key, $parents);
}

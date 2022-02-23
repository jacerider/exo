<?php

namespace Drupal\exo_icon;

/**
 * Defines an object which can be rendered by the Render API.
 */
interface ExoIconInterface {

  /**
   * Get the icon id.
   *
   * @return string
   *   The icon id.
   */
  public function getId();

  /**
   * Get icon as render array.
   *
   * @return array
   *   A render array.
   */
  public function toRenderable();

  /**
   * Add class.
   *
   * @param string|array $classes
   *   CSS classes to add to the class attribute array.
   *
   * @return $this
   */
  public function addClass($classes);

  /**
   * Removes argument values from array of existing CSS classes.
   *
   * @param string|array $classes
   *   CSS classes to remove from the class attribute array.
   *
   * @return $this
   */
  public function removeClass($classes);

  /**
   * Get the attributes.
   *
   * @param array $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   */
  public function setAttributes(array $attributes);

  /**
   * Sets values for an attribute key.
   *
   * @param string $attribute
   *   Name of the attribute.
   * @param string|array $value
   *   Value(s) to set for the given attribute key.
   *
   * @return $this
   */
  public function setAttribute($attribute, $value);

  /**
   * Get the attributes.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The attributes.
   */
  public function getAttributes();

  /**
   * Add class.
   *
   * @param string|array $classes
   *   CSS classes to add to the class attribute array.
   *
   * @return $this
   */
  public function addIconClass($classes);

  /**
   * Removes argument values from array of existing CSS classes.
   *
   * @param string|array $classes
   *   CSS classes to remove from the class attribute array.
   *
   * @return $this
   */
  public function removeIconClass($classes);

  /**
   * Get the attributes.
   *
   * @param array $attributes
   *   An associative array of key-value pairs to be converted to attributes.
   */
  public function setIconAttributes(array $attributes);

  /**
   * Sets values for an attribute key.
   *
   * @param string $attribute
   *   Name of the attribute.
   * @param string|array $value
   *   Value(s) to set for the given attribute key.
   *
   * @return $this
   */
  public function setIconAttribute($attribute, $value);

  /**
   * Get the attributes.
   *
   * @return \Drupal\Core\Template\Attribute
   *   The attributes.
   */
  public function getIconAttributes();

}

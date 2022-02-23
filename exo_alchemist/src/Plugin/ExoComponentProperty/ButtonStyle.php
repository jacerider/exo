<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'button_style' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "button_style",
 *   label = @Translation("Button Style"),
 * )
 */
class ButtonStyle extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $options = [
    'primary' => 'Primary',
    'secondary' => 'Secondary',
  ];

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    $attributes = parent::asAttributeArray();
    return $attributes;
  }

}

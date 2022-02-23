<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'breakpoint' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "breakpoint",
 *   label = @Translation("Breakpoint"),
 * )
 */
class Breakpoint extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_checkboxes';

  /**
   * {@inheritdoc}
   */
  protected $multiple = TRUE;

  /**
   * {@inheritdoc}
   */
  protected $options = [
    'small' => 'Small',
    'medium' => 'Medium',
    'large' => 'Large',
    'xlarge' => 'Extra Large',
  ];

}

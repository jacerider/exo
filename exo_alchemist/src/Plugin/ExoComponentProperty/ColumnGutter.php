<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

/**
 * A 'column_gutter' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "column_gutter",
 *   label = @Translation("Column Gutter"),
 * )
 */
class ColumnGutter extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  protected $type = 'exo_radios_slider';

  /**
   * {@inheritdoc}
   */
  protected $options = [
    '0' => '0',
    '10' => '10',
    '20' => '20',
    '30' => '30',
    '60' => '60',
    '90' => '90',
    '120' => '120',
  ];

}

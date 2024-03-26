<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

/**
 * A layout builder 'section' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "section_column_one_large",
 *   label = @Translation("Section: One Column (Large)"),
 * )
 */
class SectionColumnOneLarge extends SectionBase {

  /**
   * The layout id.
   *
   * @var string
   */
  protected $layoutId = 'layout_nowrap';

  /**
   * {@inheritdoc}
   */
  protected function getRegionSizes() {
    return [
      'first' => 'large',
    ];
  }

}

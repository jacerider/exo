<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;

/**
 * A 'exo_theme_color' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "exo_theme_color",
 *   label = @Translation("eXo Theme Color")
 * )
 */
class ExoThemeColor extends Text {

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return [
      'type' => 'exo_attribute',
    ] + parent::getStorageConfig();
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'exo_theme_color',
      'settings' => [
        'exo_style' => 'grid-compact',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'value' => $this->t('The theme color name.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'value' => 'theme-primary',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [
      'value' => $item->value,
    ];
  }

}

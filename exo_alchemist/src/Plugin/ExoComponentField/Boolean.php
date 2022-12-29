<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'boolean' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "boolean",
 *   label = @Translation("Boolean")
 * )
 */
class Boolean extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    return [
      'type' => 'boolean',
      'settings' => [
        'on_label' => new TranslatableMarkup('Yes'),
        'off_label' => new TranslatableMarkup('No'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'boolean_checkbox',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'value' => $this->t('The boolean value.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    return [
      'value' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewValue(FieldItemInterface $item, $delta, array $contexts) {
    return [
      'value' => !empty($item->value),
    ];
  }

}

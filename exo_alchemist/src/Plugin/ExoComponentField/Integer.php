<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentField;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentFieldFieldableBase;

/**
 * A 'integer' adapter for exo components.
 *
 * @ExoComponentField(
 *   id = "integer",
 *   label = @Translation("Integer")
 * )
 */
class Integer extends ExoComponentFieldFieldableBase {

  /**
   * {@inheritdoc}
   */
  public function getStorageConfig() {
    $field = $this->getFieldDefinition();
    return [
      'type' => 'integer',
      'settings' => [
        'min' => $field->getAdditionalValue('min') ?: '',
        'max' => $field->getAdditionalValue('max') ?: '',
        'prefix' => $field->getAdditionalValue('prefix') ?: '',
        'suffix' => $field->getAdditionalValue('suffix') ?: '',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getWidgetConfig() {
    return [
      'type' => 'number',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'value' => $this->t('The number value.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultValue($delta = 0) {
    $field = $this->getFieldDefinition();
    return [
      'value' => $this->t('Placeholder for @label', [
        '@label' => strtolower($field->getLabel()),
      ]),
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

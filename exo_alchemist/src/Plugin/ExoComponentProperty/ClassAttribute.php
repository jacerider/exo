<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyAsClassInterface;

/**
 * A 'class attribute' adapter for exo components.
 */
abstract class ClassAttribute extends Value implements ExoComponentPropertyAsClassInterface {

  /**
   * The element type.
   *
   * @var string
   */
  protected $type = 'exo_radios';

  /**
   * An array of options.
   *
   * Key should be class key and value should be class label.
   *
   * @var array
   */
  protected $options;

  /**
   * The class prefix.
   *
   * @var string
   */
  protected $prefix = 'exo-modifier';

  /**
   * Supports multiple values.
   *
   * @var bool
   */
  protected $multiple = FALSE;

  /**
   * Get element type.
   *
   * @return string
   *   A valid element type.
   */
  protected function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  public function setPrefix($prefix) {
    $this->prefix = $prefix;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return $this->allowsMultiple() ? [] : key($this->getFilteredOptions());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedOptions() {
    $formatted = [];
    foreach ($this->getFilteredOptions() as $key => $label) {
      $name = $this->getHtmlClassName($this->getPropertyDefinition()->getAlias(), $key);
      $values = is_array($name) ? $name : [$name];
      $values = array_map(function ($value) {
        return Html::getClass($value);
      }, $values);
      $formatted[$key] = implode(' ', $values);
    }
    return $formatted;
  }

  /**
   * The class name.
   *
   * @param string $name
   *   The property name.
   * @param string $value
   *   The property value.
   *
   * @return string
   *   The class to use.
   */
  protected function getHtmlClassName($name, $value) {
    return $this->prefix . '--' . $name . '-' . $value;
  }

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    $attributes = [];
    $formatted_options = $this->getFormattedOptions();
    $value = $this->getValue();
    if ($this->allowsMultiple()) {
      foreach ($value as $val) {
        if (!empty($formatted_options[$val])) {
          $attributes['class'][] = $formatted_options[$val];
        }
      }
    }
    elseif (!empty($formatted_options[$value])) {
      $attributes['class'][] = $formatted_options[$value];
    }
    return $attributes;
  }

}

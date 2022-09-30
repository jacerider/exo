<?php

namespace Drupal\exo_alchemist\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for Component Property plugins.
 */
abstract class ExoComponentPropertyBase extends PluginBase implements ExoComponentPropertyInterface {

  /**
   * Supports multiple values.
   *
   * @var bool
   */
  protected $multiple = FALSE;

  /**
   * Returns the property definition.
   *
   * @return \Drupal\exo_alchemist\Definition\ExoComponentDefinitionModifierProperty
   *   The property definition.
   */
  protected function getPropertyDefinition() {
    return !empty($this->configuration['property']) ? $this->configuration['property'] : NULL;
  }

  /**
   * Returns the value.
   *
   * @return mixed
   *   The value of the property.
   */
  protected function getValue() {
    return $this->configuration['value'] ?? $this->getPropertyDefinition()->getDefault();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function asAttributeArray() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function allowsMultiple() {
    return $this->multiple === TRUE;
  }

}

<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_alchemist\Plugin\ExoComponentPropertyBase;

/**
 * A 'value' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "value",
 *   label = @Translation("Value"),
 * )
 */
class Value extends ExoComponentPropertyBase {

  /**
   * Get element type.
   *
   * @return string
   *   A valid element type.
   */
  protected function getType() {
    return $this->getPropertyDefinition()->getAdditionalValue('property_widget') ?: 'select';
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = [];
    if ($definitionOptions = $this->getPropertyDefinition()->getAdditionalValue('property_options')) {
      if (key($definitionOptions) === 0) {
        $options = array_combine($definitionOptions, $definitionOptions);
      }
      else {
        $options = $definitionOptions;
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilteredOptions() {
    $options = $this->getOptions();
    if ($definition = $this->getPropertyDefinition()) {
      $none = !empty($options['_none']) ? $options['_none'] : NULL;
      if ($include = $definition->getInclude()) {
        $options = array_intersect_key($options, array_flip($include));
      }
      if ($exclude = $definition->getExclude()) {
        $options = array_diff_key($options, array_flip($exclude));
      }
      if (!empty($none) && !isset($options['_none'])) {
        $options = ['_none' => $none] + $options;
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $definition = $this->getPropertyDefinition();
    $required = $definition->isRequired();
    $form['#type'] = $this->getType();
    $form['#required'] = $required;
    if ($options = $this->getFilteredOptions()) {
      $form['#options'] = $options;
      if ($required) {
        unset($form['#options']['_none']);
      }
    }
    $form['#default_value'] = $this->getValue();
    switch ($form['#type']) {
      case 'exo_radios':
      case 'exo_checkboxes':
        $form['#exo_style'] = 'grid';
        break;

      case 'exo_radios_slider':
        $form['#pips'] = TRUE;
        break;
    }
    return $form;
  }

}

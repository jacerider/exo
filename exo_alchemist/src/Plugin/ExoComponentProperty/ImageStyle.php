<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentProperty;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\exo_alchemist\Definition\ExoComponentDefinition;

/**
 * A 'image style' adapter for exo components.
 *
 * @ExoComponentProperty(
 *   id = "image_style",
 *   label = @Translation("Image Style"),
 *   alter = true,
 *   provider = "exo_imagine",
 * )
 */
class ImageStyle extends ClassAttribute {

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    if (!isset($this->options)) {
      $this->options = [
        '_none' => t('Default'),
      ];
      if ($definition = $this->getPropertyDefinition()) {
        $settings = $definition->getAdditionalValue('options');
        foreach ($settings as $key => $setting) {
          $this->options[$key] = $setting['label'] ?? $key;
        }
      }
    }
    return parent::getOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function alter(ExoComponentDefinition $definition, ContentEntityInterface $entity, array $contexts) {
    $field_name = $this->getPropertyDefinition()->getAdditionalValue('field');
    if ($field_name && $definition->hasField($field_name)) {
      $styles = $definition->getField($field_name)->getAdditionalValue('styles');
      $new_styles = $this->getPropertyDefinition()->getAdditionalValue('options')[$this->getValue()]['styles'] ?? NULL;
      if ($new_styles) {
        $definition->getField($field_name)->setAdditionalValue('styles', $new_styles);
      }
    }
  }

}

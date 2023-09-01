<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'split_text' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "split_text",
 *   label = @Translation("Split Text"),
 * )
 */
class SplitText extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains text that will be split.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $aos = \Drupal::service('exo_aos.generator')->generate(($this->getEnhancementDefinition()->getAdditionalValue('animation') ?: []) + [
      'animation' => 'custom',
    ]);
    $id = Html::getId($this->getEnhancementDefinition()->id() . '-' . $contexts['component_id']->getContextValue());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $attributes = [
      'class' => ['ee--split-text-wrapper', 'ee--split-text-wrapper'],
      'data-ee--split-text-id' => $id,
    ];
    if ($type = $this->getEnhancementDefinition()->getAdditionalValue('split_text_type')) {
      // Can be 'lines', 'words' or 'chars'.
      $attributes['data-ee--split-text-type'] = $type;
    }
    if ($val = $this->getEnhancementDefinition()->getAdditionalValue('split_text_delay_lines')) {
      $attributes['data-ee--split-text-delay-lines'] = $val;
    }
    $attributes = NestedArray::mergeDeep($aos->getAttributes()->toArray(), $attributes);
    $attached = $aos->getAttachments();
    $attached['library'][] = 'exo_alchemist/enhancement.split-text';
    $view = [
      '#attached' => $attached,
      'wrapper' => new ExoComponentAttribute($attributes, $is_layout_builder),
    ];
    return $view;
  }

}

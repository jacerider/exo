<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'count' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "count",
 *   label = @Translation("Count"),
 * )
 */
class Count extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be counted.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $aos = \Drupal::service('exo_aos.generator')->generate(($this->getEnhancementDefinition()->getAdditionalValue('animation') ?: []) + [
      'animation' => 'fade-in',
    ]);
    $id = Html::getId($this->getEnhancementDefinition()->id() . '-' . $contexts['component_id']->getContextValue());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $attributes = NestedArray::mergeDeep($aos->getAttributes()->toArray(), [
      'class' => ['ee--count-wrapper'],
      'data-ee--count-id' => $id,
    ]);
    $attached = $aos->getAttachments();
    $attached['library'][] = 'exo_alchemist/enhancement.count';
    $view = [
      '#attached' => $attached,
      'wrapper' => new ExoComponentAttribute($attributes, $is_layout_builder),
    ];
    return $view;
  }

}

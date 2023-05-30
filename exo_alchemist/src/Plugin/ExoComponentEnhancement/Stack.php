<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'stack' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "stack",
 *   label = @Translation("Stack"),
 * )
 */
class Stack extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element (ul) that contains elements that will be stacked.'),
      'item' => $this->t('Attributes that should be added to each element that should stack.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $id = Html::getId($this->getEnhancementDefinition()->id() . '-' . $contexts['component_id']->getContextValue());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $view = [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.stack'],
      ],
      'wrapper' => new ExoComponentAttribute([
        'class' => ['ee--stack-wrapper'],
        'data-ee--stack-id' => $id,
      ], $is_layout_builder),
      'item' => new ExoComponentAttribute([
        'class' => ['ee--stack-item'],
      ], $is_layout_builder),
    ];
    return $view;
  }

}

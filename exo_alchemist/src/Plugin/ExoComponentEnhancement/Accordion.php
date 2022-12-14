<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'accordion' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "accordion",
 *   label = @Translation("Accordion"),
 * )
 */
class Accordion extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be toggleable.'),
      'item' => $this->t('Attributes that should be added to each element that should be toggleable.'),
      'trigger' => $this->t('Attributes that should be added to each element that should act as a trigger for the toggle.'),
      'content' => $this->t('Attributes that should be added to each element that should expand/contract.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $id = Html::getId($this->getEnhancementDefinition()->id() . '-' . $contexts['component_id']->getContextValue());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $attributes = [
      'class' => ['ee--accordion-wrapper'],
      'data-ee--accordion-id' => $id,
    ];
    if ($this->getEnhancementDefinition()->getAdditionalValue('collapse')) {
      $attributes['data-ee--accordion-collapse'] = 1;
    }
    if ($this->getEnhancementDefinition()->getAdditionalValue('history')) {
      $attributes['data-ee--accordion-history'] = 1;
    }
    if ($this->getEnhancementDefinition()->getAdditionalValue('require')) {
      $attributes['data-ee--accordion-require'] = 1;
    }

    $view = [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.accordion'],
      ],
      'wrapper' => new ExoComponentAttribute($attributes, $is_layout_builder),
      'item' => new ExoComponentAttribute([
        'class' => ['ee--accordion-item'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
      'trigger' => new ExoComponentAttribute([
        'class' => ['ee--accordion-trigger', 'tabbable'],
        'data-ee--accordion-id' => $id,
        'tabindex' => 0,
        'aria-expanded' => 'false',
      ], $is_layout_builder),
      'content' => new ExoComponentAttribute([
        'class' => ['ee--accordion-content'],
        'data-ee--accordion-id' => $id,
      ], $is_layout_builder),
    ];
    if ($is_layout_builder) {
      $view['trigger']->events(TRUE);
    }
    return $view;
  }

}

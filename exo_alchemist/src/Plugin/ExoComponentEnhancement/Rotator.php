<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'rotator' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "rotator",
 *   label = @Translation("Rotator"),
 * )
 */
class Rotator extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    return [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be rotated.'),
      'items' => $this->t('Attributes that should be added to a wrapper that contains only items.'),
      'item' => $this->t('Attributes that should be added to each element that should be rotated.'),
      'next' => $this->t('Attributes that should be added to an element that, when clicked, will move the rotator forward.'),
      'prev' => $this->t('Attributes that should be added to an element that, when clicked, will move the rotator backward.'),
      'nav' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be a rotator navigation.'),
      'nav_item' => $this->t('Attributes that should be added to each element that, when clicked, will rotate to an item.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $id = Html::getId($this->getEnhancementDefinition()->id());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $attributes = [
      'id' => $id,
      'class' => ['ee--rotator-wrapper'],
      'tabindex' => 0,
      'aria-roledescription' => 'carousel',
      'data-ee--rotator-id' => $id,
    ];
    if ($speed = $this->getEnhancementDefinition()->getAdditionalValue('speed')) {
      $attributes['data-ee--rotator-speed'] = $speed;
    }
    if ($this->getEnhancementDefinition()->getAdditionalValue('pauseOnHover')) {
      $attributes['data-ee--rotator-pauseonhover'] = 1;
    }
    $view = [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.rotator'],
      ],
      'wrapper' => new ExoComponentAttribute($attributes, $is_layout_builder),
      'items' => new ExoComponentAttribute([
        'class' => ['ee--rotator-items'],
        'role' => 'group',
        'aria-live' => 'off',
      ], $is_layout_builder),
      'item' => new ExoComponentAttribute([
        'class' => ['ee--rotator-item'],
        'role' => 'group',
        'aria-roledescription' => 'slide',
      ], $is_layout_builder),
      'next' => new ExoComponentAttribute([
        'class' => ['ee--rotator-next', 'tabbable'],
        'aria-controls' => $id . '-items',
        'aria-label' => $this->t('Next Slide'),
        'tabindex' => 0,
      ], $is_layout_builder),
      'prev' => new ExoComponentAttribute([
        'class' => ['ee--rotator-prev', 'tabbable'],
        'aria-controls' => $id . '-items',
        'aria-label' => $this->t('Previous Slide'),
        'tabindex' => 0,
      ], $is_layout_builder),
      'nav' => new ExoComponentAttribute(['class' => ['ee--rotator-nav']], $is_layout_builder),
      'nav_item' => new ExoComponentAttribute([
        'class' => ['ee--rotator-nav-item', 'tabbable'],
        'aria-controls' => $id . '-items',
        'tabindex' => 0,
      ], $is_layout_builder),
    ];

    if ($is_layout_builder) {
      $view['next']->events(TRUE);
      $view['prev']->events(TRUE);
      $view['nav_item']->events(TRUE);
      $view['wrapper']->addOp('rotator-prev', 'Prev Slide', 'regular-chevron-square-left');
      $view['wrapper']->addOp('rotator-next', 'Next Slide', 'regular-chevron-square-right');
    }
    return $view;
  }

}

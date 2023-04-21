<?php

namespace Drupal\exo_alchemist\Plugin\ExoComponentEnhancement;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\exo_alchemist\ExoComponentAttribute;
use Drupal\exo_alchemist\Plugin\ExoComponentEnhancementBase;

/**
 * A 'slider' enhancer for exo components.
 *
 * @ExoComponentEnhancement(
 *   id = "slider",
 *   label = @Translation("Slider"),
 * )
 */
class Slider extends ExoComponentEnhancementBase {

  /**
   * {@inheritdoc}
   */
  public function propertyInfo() {
    $info = [
      'wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be slider.'),
      'items' => $this->t('Attributes that should be added to a wrapper that contains only slider items.'),
      'item' => $this->t('Attributes that should be added to each element that should slide.'),
      'pagination' => $this->t('Attributes that should be added to an empty element that will be the slider pagination.'),
      'prev' => $this->t('Attributes that should be added to an empty element that will be the slider prev button.'),
      'next' => $this->t('Attributes that should be added to an empty element that will be the slider next button.'),
      'scrollbar' => $this->t('Attributes that should be added to an empty element that will be the slider scrollbar.'),
    ];
    if (!empty($this->getEnhancementDefinition()->getAdditionalValue('slider_nav'))) {
      $info += [
        'nav_wrapper' => $this->t('Attributes that should be added to a wrapper element that contains elements that will be slider navigation.'),
        'nav_items' => $this->t('Attributes that should be added to a wrapper that contains only navigation elements.'),
        'nav_item' => $this->t('Attributes that should be added to each individual navigation element.'),
      ];
    }
    if (!empty($this->getEnhancementDefinition()->getAdditionalValue('slider_progress'))) {
      $info += [
        'progress_time' => $this->t('Attributes that should be added to an element with a progress countdown in seconds.'),
        'progress_bar' => $this->t('Attributes that should be added to an element with a progress bar.'),
      ];
    }
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array $contexts) {
    $id = Html::getId($this->getEnhancementDefinition()->id() . '-' . $contexts['component_id']->getContextValue());
    $is_layout_builder = $this->isLayoutBuilder($contexts);
    $settings = $this->getEnhancementDefinition()->getAdditionalValue('settings');
    if ($is_layout_builder) {
      $settings['loop'] = FALSE;
      $settings['slidesOffsetAfter'] = 1000;
    }
    $view = [
      '#attached' => [
        'library' => ['exo_alchemist/enhancement.slider'],
      ],
      'wrapper' => new ExoComponentAttribute([
        'class' => ['ee--slider-wrapper', 'swiper'],
        'data-ee--slider-id' => $id,
        'data-ee--slider-settings' => $settings ? Json::encode($settings) : NULL,
      ], $is_layout_builder),
      'items' => new ExoComponentAttribute([
        'class' => ['ee--slider-items', 'swiper-wrapper'],
      ], $is_layout_builder),
      'item' => new ExoComponentAttribute([
        'class' => ['ee--slider-item', 'swiper-slide'],
      ], $is_layout_builder),
      'pagination' => new ExoComponentAttribute([
        'class' => ['ee--slider-pagination', 'swiper-pagination'],
      ], $is_layout_builder),
      'prev' => new ExoComponentAttribute([
        'class' => ['ee--slider-prev', 'swiper-button-prev'],
      ], $is_layout_builder),
      'next' => new ExoComponentAttribute([
        'class' => ['ee--slider-next', 'swiper-button-next'],
      ], $is_layout_builder),
      'scrollbar' => new ExoComponentAttribute([
        'class' => ['ee--slider-scrollbar', 'swiper-scrollbar'],
      ], $is_layout_builder),
    ];

    $nav_settings = $this->getEnhancementDefinition()->getAdditionalValue('slider_nav');
    if (!empty($nav_settings)) {
      $nav_settings = is_array($nav_settings) ? $nav_settings : [];
      $view['nav_wrapper'] = new ExoComponentAttribute([
        'class' => ['ee--slider-nav', 'swiper'],
        'data-ee--slider-id' => $id,
        'data-ee--slider-settings' => $nav_settings ? Json::encode($nav_settings) : NULL,
      ], $is_layout_builder);
      $view['nav_items'] = new ExoComponentAttribute([
        'class' => ['ee--slider-nav', 'swiper-wrapper'],
        'thumbsSlider' => '',
      ], $is_layout_builder);
      $view['nav_item'] = new ExoComponentAttribute([
        'class' => ['ee--slider-nav-item', 'swiper-slide'],
      ], $is_layout_builder);
    }

    if (!empty($this->getEnhancementDefinition()->getAdditionalValue('slider_progress'))) {
      $view['progress_time'] = new ExoComponentAttribute([
        'class' => ['ee--slider-progress-time', 'swiper-autoplay-time'],
      ], $is_layout_builder);
      $view['progress_bar'] = new ExoComponentAttribute([
        'class' => ['ee--slider-progress-bar', 'swiper-autoplay-bar'],
      ], $is_layout_builder);
    }

    if ($is_layout_builder) {
      $view['next']->events(TRUE);
      $view['prev']->events(TRUE);
      $view['wrapper']->addOp('rotator-prev', 'Prev Slide', 'regular-chevron-square-left');
      $view['wrapper']->addOp('rotator-next', 'Next Slide', 'regular-chevron-square-right');
    }
    return $view;
  }

}

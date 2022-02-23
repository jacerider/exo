<?php

namespace Drupal\exo_fixed;

use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Pre-render callback handler.
 */
class ExoFixedPreRender implements TrustedCallbackInterface {

  /**
   * Pre render.
   *
   * @param array $element
   *   The render array.
   *
   * @return mixed
   *   The element ready for rendering.
   */
  public static function preRender(array $element) {
    $exo_settings = \Drupal::service('exo_fixed.settings');
    $theme = \Drupal::service('theme.manager')->getActiveTheme();
    if ($exo_settings->getSetting([
      'themes',
      $theme->getName(),
    ])) {
      foreach (Element::children($element) as $region) {
        if ($exo_settings->getSetting([
          'themes',
          $theme->getName(),
          $region,
          'status',
        ])) {
          exo_fixed_element($element[$region], $region, $exo_settings->getSetting([
            'themes',
            $theme->getName(),
            $region,
            'type',
          ]));
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritDoc}
   */
  public static function trustedCallbacks() {
    return ['preRender'];
  }

}

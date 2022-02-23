<?php

namespace Drupal\exo_icon\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class providing ExoIcon Twig extensions.
 *
 * This provides a Twig extension that registers the {{ icon() }} extension
 * to Twig.
 */
class ExoIcon extends AbstractExtension {

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig.exo_icon';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('icon', [$this, 'renderIcon']),
    ];
  }

  /**
   * Render the icon.
   *
   * @param string $icon
   *   The icon_id of the icon to render.
   *
   * @return mixed[]
   *   A render array.
   */
  public static function renderIcon($icon) {
    if (!$icon) {
      return '';
    }
    $build = [
      '#theme' => 'exo_icon',
      '#icon' => $icon,
    ];
    return $build;
  }

}

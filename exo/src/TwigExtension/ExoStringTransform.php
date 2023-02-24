<?php

namespace Drupal\exo\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * A class providing ExoIcon Twig extensions.
 *
 * This provides a Twig extension that registers the {{ icon() }} extension
 * to Twig.
 */
class ExoStringTransform extends AbstractExtension {

  /**
   * Gets a unique identifier for this Twig extension.
   *
   * @return string
   *   A unique identifier for this Twig extension.
   */
  public function getName() {
    return 'twig.exo.string.transform';
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return [
      new TwigFunction('exo_string', [$this, 'renderString']),
    ];
  }

  /**
   * Render the icon.
   *
   * @return mixed[]
   *   A render array.
   */
  public static function renderString($string, $remove = FALSE, $wrap = FALSE) {
    return exo_string_transform($string, $remove, $wrap);
  }

}

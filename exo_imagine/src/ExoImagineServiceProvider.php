<?php

namespace Drupal\exo_imagine;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Modifies the image warmer manager service.
 */
class ExoImagineServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('image_style_warmer.warmer')) {
      $definition = $container->getDefinition('image_style_warmer.warmer');
      $definition->setClass('Drupal\exo_imagine\ExoImagineStylesWarmer');
    }
  }

}

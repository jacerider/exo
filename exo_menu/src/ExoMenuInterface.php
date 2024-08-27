<?php

namespace Drupal\exo_menu;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ObjectWithPluginCollectionInterface;
use Drupal\Core\Render\RenderableInterface;

/**
 * Defines an object which can be rendered by the Render API.
 */
interface ExoMenuInterface extends RenderableInterface, RefinableCacheableDependencyInterface, ObjectWithPluginCollectionInterface {

}

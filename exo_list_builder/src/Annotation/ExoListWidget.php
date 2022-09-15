<?php

namespace Drupal\exo_list_builder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a exo list widget annotation.
 *
 * @see \Drupal\exo_list_builder\Plugin\ExoListManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoListWidget extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin description.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}

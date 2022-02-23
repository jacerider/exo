<?php

namespace Drupal\exo_list_builder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a exo list action annotation.
 *
 * @see \Drupal\exo_list_builder\Plugin\ExoListManager
 * @see plugin_api
 *
 * @Annotation
 */
class ExoListAction extends Plugin {

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
   * The weight of the plugin.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * The plugin description.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The entity type ids on which the plugin apply.
   *
   * @var array
   */
  public $entity_type = [];

  /**
   * The bundles on which the plugin apply.
   *
   * @var array
   */
  public $bundle = [];

}

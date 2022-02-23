<?php

namespace Drupal\exo_filter\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\Container;

/**
 * Provides the eXo Filter plugin manager.
 */
class ExoFilterManager extends DefaultPluginManager {

  /**
   * The widget type.
   *
   * @var string
   */
  protected $type;

  /**
   * Constructs a new ExoFilterManager object.
   *
   * @param string $type
   *   The plugin type, for example filter, filter or sort.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $plugin_interface = 'Drupal\exo_filter\Plugin\ExoFilterInterface';
    $plugin_definition_annotation_name = 'Drupal\exo_filter\Annotation\Exo' . Container::camelize($type);
    parent::__construct("Plugin/ExoFilter/$type", $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);
    $this->alterInfo('exo_filter_exo_filter_info');
    $this->setCacheBackend($cache_backend, 'exo_filter_exo_filter_plugins');

    $this->type = $type;
    $this->alterInfo('exo_filter_exo_' . $type . '_info');
    $this->setCacheBackend($cache_backend, 'exo_filter_exo_' . $type . '_plugins');
  }

  /**
   * Returns an array of filter options for a field type.
   *
   * @param string|null $field_type
   *   (optional) The name of a field type, or NULL to retrieve all filters.
   *
   * @return array
   *   If no field type is provided, returns a nested array of all filters,
   *   keyed by field type.
   */
  public function getOptions($field_type = NULL) {
    $filter_options = [];
    $filter_types = $this->getDefinitions();
    foreach ($filter_types as $name => $filter_type) {
      foreach ($filter_type['field_types'] as $filter_field_type) {
        $filter_options[$filter_field_type][$name] = $filter_type['label'];
      }
    }
    if ($field_type) {
      return !empty($filter_options[$field_type]) ? $filter_options[$field_type] : [];
    }
    return $filter_options;
  }

}

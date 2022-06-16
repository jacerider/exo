<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Field type export plugin manager.
 */
class ExoListSortManager extends DefaultPluginManager implements ExoListSortManagerInterface {

  /**
   * Provides default values for all exo_list_field plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
    'weight' => 0,
    'description' => '',
    'entity_type' => [],
    'bundle' => [],
  ];

  /**
   * Constructs a new ExoListSortManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $interface = 'Drupal\exo_list_builder\Plugin\ExoListSortInterface';
    $annotation = 'Drupal\exo_list_builder\Annotation\ExoListSort';
    parent::__construct('Plugin/ExoList/Sort', $namespaces, $module_handler, $interface, $annotation);
    $this->alterInfo('exo_list_builder_sort_info');
    $this->setCacheBackend($cache_backend, 'exo_list_builder_sort_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getOptions() {
    $options = [];
    $definitions = $this->getDefinitions();
    $this->sortDefinitions($definitions);
    foreach ($definitions as $plugin_id => $definition) {
      $options[$plugin_id] = $definition['label'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldOptions($entity_type = NULL, $bundle = NULL) {
    $options = [];
    $definitions = $this->getDefinitions();

    // Filter out definitions that can not be configured in Field UI.
    $definitions = array_filter($definitions, function ($definition) {
      return empty($definition['no_ui']);
    });

    $this->sortDefinitions($definitions);
    foreach ($definitions as $plugin_id => $definition) {
      if ($entity_type) {
        if (!empty($definition['entity_type']) && !in_array($entity_type, $definition['entity_type'])) {
          continue;
        }
      }
      if ($bundle) {
        if (!empty($definition['bundle']) && !in_array($bundle, $definition['bundle'])) {
          continue;
        }
      }
      $options[$plugin_id] = $definition['label'];
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function removeExcludeDefinitions(array $definitions) {
    $definitions = isset($definitions) ? $definitions : $this->getDefinitions();
    // Exclude 'broken' fallback plugin.
    unset($definitions['broken']);
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return 'broken';
  }

  /**
   * Sort definitions by weigth descending.
   *
   * @param array $definitions
   *   The definitions.
   */
  protected function sortDefinitions(array &$definitions) {
    uasort($definitions, function ($a, $b) {
      return $a['weight'] - $b['weight'];
    });
  }

}

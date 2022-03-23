<?php

namespace Drupal\exo_list_builder;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\exo_list_builder\Entity\EntityList;

/**
 * Provides the default exo_list_field manager.
 */
class ExoListFieldManager extends DefaultPluginManager implements ExoListFieldManagerInterface {

  /**
   * Provides default values for all exo_list_field plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
    'display_label' => '',
    'alias_field' => '',
    'sort_field' => '',
    'entity_type' => [],
    'bundle' => [],
  ];

  /**
   * Constructs a new ExoListFieldManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   */
  public function __construct(ModuleHandlerInterface $module_handler, CacheBackendInterface $cache_backend) {
    // Add more services as required.
    $this->moduleHandler = $module_handler;
    $this->setCacheBackend($cache_backend, 'exo_list_field', ['exo_list_field']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = new YamlDiscovery('exo_list.field', $this->moduleHandler->getModuleDirectories());
      $this->discovery->addTranslatableProperty('label', 'label_context');
      $this->discovery->addTranslatableProperty('display_label', 'label_context');
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($this->discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    parent::processDefinition($definition, $plugin_id);
    $definition['id'] = $plugin_id;

    // You can add validation of the plugin definition here.
    if (empty($definition['label'])) {
      throw new PluginException(sprintf('Exo List plugin property (%s) definition "label" is required.', $plugin_id));
    }

    if (empty($definition['display_label'])) {
      $definition['display_label'] = $definition['label'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFields($entity_type = NULL, $bundle = NULL) {
    $definitions = [];
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
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
      $definitions[$plugin_id] = [
        'label' => $definition['label'],
        'display_label' => $definition['display_label'],
        'alias_field' => $definition['alias_field'],
        'sort_field' => $definition['sort_field'],
        'type' => 'custom',
      ];
    }

    return $definitions;
  }

}

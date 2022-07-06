<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides the Field type export plugin manager.
 */
class ExoListActionManager extends DefaultPluginManager implements ExoListActionManagerInterface {

  /**
   * Provides default values for all exo_list_field plugins.
   *
   * @var array
   */
  protected $defaults = [
    // Add required and optional plugin properties.
    'id' => '',
    'label' => '',
    'description' => '',
    'weight' => 0,
    'entity_type' => [],
    'bundle' => [],
    'queue' => FALSE,
  ];

  /**
   * Constructs a new ExoListActionManager object.
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
    $interface = 'Drupal\exo_list_builder\Plugin\ExoListActionInterface';
    $annotation = 'Drupal\exo_list_builder\Annotation\ExoListAction';
    parent::__construct('Plugin/ExoList/Action', $namespaces, $module_handler, $interface, $annotation);
    $this->alterInfo('exo_list_builder_action_info');
    $this->setCacheBackend($cache_backend, 'exo_list_builder_action_plugins');
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
    $definitions = $definitions ?? $this->getDefinitions();
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
      // Sort by weight.
      $weight = $a['weight'] - $b['weight'];
      if ($weight) {
        return $weight;
      }

      // Sort by label.
      return strcmp($a['label'], $b['label']);
    });
  }

  /**
   * Batch start operation.
   *
   * @param array $action
   *   The action definition.
   * @param string $entity_list_id
   *   The entity list id.
   * @param array $field_ids
   *   The shown field ids.
   * @param array $entity_ids
   *   The complete list of entity ids.
   * @param array $settings
   *   The action settings.
   * @param array $context
   *   The context.
   */
  public static function batchStart(array $action, $entity_list_id, array $field_ids, array $entity_ids, array $settings, array &$context) {
    /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
    $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($action['id'], $action['settings']);
    /** @var \Drupal\exo_list_builder\EntityListInterface $entity_list */
    $entity_list = \Drupal::entityTypeManager()->getStorage('exo_entity_list')->load($entity_list_id);
    // Override the entity fields.
    $fields = array_intersect_key($entity_list->getFields(), array_flip($field_ids));
    $entity_list->setFields($fields);
    // Set context data.
    $context['results']['entity_list_id'] = $entity_list_id;
    $context['results']['entity_list_action'] = $action;
    $context['results']['entity_list_fields'] = $field_ids;
    $context['results']['entity_ids'] = $entity_ids;
    $context['results']['entity_ids_complete'] = [];
    $context['results']['action_settings'] = $settings;
    // Start.
    $instance->executeStart($entity_list, $context);
  }

  /**
   * Batch operation.
   *
   * @param array $action
   *   The action definition.
   * @param string $entity_list_id
   *   The entity list id.
   * @param array $field_ids
   *   The shown field ids.
   * @param string $entity_id
   *   The entity id.
   * @param bool $selected
   *   Will be true if entity was selected.
   * @param array $context
   *   The context.
   */
  public static function batch(array $action, $entity_list_id, array $field_ids, $entity_id, $selected, array &$context) {
    /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
    $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($action['id'], $action['settings']);
    /** @var \Drupal\exo_list_builder\EntityListInterface $entity_list */
    $entity_list = \Drupal::entityTypeManager()->getStorage('exo_entity_list')->load($entity_list_id);
    // Override the entity fields.
    $fields = array_intersect_key($entity_list->getFields(), array_flip($field_ids));
    $entity_list->setFields($fields);
    $instance->execute($entity_id, $entity_list, $selected, $context);
    $context['results']['entity_ids_complete'][$entity_id] = \Drupal::time()->getRequestTime();
  }

  /**
   * Batch operation finish.
   *
   * @param bool $success
   *   The success.
   * @param array $results
   *   The results.
   * @param array $operations
   *   The operations.
   */
  public static function batchFinish($success, array $results, array $operations) {
    if ($success) {
      $entity_list_id = $results['entity_list_id'];
      $action = $results['entity_list_action'];
      $field_ids = $results['entity_list_fields'];
      /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
      $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($action['id'], $action['settings']);
      /** @var \Drupal\exo_list_builder\EntityListInterface $entity_list */
      $entity_list = \Drupal::entityTypeManager()->getStorage('exo_entity_list')->load($entity_list_id);
      // Override the entity fields.
      $fields = array_intersect_key($entity_list->getFields(), array_flip($field_ids));
      $entity_list->setFields($fields);
      $instance->executeFinish($entity_list, $results);

      if (empty($results['entity_list_hide_message'])) {
        $message = t('@count items successfully processed.', ['@count' => count($results['entity_ids'])]);
        \Drupal::messenger()->addStatus($message);
      }
    }
    else {
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
        '%error_operation' => $error_operation[0],
        '@arguments' => print_r($error_operation[1], TRUE),
      ]);
      \Drupal::messenger()->addError($message);
    }
  }

}

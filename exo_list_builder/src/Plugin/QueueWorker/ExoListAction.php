<?php

namespace Drupal\exo_list_builder\Plugin\QueueWorker;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Drupal\exo_list_builder\ExoListActionManager;
use Drupal\exo_list_builder\ExoListActionManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the art_board_share queueworker.
 *
 * @QueueWorker (
 *   id = "exo_list_action",
 *   title = @Translation("Process eXo list action item."),
 *   deriver = "Drupal\exo_list_builder\Plugin\Derivative\ExoListAction",
 *   cron = {"time" = 30}
 * )
 */
class ExoListAction extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The entity list storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityListStorage;

  /**
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $tempStore;

  /**
   * The action manager service.
   *
   * @var \Drupal\exo_list_builder\ExoListActionManagerInterface
   */
  protected $actionManager;

  /**
   * Constructs a new ExoListAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\exo_list_builder\ExoListActionManagerInterface $action_manager
   *   The action manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, SharedTempStoreFactory $temp_store_factory, ExoListActionManagerInterface $action_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityListStorage = $entity_type_manager->getStorage('exo_entity_list');
    $this->tempStore = $temp_store_factory->get('exo_entity_list_action');
    $this->actionManager = $action_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('tempstore.shared'),
      $container->get('plugin.manager.exo_list_action')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    [$base_plugin_id, $list_id, $action_id] = explode(':', $this->getPluginId());
    $context = $this->tempStore->get($data['job_id']);
    if (empty($context)) {
      $context = [
        'sandbox' => [],
        'results' => [],
        'finished' => 1,
        'message' => '',
      ];
    }
    switch ($data['op']) {
      case 'start':
        ExoListActionManager::batchStart($data['action'], $data['list_id'], $context);
        $this->tempStore->set($data['job_id'], $context);
        break;

      case 'process':
        ExoListActionManager::batch($data['action'], $data['id'], $data['list_id'], $data['field_ids'], $data['selected'], $context);
        $this->tempStore->set($data['job_id'], $context);
        break;

      case 'finish':
        ExoListActionManager::batchFinish(TRUE, $context['results'], []);
        $this->tempStore->delete($data['job_id']);
        break;
    }
  }

  /**
   * Get the temp store.
   *
   * @return \Drupal\Core\TempStore\SharedTempStore
   *   The temp store.
   */
  public function getTempStore() {
    return $this->tempStore;
  }

}

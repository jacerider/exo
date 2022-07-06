<?php

namespace Drupal\exo_list_builder\Plugin\QueueWorker;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\State\StateInterface;
use Drupal\exo_list_builder\ExoListActionManager;
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
   * The tempstore object.
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  protected $state;

  /**
   * Constructs a new ExoListAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The tempstore factory.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    switch ($data['op']) {
      case 'start':
        // Reset whenever we start a new batch.
        $this->deleteContext();
        $context = $this->getContext();
        $context['job_start'] = \Drupal::time()->getRequestTime();
        ExoListActionManager::batchStart($data['action'], $data['list_id'], $data['field_ids'], $data['entity_ids'], $data['settings'], $context);
        $this->state->set($this->getStateId(), $context);
        break;

      case 'process':
        $context = $this->getContext();
        ExoListActionManager::batch($data['action'], $data['list_id'], $data['field_ids'], $data['id'], $data['selected'], $context);
        $this->state->set($this->getStateId(), $context);
        break;

      case 'finish':
        $context = $this->getContext();
        $context['job_finish'] = \Drupal::time()->getRequestTime();
        ExoListActionManager::batchFinish(TRUE, $context['results'], []);
        $this->state->set($this->getStateId(), $context);
        break;
    }
  }

  /**
   * Get unique state id.
   *
   * @return string
   *   The state id.
   */
  protected function getStateId() {
    return 'exo_list_action:' . $this->getPluginId();
  }

  /**
   * Get the context.
   *
   * @return array
   *   The context.
   */
  public function getContext() {
    return $this->state->get($this->getStateId()) ?: [
      'sandbox' => [],
      'results' => [
        'entity_list_id' => NULL,
        'entity_list_action' => [],
        'entity_list_fields' => [],
        'entity_ids' => [],
        'entity_ids_complete' => [],
        'action_settings' => [],
      ],
      'finished' => 1,
      'message' => '',
    ];
  }

  /**
   * Delete the context.
   *
   * @return $this
   */
  public function deleteContext() {
    $this->state->delete($this->getStateId());
    return $this;
  }

}

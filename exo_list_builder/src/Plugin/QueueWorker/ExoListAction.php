<?php

namespace Drupal\exo_list_builder\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\State\StateInterface;
use Drupal\exo_list_builder\ExoListActionManager;
use Drupal\exo_list_builder\Plugin\ExoListActionInterface;
use Psr\Log\LogLevel;
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
   * @var \Drupal\Core\State\StateInterface
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

      case 'run':
        $do = TRUE;
        $context = $this->getContext();
        $requestTime = \Drupal::time()->getRequestTime();
        if ($context['last'] && $context['last'] > strtotime('-30 second', $requestTime)) {
          // If this action has been run within the last 30 seconds, prevent
          // another process from running it simultaneously.
          throw new SuspendQueueException('Another process is running this job.');
        }
        while ($do) {
          $context = $this->getContext();
          $processing = count($context['results']['entity_ids_complete']);
          $entity_id = array_slice($context['results']['entity_ids'], $processing, 1);
          if ($entity_id) {
            $entity_id = reset($entity_id);
            $this->cliLog('Processing Entity: %entity_id', ['%entity_id' => $entity_id], 'info');
            ExoListActionManager::batch($data['action'], $data['list_id'], $data['field_ids'], $entity_id, isset($data['selected'][$entity_id]), $context);
            $context['last'] = time();
            $this->state->set($this->getStateId(), $context);
          }
          else {
            $this->cliLog('Processing finished.', [], 'notice');
            $context['job_finish'] = \Drupal::time()->getRequestTime();
            ExoListActionManager::batchFinish(TRUE, $context['results'], []);
            $this->state->set($this->getStateId(), $context);
            $do = FALSE;
          }
        }
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
    return $this->state->get($this->getStateId(), [
      'sandbox' => [],
      'results' => [
        'entity_list_id' => NULL,
        'entity_list_action' => [],
        'entity_list_fields' => [],
        'entity_ids' => [],
        'entity_ids_complete' => [],
        'action_settings' => [],
        'queue' => TRUE,
      ],
      'last' => 0,
      'finished' => 1,
      'message' => '',
    ]);
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

  /**
   * Log a message if called during drush operations.
   */
  protected function cliLog($string, array $args = [], $type = 'info') {
    if (PHP_SAPI === 'cli') {
      $red = "\033[31;40m\033[1m%s\033[0m";
      $yellow = "\033[1;33;40m\033[1m%s\033[0m";
      $green = "\033[1;32;40m\033[1m%s\033[0m";
      switch ($type) {
        case LogLevel::EMERGENCY:
        case LogLevel::ALERT:
        case LogLevel::CRITICAL:
        case LogLevel::ERROR:
          $color = $red;
          break;

        case LogLevel::WARNING:
          $color = $yellow;
          break;

        case LogLevel::NOTICE:
          $color = $green;
          break;

        default:
          $color = "%s";
          break;
      }
      $message = strip_tags(sprintf($color, dt($string, $args)));
      fwrite(STDOUT, $message . "\n");
    }
  }

}

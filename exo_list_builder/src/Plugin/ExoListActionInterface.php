<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\ExoListBuilderInterface;

/**
 * Defines an interface for exo list actions.
 */
interface ExoListActionInterface extends PluginInspectionInterface, ConfigurableInterface {

  /**
   * Get label.
   *
   * @return string
   *   The label.
   */
  public function label();

  /**
   * Flag indicating if action should be run in a job queue.
   *
   * @returb bool
   *   TRUE if action should be run in a job queue.
   */
  public function supportsJobQueue();

  /**
   * Check if action should be run as a background job.
   *
   * @param int $count
   *   The total number of items being processed.
   *
   * @return bool
   *   Returns TRUE if action should be run as a background job.
   */
  public function runAsJobQueue(int $count = 0);

  /**
   * Build the configuration form.
   *
   * @param array $form
   *   The configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $action
   *   The action definition.
   *
   * @return mixed
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $action);

  /**
   * Validates a configuration form for this plugin.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state);

  /**
   * Get entity ids to process.
   *
   * @param array $selected_ids
   *   The entity id.
   * @param \Drupal\exo_list_builder\ExoListBuilderInterface $exo_list_builder
   *   The current entity list builder.
   */
  public function getEntityIds(array $selected_ids, ExoListBuilderInterface $exo_list_builder);

  /**
   * Execute start action.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   The batch context.
   */
  public function executeStart(EntityListInterface $entity_list, array &$context);

  /**
   * Execute action.
   *
   * @param string $entity_id
   *   The entity id.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param bool $selected
   *   Will be true if entity was selected.
   * @param array $context
   *   The batch context.
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context);

  /**
   * Execute finish action.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $results
   *   The batch results.
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results);

  /**
   * Get overview.
   *
   * @param array $context
   *   The context.
   *
   * @return string|array
   *   The overview.
   */
  public function overview(array $context);

  /**
   * An optional email no notify when the job queue has finished.
   *
   * @return string
   *   The email to notify.
   */
  public function getNotifyEmail();

  /**
   * Whether this theme negotiator should be used on the current list.
   *
   * @param \Drupal\exo_list_builder\ExoListBuilderInterface $exo_list
   *   The exo list builder.
   *
   * @return bool
   *   TRUE if this filter should be allowed.
   */
  public function applies(ExoListBuilderInterface $exo_list);

}

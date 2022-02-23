<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list actions.
 */
interface ExoListActionInterface extends PluginInspectionInterface, ConfigurableInterface {

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
   * Execute action.
   *
   * @param string $entity_id
   *   The entity id.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $context
   *   The batch context.
   */
  public function execute($entity_id, EntityListInterface $entity_list, array &$context);

  /**
   * Execute finish action.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $results
   *   The batch results.
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results);

}

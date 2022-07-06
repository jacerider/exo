<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list filters.
 */
interface ExoListActionSettingsInterface {

  /**
   * Build the settings form.
   *
   * @param array $form
   *   The settings form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $action
   *   The action definition.
   *
   * @return mixed
   *   The settings form.
   */
  public function buildSettingsForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $action);

  /**
   * Validates a settings form for this plugin.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateSettingsForm(array $form, FormStateInterface $form_state);

}

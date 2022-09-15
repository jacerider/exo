<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list widget.
 */
interface ExoListWidgetInterface extends PluginInspectionInterface, ConfigurableInterface {

  /**
   * The default settings.
   */
  const DEFAULTS = [];

  /**
   * Get label.
   *
   * @return string
   *   The label.
   */
  public function label();

  /**
   * Build the configuration form.
   *
   * @param array $form
   *   The configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $filter
   *   The filter.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field);

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
   * Alter the element.
   *
   * @param array $element
   *   The widget element.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $filter
   *   The filter.
   * @param array $field
   *   The field definition.
   */
  public function alterElement(array &$element, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field);

  /**
   * Alter the options.
   *
   * @param array $options
   *   The options.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param \Drupal\exo_list_builder\Plugin\ExoListFilterInterface $filter
   *   The filter.
   * @param array $field
   *   The field definition.
   */
  public function alterOptions(array &$options, EntityListInterface $entity_list, ExoListFilterInterface $filter, array $field);

  /**
   * Whether this theme negotiator should be used on the current list.
   *
   * @param \Drupal\exo_list_builder\EntityListInterface $exo_list
   *   The exo list builder.
   *
   * @return bool
   *   TRUE if this filter should be allowed.
   */
  public function applies(EntityListInterface $exo_list);

}

<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines an interface for exo list filters.
 */
interface ExoListElementInterface extends PluginInspectionInterface, ConfigurableInterface {

  /**
   * The default settings.
   */
  const DEFAULTS = [
    'link' => FALSE,
    'separator' => ', ',
    'empty' => '-',
  ];

  /**
   * Build the configuration form.
   *
   * @param array $form
   *   The configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form_state object.
   * @param \Drupal\exo_list_builder\EntityListInterface $entity_list
   *   The entity list.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   The configuration form.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field);

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
   * Get viewable output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  public function buildView(EntityInterface $entity, array $field);

  /**
   * Get plain output. Useful for things such as exports.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   A string.
   */
  public function buildPlainView(EntityInterface $entity, array $field);

  /**
   * Whether this theme negotiator should be used to set the theme.
   *
   * @param array $field
   *   The field definition.
   *
   * @return bool
   *   TRUE if this filter should be allowed.
   */
  public function applies(array $field);

}

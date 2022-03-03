<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_icon\ExoIconTranslatableMarkup;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list elements.
 */
abstract class ExoListElementBase extends PluginBase implements ExoListElementInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'link' => FALSE,
      'separator' => ', ',
      'empty' => '-',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form['link'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Link to the entity'),
      '#default_value' => $this->configuration['link'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array $form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function buildView(EntityInterface $entity, array $field) {
    $view = $this->view($entity, $field);
    if ($this->getConfiguration()['link']) {
      $view = [
        '#type' => 'link',
        '#url' => $entity->toUrl('canonical'),
        '#title' => Markup::create($view),
      ];
    }
    return $view;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPlainView(EntityInterface $entity, array $field) {
    $build = $this->viewPlain($entity, $field);
    if (!is_array($build)) {
      $build = [
        '#markup' => $build,
      ];
    }
    /** @var \Drupal\Core\Render\RendererInterface $renderer */
    $renderer = \Drupal::service('renderer');
    return trim(str_replace(["\r", "\n"], '', strip_tags($renderer->renderPlain($build))));
  }

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
  protected function view(EntityInterface $entity, array $field) {
    return $this->getConfiguration()['empty'];
  }

  /**
   * Get plain output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    return $this->view($entity, $field);
  }

  /**
   * Get entity icon.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\exo_icon\ExoIconTranslatableMarkup
   *   The icon.
   */
  protected function getIcon(EntityInterface $entity) {
    $icon = new ExoIconTranslatableMarkup($entity->label());
    $entity_icon = exo_icon_entity_icon($entity);
    if ($entity_icon) {
      $icon->setIcon($entity_icon);
    }
    else {
      $icon->match([], (string) $entity->getEntityType()->getLabel());
    }
    return $icon;
  }

  /**
   * Get content type from field.
   *
   * @param array $field
   *   The field definition.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   The content type.
   */
  protected function getEntityTypeFromField(array $field) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
    $field_definition = $field['definition'];
    $entity_type_id = $field_definition->getSetting('target_type');
    return $this->entityTypeManager()->getDefinition($entity_type_id);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(array $field) {
    return TRUE;
  }

  /**
   * Get entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager() {
    if (!isset($this->entityTypeManager)) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

}

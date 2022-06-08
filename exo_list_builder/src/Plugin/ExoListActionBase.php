<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\exo_list_builder\EntityListInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\ExoListBuilderInterface;

/**
 * Base class for eXo list actions.
 */
abstract class ExoListActionBase extends PluginBase implements ExoListActionInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * LogGeneratorBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getPluginDefinition()['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function asJobQueue() {
    return !empty($this->getPluginDefinition()['queue']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $action) {
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
  public function getEntityIds(array $selected_ids, ExoListBuilderInterface $exo_list_builder) {
    if (empty($selected_ids)) {
      // If none are selected, process all.
      $selected_ids = $exo_list_builder->getQuery()->execute();
    }
    return $selected_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function executeStart(EntityListInterface $entity_list, array &$context) {
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
  }

  /**
   * {@inheritdoc}
   */
  public function executeFinish(EntityListInterface $entity_list, array &$results) {
  }

  /**
   * {@inheritdoc}
   */
  public function applies(ExoListBuilderInterface $exo_list) {
    return TRUE;
  }

  /**
   * Load entity.
   *
   * @param string $entity_type
   *   The entity type id.
   * @param string $entity_id
   *   The entity id.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  protected function loadEntity($entity_type, $entity_id) {
    return $this->entityTypeManager->getStorage($entity_type)->load($entity_id);
  }

}

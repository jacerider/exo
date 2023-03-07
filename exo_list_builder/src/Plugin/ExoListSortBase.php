<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Base class for eXo list sort.
 */
abstract class ExoListSortBase extends PluginBase implements ExoListSortInterface, ContainerFactoryPluginInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Flag indicating if the sort supports direction change.
   *
   * @var bool
   */
  protected $supportsDirectionChange = FALSE;

  /**
   * The default direction.
   *
   * @var string
   */
  protected $defaultDirection = 'asc';

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
  public function supportsDirectionChange() {
    return !empty($this->supportsDirectionChange);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultDirection() {
    return $this->defaultDirection === 'asc' ? 'asc' : 'desc';
  }

  /**
   * {@inheritdoc}
   */
  public function getAscLabel() {
    return $this->label() . ': ' . $this->t('A-Z');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescLabel() {
    return $this->label() . ': ' . $this->t('Z-A');
  }

  /**
   * {@inheritdoc}
   */
  public function sort($query, EntityListInterface $entity_list, &$direction = NULL) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityListInterface $exo_list) {
    return TRUE;
  }

}

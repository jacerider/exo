<?php

namespace Drupal\exo_site_settings\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides base for blocks that was to use a settings entity.
 */
class SiteSettingsTypeBlockBase extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The settings type to use.
   *
   * @var string
   */
  protected $siteSettingsTypeId = 'general';

  /**
   * The site settings entity.
   *
   * @var \Drupal\exo_site_settings\Entity\SiteSettingsInterface
   */
  protected $siteSettings;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setConfiguration($configuration);
    $this->entityTypeManager = $entity_type_manager;
    /** @var \Drupal\exo_site_settings\SiteSettingsStorage $storage */
    $storage = $this->entityTypeManager->getStorage('exo_site_settings');
    $this->siteSettings = $storage->loadByType($this->siteSettingsTypeId);
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
  public function getCacheTags() {
    return $this->siteSettings->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    return 'exo_site_settings_' . $this->siteSettingsTypeId;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();
    $dependencies['config'][] = 'exo_site_settings.exo_site_settings_type.' . $this->siteSettingsTypeId;
    return $dependencies;
  }

}

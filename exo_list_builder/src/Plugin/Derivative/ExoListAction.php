<?php

namespace Drupal\exo_list_builder\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions for custom menus.
 *
 * @see \Drupal\exo_list_builder\Plugin\Block\ExoListAction
 */
class ExoListAction extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The menu storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityListStorage;

  /**
   * Constructs new ExoListFilterBlock.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_list_storage
   *   The eXo entity list storage.
   */
  public function __construct(EntityStorageInterface $entity_list_storage) {
    $this->entityListStorage = $entity_list_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager')->getStorage('exo_entity_list')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    foreach ($this->entityListStorage->loadMultiple() as $id => $entity) {
      /** @var \Drupal\exo_list_builder\EntityListInterface $entity */
      foreach ($entity->getActions() as $action) {
        // We do this instead of loading the handler as the handler can alter
        // the available actions and we need all enabled actions.
        /** @var \Drupal\exo_list_builder\Plugin\ExoListActionInterface $instance */
        $instance = \Drupal::service('plugin.manager.exo_list_action')->createInstance($action['id'], $action['settings']);
        if ($instance->asJobQueue()) {
          $this->derivatives[$id . ':' . $instance->getPluginId()] = $base_plugin_definition;
          $this->derivatives[$id . ':' . $instance->getPluginId()]['title'] = t('Action Queue Worker for @action in @label', [
            '@action' => $action['label'],
            '@label' => $entity->label(),
          ]);
          $this->derivatives[$id . ':' . $instance->getPluginId()]['config_dependencies']['config'] = [$entity->getConfigDependencyName()];
        }
      }
    }
    return $this->derivatives;
  }

}

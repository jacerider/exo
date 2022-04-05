<?php

namespace Drupal\exo_list_builder\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions for custom menus.
 *
 * @see \Drupal\exo_list_builder\Plugin\Block\ExoListFilterBlock
 */
class ExoListFilterBlock extends DeriverBase implements ContainerDeriverInterface {

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
      if ($entity->getSetting('block_status', FALSE)) {
        $this->derivatives['exo_entity_list_' . $id] = $base_plugin_definition;
        $this->derivatives['exo_entity_list_' . $id]['admin_label'] = t('@label Filter', ['@label' => $entity->label()]);
        $this->derivatives['exo_entity_list_' . $id]['config_dependencies']['config'] = [$entity->getConfigDependencyName()];
      }
    }
    return $this->derivatives;
  }

}

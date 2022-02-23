<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The exo list builder permissions generator.
 */
class ExoListBuilderPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a ExoListBuilderPermissions instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Get permissions for Taxonomy Views Integrator.
   *
   * @return array
   *   Permissions array.
   */
  public function permissions() {
    $permissions = [];

    foreach ($this->entityTypeManager->getStorage('exo_entity_list')->loadMultiple() as $exo_entity_list) {
      /** @var \Drupal\exo_list_builder\EntityListInterface $exo_entity_list */
      $permissions += [
        'access ' . $exo_entity_list->id() . ' list' => [
          'title' => $this->t('Access the %label list', ['%label' => $exo_entity_list->label()]),
        ],
      ];
    }

    return $permissions;
  }

}

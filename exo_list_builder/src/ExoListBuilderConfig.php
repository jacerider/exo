<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a list builder for config entities.
 */
class ExoListBuilderConfig extends ExoListBuilderBase {

  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $storage;

  /**
   * {@inheritDoc}
   */
  protected function discoverFields() {
    $entity_list = $this->getEntityList();
    $fields = [];
    $entity_type = $entity_list->getTargetEntityType();
    if ($entity_type instanceof ConfigEntityTypeInterface) {
      $properties = $entity_type->getPropertiesToExport();
      foreach ($properties as $property) {
        if (substr($property, 0, 1) === '_') {
          continue;
        }
        if (in_array($property, [
          'third_party_settings',
          'dependencies',
        ])) {
          continue;
        }
        $fields[$property] = [
          'label' => ucwords(str_replace('_', ' ', $property)),
          'type' => 'config',
          'sort_field' => $property,
        ];
      }
    }
    return $fields;
  }

  /**
   * Allow builder to modify field list.
   */
  protected function alterFields(&$fields) {
    $label_key = $this->entityList->getTargetEntityType()->getKey('label');
    if ($label_key) {
      $fields['_label']['sort_field'] = $label_key;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    $entities = $this->storage->loadMultipleOverrideFree($entity_ids);
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $operations = parent::getDefaultOperations($entity);

    if ($this->entityType->hasKey('status')) {
      if (!$entity->status() && $entity->hasLinkTemplate('enable')) {
        $operations['enable'] = [
          'title' => t('Enable'),
          'weight' => -10,
          'url' => $this->ensureDestination($entity->toUrl('enable')),
        ];
      }
      elseif ($entity->hasLinkTemplate('disable')) {
        $operations['disable'] = [
          'title' => t('Disable'),
          'weight' => 40,
          'url' => $this->ensureDestination($entity->toUrl('disable')),
        ];
      }
    }

    return $operations;
  }

}

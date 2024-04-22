<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a list builder for content entities.
 */
class ExoListBuilderContent extends ExoListBuilderBase {

  /**
   * {@inheritDoc}
   */
  protected function discoverFields() {
    $entity_list = $this->getEntityList();
    $fields = [];
    $definitions = [];
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $field_manager */
    $field_manager = \Drupal::service('entity_field.manager');
    foreach ($entity_list->getTargetBundleIds() as $bundle) {
      $definitions += $field_manager->getFieldDefinitions($entity_list->getTargetEntityTypeId(), $bundle);
    }
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $definitions */
    foreach ($definitions as $key => $definition) {
      $fields[$key] = [
        'label' => $definition->getLabel(),
        'type' => $definition->getType(),
        'definition' => $definition,
      ];
      if (!$definition->isComputed()) {
        $fields[$key]['sort_field'] = $definition->getName();
      }
    }
    return $fields;
  }

  /**
   * Allow builder to modify field list.
   */
  protected function alterFields(&$fields) {
    parent::alterFields($fields);
    switch ($this->entityList->getTargetEntityTypeId()) {
      case 'user':
        $fields['_label']['sort_field'] = 'name';
        break;

      default:
        $label_key = $this->entityList->getTargetEntityType()->getKey('label');
        if ($label_key && isset($fields[$label_key]['definition']) && !$fields[$label_key]['definition']->isComputed()) {
          $fields['_label']['sort_field'] = $label_key;
        }
        break;
    }
  }

  /**
   * Get field entity.
   */
  public function getFieldEntity(EntityInterface $entity, array $field) {
    $field_entity = $entity;
    if (!empty($field['reference_field'])) {
      if ($reference_entity = $this->getFieldEntityByPath($entity, explode(':', $field['reference_field']))) {
        $field_entity = $reference_entity;
      }
      else {
        $field_entity = NULL;
      }
    }
    return $field_entity;
  }

  /**
   * Get field entity.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The field entity.
   */
  protected function getFieldEntityByPath(ContentEntityInterface $entity, array $path) {
    if (!empty($path)) {
      $field_name = array_shift($path);
      if ($entity->hasField($field_name) && !empty($entity->get($field_name)->entity)) {
        $entity = $entity->get($field_name)->entity;
        if (!empty($path)) {
          return $this->getFieldEntityByPath($entity, $path);
        }
        return $entity;
      }
    }
    return NULL;
  }

}

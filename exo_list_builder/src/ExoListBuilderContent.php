<?php

namespace Drupal\exo_list_builder;

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

}

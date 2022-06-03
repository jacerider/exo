<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Provides a trait for handling content entities.
 */
trait ExoListContentTrait {

  /**
   * Get item from entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The field item list.
   */
  protected function getItems(ContentEntityInterface $entity, array $field) {
    $field_name = $field['field_name'];
    if (!$entity->hasField($field_name)) {
      return NULL;
    }
    $field_items = $entity->get($field_name);
    if ($field_items->isEmpty()) {
      return NULL;
    }
    return $field_items;
  }

  /**
   * Get item from entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return \Drupal\Core\Field\FieldItemInterface|null
   *   The field item.
   */
  protected function getItem(ContentEntityInterface $entity, array $field) {
    $items = $this->getItems($entity, $field);
    if (!$items) {
      return NULL;
    }
    return $items->first();
  }

  /**
   * Get the entity type bundles from the definition.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   *
   * @return array
   *   An array of bundles.
   */
  protected function getFieldBundles(FieldDefinitionInterface $field_definition, EntityTypeInterface $entity_type) {
    $handler = $field_definition->getSetting('handler_settings');
    if ($entity_type->hasKey('bundle')) {
      $bundles = $handler['target_bundles'] ?? array_keys(\Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type->id()));
    }
    else {
      $bundles = $handler['target_bundles'] ?? [$entity_type->id()];
    }
    return $bundles;
  }

  /**
   * Get the property options to export.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   An array of property.
   */
  protected function getPropertyOptions(FieldDefinitionInterface $field_definition) {
    $property = $this->getFieldProperties($field_definition);
    $options = [];
    foreach ($property as $property_name => $property) {
      $options[$property_name] = $property->getLabel();
    }
    return $options;
  }

  /**
   * Get the property options to export.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   An array of property.
   */
  protected function getPropertyReferenceOptions(FieldDefinitionInterface $field_definition) {
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $entity_type_id = $field_definition->getSetting('target_type');
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    $bundles = $this->getFieldBundles($field_definition, $entity_type);
    $fields = [];
    foreach ($bundles as $bundle) {
      $fields += $entity_field_manager->getFieldDefinitions($entity_type_id, $bundle);
    }
    $options = [];
    foreach ($fields as $field_name => $referenced_field_definition) {
      $property = $this->getFieldProperties($referenced_field_definition);
      foreach ($property as $property_name => $property) {
        $options[$field_name . '.' . $property_name] = $referenced_field_definition->getLabel() . ': ' . $property->getLabel();
      }
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldProperties(FieldDefinitionInterface $definition) {
    $key = $definition->getTargetEntityTypeId() . '.' . $definition->getName();
    if (empty($this->property[$key])) {
      $storage = $definition->getFieldStorageDefinition();
      $property = $storage->getPropertyDefinitions();
      // Filter out all computed property, these cannot be set.
      $property = array_filter($property, function (DataDefinitionInterface $definition) {
        return !$definition->isComputed();
      });

      if ($definition->getType() === 'image') {
        unset($property['width'], $property['height']);
      }
      $this->property[$key] = $property;
    }
    return $this->property[$key];
  }

  /**
   * Get available field values.
   */
  public function getAvailableFieldValues(EntityListInterface $entity_list, $field_id, $property, $condition) {
    $cid = 'exo_list_buider:filter:' . $entity_list->id() . ':' . $field_id . ':' . $property;
    $values = [];
    if (($cache = \Drupal::cache()->get($cid))) {
      $values = $cache->data;
    }
    else {
      $cacheable_metadata = new CacheableMetadata();
      $cacheable_metadata->addCacheTags($entity_list->getCacheTags());
      if ($query = $this->getAvailableFieldValuesQuery($entity_list, $field_id, $property, $condition, $cacheable_metadata)) {
        $values = $query->execute()->fetchCol();
      }

      $parts = explode('.', $property);
      $field_name = $parts[0] ?? $property;
      $column = $parts[1] ?? NULL;
      // When referencing a target entity, we will fetch the entity labels.
      if ($column === 'target_id') {
        $field_definition = $entity_list->getField($field_id)['definition'];
        if ($reference_field_definition = $this->getReferenceFieldDefinition($field_definition)) {
          $nested_reference_entity_type_id = $reference_field_definition->getSetting('target_type');
          $entities = $this->entityTypeManager()->getStorage($nested_reference_entity_type_id)->loadMultiple($values);
          uasort($entities, function (EntityInterface $a, EntityInterface $b) {
            if ($a instanceof TermInterface && $b instanceof TermInterface) {
              return $a->getWeight() <=> $b->getWeight();
            }
            return strnatcasecmp($a->label(), $b->label());
          });
          $values = [];
          foreach ($entities as $entity) {
            if ($entity->getEntityType()->hasKey('bundle')) {
              $cacheable_metadata->addCacheTags([$entity->getEntityTypeId() . '_list:' . $entity->bundle()]);
            }
            $cacheable_metadata->addCacheableDependency($entity);
            $values[$entity->id()] = $entity->label();
          }
        }
      }
      \Drupal::cache()->set($cid, $values, Cache::PERMANENT, $cacheable_metadata->getCacheTags());
    }
    return $values;
  }

  /**
   * Get available field values query.
   */
  protected function getAvailableFieldValuesQuery(EntityListInterface $entity_list, $field_id, $property, $condition, CacheableMetadata $cacheable_metadata) {
    /** @var \Drupal\Core\Entity\Sql\TableMappingInterface $table_mapping*/
    $storage = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId());
    if ($storage instanceof SqlEntityStorageInterface) {
      $field = $entity_list->getField($field_id);
      $field_name = $field['field_name'];
      $table_mapping = $storage->getTableMapping();
      $field_table = $table_mapping->getFieldTableName($field_name);
      $field_storage_definitions = \Drupal::service('entity_field.manager')->getFieldStorageDefinitions($entity_list->getTargetEntityTypeId())[$field_name];
      $field_column = $table_mapping->getFieldColumnName($field_storage_definitions, $property);
      $connection = \Drupal::database();
      $query = $connection->select($field_table, 'f')
        ->fields('f', [$field_column])
        ->distinct(TRUE);
      if (!empty($condition)) {
        $query->condition($field_column, '%' . $connection->escapeLike($condition) . '%', 'LIKE');
      }
      if ($bundle_key = $entity_list->getTargetEntityType()->getKey('bundle')) {
        $query->condition($bundle_key, $entity_list->getTargetBundleIds(), 'IN');
      }
      if ($label_key = $entity_list->getTargetEntityType()->getKey('label')) {
        $query->orderBy($label_key);
      }
      return $query;
    }
    return NULL;
  }

  /**
   * Get referenced table mapping.
   *
   * @return \Drupal\Core\Entity\Sql\DefaultTableMapping
   *   The mapping.
   */
  protected function getReferencedTableMapping($entity_type_id) {
    $mapping = NULL;
    $storage = $this->entityTypeManager()->getStorage($entity_type_id);
    if ($storage instanceof SqlEntityStorageInterface) {
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $reference_table_mapping */
      $mapping = $storage->getTableMapping();
    }
    return $mapping;
  }

  /**
   * Get the entity type bundles from the definition.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   *   A field definition.
   */
  protected function getReferenceFieldDefinition(FieldDefinitionInterface $field_definition) {
    $configuration = $this->getConfiguration();
    [$field_name, $property] = explode('.', $configuration['property']);
    $reference_entity_type_id = $field_definition->getSetting('target_type');
    $reference_entity_type = $this->entityTypeManager()->getDefinition($reference_entity_type_id);
    $reference_bundles = $this->getFieldBundles($field_definition, $reference_entity_type);
    /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $fields = [];
    foreach ($reference_bundles as $bundle_id) {
      $fields += $entity_field_manager->getFieldDefinitions($field_definition->getTargetEntityTypeId(), $bundle_id);
    }
    return isset($fields[$field_name]) ? $fields[$field_name] : NULL;
  }

}

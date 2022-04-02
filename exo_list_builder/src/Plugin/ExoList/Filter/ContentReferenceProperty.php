<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "content_reference_property",
 *   label = @Translation("Reference Property"),
 *   description = @Translation("Filter by entity reference property."),
 *   weight = 0,
 *   field_type = {
 *     "entity_reference",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 * )
 */
class ContentReferenceProperty extends ContentProperty {

  /**
   * {@inheritdoc}
   */
  protected function getPropertyOptions(FieldDefinitionInterface $field_definition) {
    return $this->getPropertyReferenceOptions($field_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['field_name'] . '.entity.' . $this->getConfiguration()['property'], $query, $value, $entity_list, $field);
  }

  /**
   * Get available field values query.
   */
  protected function getAvailableFieldValuesQuery(EntityListInterface $entity_list, $field_id, $property, $condition = NULL) {
    $field = $entity_list->getField($field_id);
    $field_definition = $field['definition'];
    return $this->getReferencedAvailableFieldValuesQuery($field_definition, $property, $condition);
  }

  /**
   * Get referenced available field values query.
   */
  protected function getReferencedAvailableFieldValuesQuery(FieldConfigInterface $field_definition, $property, $condition = NULL) {
    $query = NULL;
    /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    $reference_entity_type_id = $field_definition->getSetting('target_type');
    $storage = $this->entityTypeManager()->getStorage($entity_type_id);
    if (!$storage instanceof SqlEntityStorageInterface) {
      return $query;
    }
    if ($reference_table_mapping = $this->getReferencedTableMapping($reference_entity_type_id)) {
      $reference_entity_type = $this->entityTypeManager()->getDefinition($reference_entity_type_id);
      $reference_bundles = $this->getFieldBundles($field_definition, $reference_entity_type);
      @[$reference_field_name, $reference_property] = explode('.', $property);
      $reference_field_definitions = [];
      foreach ($reference_bundles as $reference_bundle) {
        $reference_field_definitions += $entity_field_manager->getFieldDefinitions($reference_entity_type_id, $reference_bundle);
      };
      /** @var \Drupal\Core\Field\FieldConfigInterface $reference_field_definition */
      $reference_field_definition = $reference_field_definitions[$reference_field_name];
      $reference_field_storage_definition = $reference_field_definition->getFieldStorageDefinition();
      // $reference_field_storage_definition = $entity_field_manager->getFieldStorageDefinitions($reference_entity_type_id)[$reference_field_name];
      $reference_id_key = $reference_entity_type->getKey('id');
      $reference_data_table = $reference_table_mapping->getDataTable() ?: $reference_table_mapping->getBaseTable();
      $reference_field_table = $reference_table_mapping->getFieldTableName($reference_field_name);
      $reference_field_column = $reference_table_mapping->getFieldColumnName($reference_field_storage_definition, $reference_property);
      $reference_bundle_key = $reference_entity_type->getKey('bundle');
      $connection = \Drupal::database();
      // We are searching against a field's table.
      if ($reference_data_table && $reference_data_table !== $reference_field_table) {
        $query = $connection->select($reference_data_table, 'd');
        $query->join($reference_field_table, 'f', 'd.' . $reference_id_key . ' = f.entity_id');
        if (!empty($reference_bundles)) {
          $query->condition('bundle', $reference_bundles, 'IN');
        }
      }
      else {
        $query = $connection->select($reference_field_table, 'f');
        if (!empty($reference_bundles) && $reference_bundle_key) {
          $query->condition($reference_bundle_key, $reference_bundles, 'IN');
        }
      }
      $query->fields('f', [$reference_field_column])
        ->distinct(TRUE);

      if (!empty($condition)) {

        if ($reference_property === 'target_id') {
          $target_entity_type = $this->entityTypeManager()->getDefinition($reference_field_definition->getSetting('target_type'));
          $target_table_mapping = $this->getReferencedTableMapping($target_entity_type->id());
          $label_key = $target_entity_type->getKey('label');
          if ($label_key && $target_table_mapping) {
            $target_data_table = $target_table_mapping->getDataTable() ?: $target_table_mapping->getBaseTable();
            $target_id_key = $target_entity_type->getKey('id');
            $target_field_column = $reference_table_mapping->getFieldColumnName($reference_field_storage_definition, 'target_id');
            $query->join($target_data_table, 't', 't.' . $target_id_key . ' = f.' . $target_field_column);
            $query->condition('t.' . $label_key, '%' . $connection->escapeLike($condition) . '%', 'LIKE');
          }
        }
        else {
          $query->condition('f.' . $reference_field_column, '%' . $connection->escapeLike($condition) . '%', 'LIKE');
        }
      }
    }
    return $query;
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
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $configuration = $this->getConfiguration();
    [$field_name, $property] = explode('.', $configuration['property']);
    $values = $this->getAvailableFieldValues($entity_list, $field['id'], $configuration['property'], $input);
    $values = array_combine($values, $values);

    // When referencing a target entity, we will fetch the entity labels.
    if ($property === 'target_id') {
      $field_definition = $field['definition'];
      if ($reference_field_definition = $this->getReferenceFieldDefinition($field_definition)) {
        $nested_reference_entity_type_id = $reference_field_definition->getSetting('target_type');
        $entities = $this->entityTypeManager()->getStorage($nested_reference_entity_type_id)->loadMultiple($values);
        foreach ($values as $target_id => $value) {
          $values[$target_id] = $entities[$target_id]->label();
        }
      }
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function toPreview($value, EntityListInterface $entity_list, array $field) {
    $value = is_array($value) ? $value : [$value];
    $configuration = $this->getConfiguration();
    [$field_name, $property] = explode('.', $configuration['property']);
    if ($property === 'target_id') {
      $options = $this->getValueOptions($entity_list, $field);
      foreach ($value as &$val) {
        $val = isset($options[$val]) ? $options[$val] : $val;
      }
    }
    return implode(', ', $value);
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

  /**
   * {@inheritdoc}
   */
  public function applies(array $field) {
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field_definition */
    $field_definition = $field['definition'];
    $entity_type_id = $field_definition->getSetting('target_type');
    $entity_type = $this->entityTypeManager()->getDefinition($entity_type_id);
    if ($entity_type instanceof ContentEntityTypeInterface) {
      return TRUE;
    }
    return FALSE;
  }

}

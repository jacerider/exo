<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Entity\Sql\SqlEntityStorageInterface;
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
   * Get the property options to export.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The field definition.
   *
   * @return array
   *   An array of property.
   */
  protected function getPropertyOptions(FieldDefinitionInterface $field_definition) {
    $entity_type_id = $field_definition->getSetting('target_type');
    $handler = $field_definition->getSetting('handler_settings');
    $bundles = $handler['target_bundles'] ?? [$entity_type_id];
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = [];
    foreach ($bundles as $bundle) {
      $fields += $entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
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
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['id'] . '.entity.' . $this->getConfiguration()['property'], $query, $value, $entity_list, $field);
  }

  /**
   * Get available field values query.
   */
  protected function getAvailableFieldValuesQuery(EntityListInterface $entity_list, $field_name, $property, $condition = NULL) {
    /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $field = $entity_list->getField($field_name);
    $field_definition = $field['definition'];
    $reference_entity_type_id = $field_definition->getSetting('target_type');
    $reference_handler = $field_definition->getSetting('handler_settings');
    $reference_bundles = $reference_handler['target_bundles'] ?? [$reference_entity_type_id];
    $storage = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId());
    $reference_entity_type = $this->entityTypeManager()->getDefinition($reference_entity_type_id);
    $reference_storage = $this->entityTypeManager()->getStorage($reference_entity_type_id);
    if ($storage instanceof SqlEntityStorageInterface && $reference_storage instanceof SqlEntityStorageInterface) {
      /** @var \Drupal\Core\Entity\Sql\DefaultTableMapping $reference_table_mapping */
      $reference_table_mapping = $reference_storage->getTableMapping();
      [$reference_field_name, $reference_property] = explode('.', $property);
      $reference_field_storage_definition = $entity_field_manager->getFieldStorageDefinitions($reference_entity_type_id)[$reference_field_name];
      $reference_id_key = $reference_entity_type->getKey('id');
      $reference_data_table = $reference_table_mapping->getDataTable();
      $reference_field_table = $reference_table_mapping->getFieldTableName($reference_field_name);
      $reference_field_column = $reference_table_mapping->getFieldColumnName($reference_field_storage_definition, $reference_property);
      $connection = \Drupal::database();
      if ($reference_data_table && $reference_data_table !== $reference_field_table) {
        $query = $connection->select($reference_data_table, 'd');
        $query->join($reference_field_table, 'f', 'd.' . $reference_id_key . ' = f.entity_id');
      }
      else {
        $query = $connection->select($reference_field_table, 'f');
      }
      $query->fields('f', [$reference_field_column])
        ->distinct(TRUE);
      if (!empty($condition)) {
        $query->condition($reference_field_column, '%' . $connection->escapeLike($condition) . '%', 'LIKE');
      }
      if ($bundle_key = $reference_entity_type->getKey('bundle')) {
        $query->condition($bundle_key, $reference_bundles, 'IN');
      }
      return $query;
    }
    return NULL;
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

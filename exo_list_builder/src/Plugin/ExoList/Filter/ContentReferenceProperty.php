<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\ContentEntityTypeInterface;
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
    /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = \Drupal::service('entity_field.manager');
    $field = $entity_list->getField($field_id);
    $field_definition = $field['definition'];
    $reference_entity_type_id = $field_definition->getSetting('target_type');
    $storage = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId());
    $reference_entity_type = $this->entityTypeManager()->getDefinition($reference_entity_type_id);
    $reference_bundles = $this->getFieldBundles($field_definition, $reference_entity_type);
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
      if (!empty($bundles)) {
        $bundle_key = $reference_entity_type->getKey('bundle');
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

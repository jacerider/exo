<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
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
  public function queryAlter(QueryInterface $query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['id'] . '.entity.' . $this->getConfiguration()['property'], $query, $value, $entity_list, $field);
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

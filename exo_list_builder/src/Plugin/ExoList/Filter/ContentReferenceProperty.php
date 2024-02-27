<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\ContentEntityTypeInterface;
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
  public function getPropertyOptions(FieldDefinitionInterface $field_definition) {
    return $this->getPropertyReferenceOptions($field_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function queryFieldAlter($query, $value, EntityListInterface $entity_list, array $field) {
    $this->queryAlterByField($field['field_name'] . '.entity.' . $this->getConfiguration()['property'], $query, $value, $entity_list, $field);
  }

  /**
   * Get available field values query.
   */
  protected function getAvailableFieldValuesQuery(EntityListInterface $entity_list, array $field, $property, $condition, CacheableMetadata $cacheable_metadata, $limit = NULL) {
    $query = $this->getReferencedAvailableFieldValuesQuery($entity_list, $field, $property, $condition, $cacheable_metadata, $limit);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $configuration = $this->getConfiguration();
    return $this->getAvailableFieldValues($entity_list, $field, $configuration['property'], $input);
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
        $val = $options[$val] ?? $val;
      }
    }
    return implode(', ', $value);
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

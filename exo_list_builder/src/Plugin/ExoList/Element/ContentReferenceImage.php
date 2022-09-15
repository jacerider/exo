<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "content_reference_image",
 *   label = @Translation("Reference Image"),
 *   description = @Translation("Content entity reference image"),
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
class ContentReferenceImage extends ContentReferenceProperty {

  /**
   * {@inheritdoc}
   */
  public function getPropertyOptions(FieldDefinitionInterface $field_definition) {
    $options = $this->getPropertyReferenceOptions($field_definition);
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
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    $form = parent::buildConfigurationForm($form, $form_state, $entity_list, $field);
    $form['property']['#type'] = 'select';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    $configuration = $this->getConfiguration();
    [$field_name, $property] = explode('.', $configuration['property']);
    return $field_item->entity->get($field_name)->{$property};
  }

}

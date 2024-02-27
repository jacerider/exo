<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListContentTrait;
use Drupal\exo_list_builder\Plugin\ExoListFieldValuesInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterMatchBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "entity_link_redirect",
 *   label = @Translation("Redirect"),
 *   description = @Translation("Filter by entity label and redirect to entity."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "_link",
 *   },
 *   exclusive = FALSE,
 * )
 */
class EntityRedirect extends ExoListFilterMatchBase implements ExoListFieldValuesInterface {
  use ExoListContentTrait;

  /**
   * {@inheritdoc}
   */
  public function getValueOptions(EntityListInterface $entity_list, array $field, $input = NULL) {
    $available = $entity_list->getAvailableFields();
    $property = $entity_list->getTargetEntityType()->getKey('id');
    $field = $available[$property];
    return $this->getAvailableFieldValues($entity_list, $field, $property, $input);
  }

  /**
   * {@inheritdoc}
   */
  public function toUrlQuery(array $raw_value, EntityListInterface $entity_list, array $field) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    if ($id = $form_state->getValue(['q'])) {
      $entity = $this->entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->load($id);
      if ($entity) {
        $form_state->setRedirectUrl($entity->toUrl());
      }
    }
  }

}

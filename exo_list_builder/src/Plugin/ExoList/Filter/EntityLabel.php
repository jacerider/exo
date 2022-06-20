<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListFilterStringBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListFilter(
 *   id = "entity_label",
 *   label = @Translation("Fulltext"),
 *   description = @Translation("Filter by entity label."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "_label",
 *   },
 *   exclusive = FALSE,
 * )
 */
class EntityLabel extends ExoListFilterStringBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $value, EntityListInterface $entity_list, array $field) {
    $form = parent::buildForm($form, $form_state, $value, $entity_list, $field);
    $form['q']['#field_suffix'] = $this->icon()->setIcon('regular-search')->setIconOnly();
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function queryAlter($query, $value, EntityListInterface $entity_list, array $field) {
    if ($property = $entity_list->getTargetEntityType()->getKey('label')) {
      $this->queryAlterByField($property, $query, $value, $entity_list, $field);
    }
    elseif ($entity_list->getTargetEntityTypeId() === 'user') {
      $this->queryAlterByField('name', $query, $value, $entity_list, $field);
    }
  }

}

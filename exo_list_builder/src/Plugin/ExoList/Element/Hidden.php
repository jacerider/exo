<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "_hidden",
 *   label = @Translation("- Hidden -"),
 *   description = @Translation("Not rendered but can be used to sort."),
 *   weight = -100,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {},
 *   exclusive = FALSE,
 *   sort_only = TRUE,
 * )
 */
class Hidden extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state, EntityListInterface $entity_list, array $field) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildView(EntityInterface $entity, array $field) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function buildPlainView(EntityInterface $entity, array $field) {
    return NULL;
  }

}

<?php

namespace Drupal\exo_list_builder\Plugin;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;

/**
 * Base class for eXo list elements.
 */
abstract class ExoListElementContentBase extends ExoListElementBase {
  use ExoListContentTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [] + parent::defaultConfiguration();
  }

  /**
   * Get viewable output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function view(EntityInterface $entity, array $field) {
    $field_items = $this->getItems($entity, $field);
    if (!$field_items) {
      return NULL;
    }
    $configuration = $this->getConfiguration();
    $values = [];
    foreach ($field_items as $field_item) {
      if ($field_item->isEmpty()) {
        return NULL;
      }
      $values[] = $this->viewItem($entity, $field_item, $field);
    }
    return implode($configuration['separator'], $values) ?: $configuration['empty'];
  }

  /**
   * Get viewable item.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Field\FieldItemInterface $field_item
   *   The field item.
   * @param array $field
   *   The field definition.
   *
   * @return string
   *   The viewable item.
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return '-';
  }

}

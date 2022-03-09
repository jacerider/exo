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
    $configuration = $this->getConfiguration();
    $field_items = $this->getItems($entity, $field);
    if (!$field_items) {
      return $configuration['empty'];
    }
    $values = [];
    foreach ($field_items as $field_item) {
      if ($field_item->isEmpty()) {
        return $configuration['empty'];
      }
      $value = $this->viewItem($entity, $field_item, $field);
      if (is_array($value)) {
        $renderer = \Drupal::service('renderer');
        $value = $renderer->render($value);
      }
      $values[] = $value;
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

  /**
   * Get plain output.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param array $field
   *   The field definition.
   *
   * @return mixed
   *   A renderable array or string.
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
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
      $value = $this->viewPlainItem($entity, $field_item, $field);
      if (is_array($value)) {
        $renderer = \Drupal::service('renderer');
        $value = $renderer->render($value);
      }
      $values[] = $value;
    }
    return implode($configuration['separator'], $values) ?: $configuration['empty'];
  }

  /**
   * Get plain viewable item.
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
  protected function viewPlainItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return $this->viewItem($entity, $field_item, $field);
  }

}

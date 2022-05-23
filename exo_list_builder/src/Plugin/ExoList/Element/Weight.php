<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Element;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;
use Drupal\exo_list_builder\Plugin\ExoListElementContentBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "weight",
 *   label = @Translation("Weight"),
 *   description = @Translation("Render the weight as a form field with dragging."),
 *   weight = 0,
 *   field_type = {
 *     "integer",
 *     "config",
 *   },
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *    "weight",
 *   },
 *   exclusive = FALSE,
 * )
 */
class Weight extends ExoListElementBase {

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    $weight = $this->getWeight($entity, $field);
    return [
      '#type' => 'number',
      '#title' => t('Weight for @title', [
        '@title' => $entity->label(),
      ]),
      '#title_display' => 'invisible',
      '#default_value' => $weight,
      '#list_weight' => $weight,
      '#attributes' => ['class' => ['list-weight']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function viewPlain(EntityInterface $entity, array $field) {
    return $this->getWeight($entity, $field);
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(EntityInterface $entity, array $field) {
    $weight = 0;
    if ($entity instanceof ContentEntityInterface) {
      $weight = $entity->get($field['field_name'])->value;
    }
    elseif ($entity instanceof ConfigEntityInterface) {
      $weight = $entity->get($field['field_name']);
    }
    return $weight;
  }

  /**
   * {@inheritdoc}
   */
  protected function viewItem(EntityInterface $entity, FieldItemInterface $field_item, array $field) {
    return [
      '#type' => 'number',
      '#title' => t('Weight for @title', [
        '@title' => $field['label'],
      ]),
      '#title_display' => 'invisible',
      '#default_value' => $field_item->value,
      '#attributes' => ['class' => ['list-weight']],
    ];
  }

}

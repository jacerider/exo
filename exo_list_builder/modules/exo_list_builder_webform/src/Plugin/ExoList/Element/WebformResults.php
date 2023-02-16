<?php

namespace Drupal\exo_list_builder_webform\Plugin\ExoList\Element;

use Drupal\Core\Entity\EntityInterface;
use Drupal\exo_list_builder\Plugin\ExoListElementBase;

/**
 * Defines a eXo list element for rendering a content entity field.
 *
 * @ExoListElement(
 *   id = "_webform_results",
 *   label = @Translation("Render"),
 *   description = @Translation("Render property."),
 *   weight = 0,
 *   field_type = {},
 *   entity_type = {},
 *   bundle = {},
 *   field_name = {
 *     "_webform_results",
 *   },
 *   exclusive = FALSE,
 * )
 */
class WebformResults extends ExoListElementBase {

  /**
   * Allow field to be linked to entity.
   *
   * @var bool
   */
  protected $allowEntityLink = FALSE;

  /**
   * {@inheritdoc}
   */
  protected function view(EntityInterface $entity, array $field) {
    $query = $this->entityTypeManager()->getStorage('webform_submission')->getQuery();
    $query->condition('webform_id', $entity->id());
    $query->accessCheck(TRUE);
    return [
      '#type' => 'link',
      '#url' => $entity->toUrl('results-submissions'),
      '#title' => count($query->execute()),
    ];
  }

}

<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Action;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListActionBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListAction(
 *   id = "save",
 *   label = @Translation("Save"),
 *   description = @Translation("Save the entity."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 *   queue = TRUE,
 * )
 */
class Save extends ExoListActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->load($entity_id);
    $entity->save();
  }

}

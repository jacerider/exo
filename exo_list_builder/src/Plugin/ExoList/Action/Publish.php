<?php

namespace Drupal\exo_list_builder\Plugin\ExoList\Action;

use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListActionBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListAction(
 *   id = "publish",
 *   label = @Translation("Publish"),
 *   description = @Translation("Publish the entity."),
 *   weight = 0,
 *   entity_type = {},
 *   bundle = {},
 *   queue = TRUE,
 * )
 */
class Publish extends ExoListActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = \Drupal::entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->load($entity_id);
    if ($entity instanceof EntityPublishedInterface) {
      $entity->setPublished(TRUE);
      $entity->save();
    }
  }

}

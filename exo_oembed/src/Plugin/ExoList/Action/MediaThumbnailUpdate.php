<?php

namespace Drupal\exo_oembed\Plugin\ExoList\Action;

use Drupal\exo_list_builder\EntityListInterface;
use Drupal\exo_list_builder\Plugin\ExoListActionBase;

/**
 * Defines a eXo list action for batch operations.
 *
 * @ExoListAction(
 *   id = "media_thumbnail_update",
 *   label = @Translation("Update Thumbnails"),
 *   description = @Translation("Update media thumbnails."),
 *   weight = 5,
 *   entity_type = {"media"},
 *   bundle = {},
 *   queue = TRUE,
 * )
 */
class MediaThumbnailUpdate extends ExoListActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity_id, EntityListInterface $entity_list, $selected, array &$context) {
    /** @var \Drupal\media\Entity\Media $entity */
    $entity = \Drupal::entityTypeManager()->getStorage($entity_list->getTargetEntityTypeId())->load($entity_id);
    $entity->updateQueuedThumbnail();
    $entity->save();
  }

}

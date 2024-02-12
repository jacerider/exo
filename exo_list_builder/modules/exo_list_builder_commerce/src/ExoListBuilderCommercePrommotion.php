<?php

namespace Drupal\exo_list_builder_commerce;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\exo_list_builder\ExoListBuilderContent;

/**
 * Provides a list builder for content entities.
 */
class ExoListBuilderCommercePrommotion extends ExoListBuilderContent {

  /**
   * Gets this list's default operations.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity the operations are for.
   *
   * @return array
   *   The array structure is identical to the return value of
   *   self::getOperations().
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['coupons'] = [
      'title' => $this->t('Coupons'),
      'weight' => -10,
      'url' => Url::fromRoute('entity.commerce_promotion_coupon.collection', ['commerce_promotion' => $entity->id()]),
    ];
    return $operations;
  }

}

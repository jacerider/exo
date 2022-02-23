<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;

/**
 * Defines the access control handler for the entity list entity type.
 *
 * @see \Drupal\exo_list_builder\Entity\EntityList
 */
class EntityListAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\exo_list_builder\EntityListInterface $entity */
    if ($operation === 'delete' && $entity->isNew()) {
      return AccessResult::forbidden()->addCacheableDependency($entity);
    }
    $admin_permission = $this->entityType->getAdminPermission();
    if ($admin_permission && $account->hasPermission($admin_permission)) {
      return AccessResult::allowedIfHasPermission($account, $admin_permission);
    }
    if ($operation === 'view') {
      $permission = 'access ' . $entity->id() . ' list';
      if ($account->hasPermission($permission)) {
        return AccessResult::allowed()->addCacheContexts(['user.permissions']);
      }
      if ($entity->isOverride() && $this->entityType->getLinkTemplate('collection')) {
        $target_entity_type = $entity->getTargetEntityType();
        $route_name = "entity.{$target_entity_type->id()}.collection";
        $url = new Url($route_name);
        return $url->access($account, TRUE);
      }
    }
    return AccessResult::neutral();
  }

}

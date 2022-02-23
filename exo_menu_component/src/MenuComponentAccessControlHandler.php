<?php

namespace Drupal\exo_menu_component;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Menu Component entity.
 *
 * @see \Drupal\exo_menu_component\Entity\MenuComponent.
 */
class MenuComponentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\exo_menu_component\Entity\MenuComponentInterface $entity */

    switch ($operation) {

      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view menu components');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit menu components');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete menu components');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add menu components');
  }

}

<?php

namespace Drupal\exo_list_builder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * View builder handler for nodes.
 */
class EntityListViewBuilder extends EntityViewBuilder implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  protected function getBuildDefaults(EntityInterface $entity, $view_mode) {
    /** @var \Drupal\exo_list_builder\EntityListInterface $entity */
    return $entity->getHandler()->render();
  }

}

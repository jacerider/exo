<?php

namespace Drupal\exo_menu_component;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Menu Components.
 *
 * @ingroup exo_menu_component
 */
class MenuComponentListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Menu Component ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\exo_menu_component\Entity\MenuComponent $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.exo_menu_component.edit_form',
      ['exo_menu_component' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}

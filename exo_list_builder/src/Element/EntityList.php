<?php

namespace Drupal\exo_list_builder\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\exo_list_builder\Entity\EntityList as EntityEntityList;

/**
 * Provides a render element to display an entity list.
 *
 * @RenderElement("exo_entity_list")
 */
class EntityList extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = static::class;
    return [
      '#pre_render' => [
        [$class, 'preRenderExoEntityListViewElement'],
      ],
      '#list_id' => NULL,
      '#list_filters' => [],
      '#cache' => [],
    ];
  }

  /**
   * View element pre render callback.
   */
  public static function preRenderExoEntityListViewElement($element) {
    if (!empty($element['#pre_rendered'])) {
      return $element;
    }

    if (!isset($element['#entity_list'])) {
      $list = EntityEntityList::load($element['#list_id']);
      if (!$list) {
        throw new \Exception("Invalid list name ({$element['#list_id']}) given.");
      }
    }
    else {
      $list = $element['#entity_list'];
    }
    /** @var \Drupal\exo_list_builder\EntityListInterface $list */
    foreach ($element['#list_filters'] as $key => $filter) {
      $list->getHandler()->setOption(['filter', $key], $filter);
    }

    $element['list'] = \Drupal::entityTypeManager()->getViewBuilder($list->getEntityTypeId())->view($list, 'default');
    if (isset($element['#filters_prefix'])) {
      $element['list']['header']['first']['filters']['inline']['prefix'] = ['#weight' => -1000] + $element['#filters_prefix'];
    }
    if (isset($element['#filters_suffix'])) {
      $element['list']['header']['first']['filters']['inline']['suffix'] = ['#weight' => 1000] + $element['#filters_suffix'];
    }
    return $element;
  }

}
